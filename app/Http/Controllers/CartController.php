<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $items = CartItem::with('product')
            ->where('user_id', Auth::id())
            ->get();

        $total = $items->sum(fn ($item) => $item->product->preco * $item->quantidade);

        return view('cart.index', compact('items', 'total'));
    }

    public function addPlan($plan)
    {
        $plan = Plan::where('plan', $plan)->firstOrFail();

        $product = Product::firstOrCreate(
            ['plan' => $plan->plan],
            [
                'nome' => $plan->name,
                'descricao' => 'Servidor de jogo',
                'preco' => $plan->price / 100,
                'categoria_id' => 2,
                'jogo_id' => $plan->jogo_id,
                'local_id' => $plan->location_id,
                'ram' => $plan->ramFormatada(),
                'armazenamento' => $plan->discoFormatado(),
                'processamento' => $plan->cpuFormatada(),
                'ddos_protection' => true,
                'featured' => false,
                'sort_order' => $plan->plan,
            ]
        );

        $this->addProductToCart($product, 1);

        return redirect()
            ->route('cart.index')
            ->with('success', 'Plano adicionado ao carrinho.');
    }

    public function addProduct(Request $request, Product $product)
    {
        $quantidade = (int) $request->input('quantidade', 1);

        if ($quantidade < 1) {
            $quantidade = 1;
        }

        $this->addProductToCart($product, $quantidade);

        return redirect()
            ->route('cart.index')
            ->with('success', 'Produto adicionado ao carrinho.');
    }

    private function addProductToCart(Product $product, int $quantidade = 1): void
    {
        abort_if(!$product->id, 404);

        $item = CartItem::where('user_id', Auth::id())
            ->where('product_id', $product->id)
            ->first();

        if ($item) {
            $item->quantidade += $quantidade;
            $item->save();
            return;
        }

        CartItem::create([
            'user_id' => Auth::id(),
            'product_id' => $product->id,
            'quantidade' => $quantidade,
        ]);
    }

    public function update(Request $request, CartItem $cartItem)
    {
        abort_if($cartItem->user_id !== Auth::id(), 403);

        $request->validate([
            'quantidade' => 'required|integer|min:1|max:99',
        ]);

        $cartItem->update([
            'quantidade' => $request->quantidade,
        ]);

        return redirect()->route('cart.index');
    }

    public function remove(CartItem $cartItem)
    {
        abort_if($cartItem->user_id !== Auth::id(), 403);

        $cartItem->delete();

        return redirect()->route('cart.index');
    }

    public function clear()
    {
        CartItem::where('user_id', Auth::id())->delete();

        return redirect()->route('cart.index');
    }
}