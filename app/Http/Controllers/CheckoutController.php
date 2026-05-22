<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function index()
    {
        $items = CartItem::with('product')
            ->where('user_id', Auth::id())
            ->get();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Carrinho vazio.');
        }

        $total = $items->sum(fn ($item) => $item->product->preco * $item->quantidade);

        return view('checkout.index', compact('items', 'total'));
    }

    public function pay(MercadoPagoService $mercadoPago)
    {
        $cartItems = CartItem::with('product')
            ->where('user_id', Auth::id())
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Carrinho vazio.');
        }

        $order = DB::transaction(function () use ($cartItems) {
            $total = $cartItems->sum(fn ($item) => $item->product->preco * $item->quantidade);

            $order = Order::create([
                'user_id' => Auth::id(),
                'total' => $total,
                'status' => 'pendente',
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantidade' => $item->quantidade,
                    'preco' => $item->product->preco,
                ]);
            }

            CartItem::where('user_id', Auth::id())->delete();

            return $order;
        });

        $order->load('items.product');

        return $this->redirectToPayment($order, $mercadoPago);
    }

    public function payOrder(Order $order, MercadoPagoService $mercadoPago)
    {
        abort_if((int) $order->user_id !== (int) Auth::id(), 403);

        if ($this->isExpiredPendingOrder($order)) {
            $order->update(['status' => 'cancelado']);

            return redirect()
                ->route('dashboard.index')
                ->with('error', 'Esta fatura expirou após 1 minuto e foi cancelada.');
        }

        if ($order->status === 'pago') {
            return redirect()
                ->route('dashboard.index')
                ->with('success', 'Esta fatura já está paga.');
        }

        if ($order->status !== 'pendente') {
            return redirect()
                ->route('dashboard.index')
                ->with('error', 'Esta fatura não pode ser paga novamente.');
        }

        $order->load('items.product');

        if ($order->items->isEmpty()) {
            return redirect()
                ->route('dashboard.index')
                ->with('error', 'Não encontramos itens nesta fatura.');
        }

        return $this->redirectToPayment($order, $mercadoPago);
    }

    private function isExpiredPendingOrder(Order $order): bool
    {
        return $order->status === 'pendente'
            && $order->billing_type === 'renewal'
            && $order->due_at
            && $order->due_at->isPast();
    }

    private function redirectToPayment(Order $order, MercadoPagoService $mercadoPago)
    {
        try {
            $preference = $mercadoPago->createPreference($order);
        } catch (\Throwable $e) {
            Log::error('Erro ao gerar preferência Mercado Pago', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('dashboard.index')
                ->with('error', 'Erro ao gerar pagamento Mercado Pago.');
        }

        $checkoutUrl = $preference['init_point'] ?? $preference['sandbox_init_point'] ?? null;

        if (! $checkoutUrl) {
            return redirect()
                ->route('dashboard.index')
                ->with('error', 'O Mercado Pago não retornou o link de pagamento.');
        }

        return redirect()->away($checkoutUrl);
    }

    public function success(Request $request, MercadoPagoService $mercadoPago)
    {
        $this->syncReturnedPayment($request, $mercadoPago);

        return redirect()->route('dashboard.index')->with('success', 'Pagamento aprovado ou em processamento.');
    }

    public function pending(Request $request, MercadoPagoService $mercadoPago)
    {
        $this->syncReturnedPayment($request, $mercadoPago);

        return redirect()->route('dashboard.index')->with('success', 'Pagamento pendente.');
    }

    public function failure(Request $request, MercadoPagoService $mercadoPago)
    {
        $this->syncReturnedPayment($request, $mercadoPago);

        return redirect()->route('dashboard.index')->with('error', 'Pagamento não aprovado.');
    }

    private function syncReturnedPayment(Request $request, MercadoPagoService $mercadoPago): void
    {
        $paymentId = $request->query('payment_id') ?: $request->query('collection_id');

        if (! $paymentId) {
            return;
        }

        try {
            $payment = $mercadoPago->getPayment((string) $paymentId);
            $mercadoPago->syncPayment($payment);
        } catch (\Throwable $e) {
            Log::warning('Não foi possível sincronizar retorno Mercado Pago', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
