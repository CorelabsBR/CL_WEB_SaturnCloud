<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\MercadoPagoService;
use App\Services\ProvisioningService;
use App\Services\RenewalBillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class CheckoutController extends Controller
{
    public function index()
    {
        $items = CartItem::with('product.categoria')
            ->where('user_id', Auth::id())
            ->get();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Carrinho vazio.');
        }

        $totals = $this->cartTotals($items);

        return view('checkout.index', [
            'items' => $items,
            'total' => $totals['subtotal'],
            'subtotal' => $totals['subtotal'],
            'discount' => $totals['discount'],
            'finalTotal' => $totals['final_total'],
            'coupon' => $totals['coupon'],
            'adminExempt' => $totals['admin_exempt'],
        ]);
    }

    public function applyCoupon(Request $request)
    {
        $data = $request->validate([
            'coupon_code' => ['required', 'string', 'max:60'],
        ]);

        $coupon = $this->findCoupon($data['coupon_code']);

        if (! $coupon) {
            return redirect()
                ->route('checkout.index')
                ->with('error', 'Cupom invalido, inativo ou esgotado.');
        }

        session(['checkout_coupon_code' => $coupon->code]);

        return redirect()
            ->route('checkout.index')
            ->with('success', 'Cupom aplicado com sucesso.');
    }

    public function removeCoupon()
    {
        session()->forget('checkout_coupon_code');

        return redirect()
            ->route('checkout.index')
            ->with('success', 'Cupom removido.');
    }

    public function pay(
        Request $request,
        MercadoPagoService $mercadoPago,
        ProvisioningService $provisioning,
        RenewalBillingService $billing
    ) {
        $billing->purgeExpiredInitialPendingOrders();

        $cartItems = CartItem::with('product.categoria')
            ->where('user_id', Auth::id())
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Carrinho vazio.');
        }

        $requestedUsernames = $this->validatedRequestedUsernames($request, $cartItems);

        $order = DB::transaction(function () use ($cartItems, $requestedUsernames) {
            $totals = $this->cartTotals($cartItems, true);

            if (! $totals['admin_exempt'] && session('checkout_coupon_code') && ! $totals['coupon']) {
                session()->forget('checkout_coupon_code');

                throw ValidationException::withMessages([
                    'coupon_code' => 'Este cupom nao esta mais disponivel.',
                ]);
            }

            if ($totals['admin_exempt']) {
                session()->forget('checkout_coupon_code');
            }

            $order = Order::create([
                'user_id' => Auth::id(),
                'total' => $totals['final_total'],
                'coupon_code' => $totals['admin_exempt'] ? null : $totals['coupon']?->code,
                'discount_amount' => $totals['discount'],
                'status' => $totals['final_total'] <= 0 ? 'pago' : 'pendente',
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantidade' => $item->quantidade,
                    'preco' => $item->product->preco,
                    'requested_username' => $requestedUsernames[$item->id] ?? null,
                ]);
            }

            if (! $totals['admin_exempt'] && $totals['coupon']) {
                if ($totals['coupon']->isSingleUsePerUser()) {
                    $redeemed = DB::table('coupon_redemptions')->insertOrIgnore([
                        'coupon_id' => $totals['coupon']->id,
                        'user_id' => Auth::id(),
                        'order_id' => $order->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    if (! $redeemed) {
                        throw ValidationException::withMessages([
                            'coupon_code' => 'Você já usou este cupom.',
                        ]);
                    }
                }

                $totals['coupon']->increment('used_count');
            }

            CartItem::where('user_id', Auth::id())->delete();
            session()->forget('checkout_coupon_code');

            return $order;
        });

        $order->load('items.product');

        if ((float) $order->total <= 0) {
            $adminExempt = $this->isAdminExempt();

            $order->payments()->create([
                'provider' => $adminExempt ? 'admin_grant' : 'coupon',
                'amount' => 0,
                'status' => 'approved',
                'raw_payload' => [
                    'approved_by_user_id' => $adminExempt ? Auth::id() : null,
                    'coupon_code' => $adminExempt ? null : $order->coupon_code,
                    'discount_amount' => $order->discount_amount,
                    'reason' => $adminExempt ? 'root_admin_checkout_exemption' : 'coupon_checkout',
                ],
            ]);

            $provisioning->provisionOrder($order);
            $this->processHestiaQueue($order);

            return redirect()
                ->route('dashboard.index')
                ->with('success', $adminExempt
                    ? 'Pedido administrativo aprovado sem pagamento.'
                    : 'Cupom aplicado. Pedido aprovado sem pagamento.'
                );
        }

        return $this->redirectToPayment($order, $mercadoPago);
    }

    public function payOrder(Order $order, MercadoPagoService $mercadoPago, RenewalBillingService $billing)
    {
        abort_if((int) $order->user_id !== (int) Auth::id(), 403);

        if ($this->isExpiredInitialPendingOrder($order)) {
            $billing->purgeExpiredInitialPendingOrder($order);

            return redirect()
                ->route('dashboard.index')
                ->with('error', 'Esta compra passou do prazo de 24 horas sem pagamento e foi removida. Faça uma nova compra para continuar.');
        }

        if ($this->isExpiredPendingOrder($order)) {
            $terminated = $billing->terminateOverdueOrder($order);

            return redirect()
                ->route('dashboard.index')
                ->with('error', $terminated
                    ? 'Esta fatura passou do prazo de 5 dias e o serviço foi cancelado.'
                    : 'Esta fatura passou do prazo de 5 dias e não pode mais ser paga. Nossa equipe foi avisada para revisar a remoção do serviço.'
                );
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

    private function isExpiredInitialPendingOrder(Order $order): bool
    {
        return $order->status === 'pendente'
            && $order->billing_type !== 'renewal'
            && $order->criado_em
            && $order->criado_em->lte(now()->subDay());
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

        // Persist order id in session so we can redirect user to configuration after payment return
        session(['checkout_order_id' => $order->id]);

        if (! $checkoutUrl) {
            return redirect()
                ->route('dashboard.index')
                ->with('error', 'O Mercado Pago não retornou o link de pagamento.');
        }

        return redirect()->away($checkoutUrl);
    }

    private function processHestiaQueue(Order $order): void
    {
        try {
            $exitCode = Artisan::call('hestia:process-queue', [
                'order_id' => (string) $order->id,
            ]);
            $output = trim(Artisan::output());

            if ($exitCode !== 0) {
                Log::warning('Fila Hestia retornou erro apos pedido aprovado por cupom', [
                    'order_id' => $order->id,
                    'exit_code' => $exitCode,
                    'output' => $output,
                ]);
            }
        } catch (Throwable $e) {
            Log::error('Falha ao processar fila Hestia apos pedido aprovado por cupom', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function cartTotals($items, bool $lockCoupon = false): array
    {
        $subtotal = round((float) $items->sum(fn ($item) => $item->product->preco * $item->quantidade), 2);
        $adminExempt = $this->isAdminExempt();
        $coupon = $adminExempt ? null : $this->activeSessionCoupon($lockCoupon);
        $discount = $adminExempt ? $subtotal : ($coupon?->discountFor($subtotal) ?? 0);

        return [
            'subtotal' => $subtotal,
            'coupon' => $coupon,
            'discount' => $discount,
            'final_total' => round(max(0, $subtotal - $discount), 2),
            'admin_exempt' => $adminExempt,
        ];
    }

    private function isAdminExempt(): bool
    {
        return Auth::check() && Auth::user()->tipo_usuario === 'root';
    }

    private function validatedRequestedUsernames(Request $request, $cartItems): array
    {
        $requested = $request->input('requested_usernames', []);
        $values = [];
        $errors = [];
        $seen = [];

        foreach ($cartItems as $item) {
            if (! $this->isWebProduct($item->product)) {
                continue;
            }

            $value = Str::lower(trim((string) ($requested[$item->id] ?? '')));

            if ($value === '') {
                $errors["requested_usernames.{$item->id}"] = 'Informe o usuário do painel para o plano web.';
                continue;
            }

            if (! preg_match('/^[a-z][a-z0-9]{2,31}$/', $value)) {
                $errors["requested_usernames.{$item->id}"] = 'Use 3 a 32 caracteres, começando com letra, apenas letras minúsculas e números.';
                continue;
            }

            if (isset($seen[$value])) {
                $errors["requested_usernames.{$item->id}"] = 'Use um usuário diferente para cada plano web.';
                continue;
            }

            $alreadyUsed = OrderItem::query()
                ->where('requested_username', $value)
                ->whereHas('order', fn ($query) => $query->whereIn('status', ['pendente', 'pago']))
                ->exists();

            if ($alreadyUsed) {
                $errors["requested_usernames.{$item->id}"] = 'Este usuário já está em uso. Escolha outro.';
                continue;
            }

            $seen[$value] = true;
            $values[$item->id] = $value;
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return $values;
    }

    private function isWebProduct($product): bool
    {
        $category = Str::lower((string) $product?->categoria?->nome);

        if (str_contains($category, 'web') || str_contains($category, 'hosped')) {
            return true;
        }

        return (int) $product?->categoria_id === 1;
    }

    private function activeSessionCoupon(bool $lock = false): ?Coupon
    {
        $code = session('checkout_coupon_code');

        return $code ? $this->findCoupon($code, $lock) : null;
    }

    private function findCoupon(string $code, bool $lock = false): ?Coupon
    {
        $code = trim($code);

        $query = Coupon::query()->where('active', true);

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->get()
            ->first(function (Coupon $coupon) use ($code) {
                if (trim($coupon->code) !== $code || ! $coupon->isAvailable()) {
                    return false;
                }

                if (! $coupon->isSingleUsePerUser()) {
                    return true;
                }

                return ! DB::table('coupon_redemptions')
                    ->where('coupon_id', $coupon->id)
                    ->where('user_id', Auth::id())
                    ->exists();
            });
    }

    public function success(Request $request, MercadoPagoService $mercadoPago)
    {
        $this->syncReturnedPayment($request, $mercadoPago);

        session()->forget('checkout_order_id');

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
