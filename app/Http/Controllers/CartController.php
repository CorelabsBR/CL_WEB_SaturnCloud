<?php

namespace App\Http\Controllers;

use App\Services\Catalog;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request, Catalog $catalog)
    {
        $cart = $request->session()->get('cart', []);
        $items = $catalog->products()->filter(fn ($product) => isset($cart[$product->id]))
            ->map(fn ($product) => (object) ['product' => $product, 'quantity' => $cart[$product->id]]);
        $portalCartUrl = config('portal.cart_url');
        if (! filter_var($portalCartUrl, FILTER_VALIDATE_URL) || parse_url($portalCartUrl, PHP_URL_SCHEME) !== 'https') {
            $portalCartUrl = null;
        }

        return view('cart.index', compact('items', 'portalCartUrl'));
    }

    public function addProduct(Request $request, Catalog $catalog, string $product)
    {
        $catalog->find($product);
        $data = $request->validate(['quantidade' => 'sometimes|required|integer|min:1|max:99']);
        $cart = $request->session()->get('cart', []);
        $cart[$product] = min(99, ($cart[$product] ?? 0) + ($data['quantidade'] ?? 1));
        $request->session()->put('cart', $cart);

        return redirect()->route('cart.index');
    }

    public function update(Request $request, Catalog $catalog, string $product)
    {
        $catalog->find($product);
        $data = $request->validate(['quantidade' => 'required|integer|min:1|max:99']);
        $cart = $request->session()->get('cart', []);
        abort_unless(isset($cart[$product]), 404);
        $cart[$product] = (int) $data['quantidade'];
        $request->session()->put('cart', $cart);

        return redirect()->route('cart.index');
    }

    public function remove(Request $request, string $product)
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$product]);
        $request->session()->put('cart', $cart);

        return redirect()->route('cart.index');
    }

    public function clear(Request $request)
    {
        $request->session()->forget('cart');

        return redirect()->route('cart.index');
    }
}
