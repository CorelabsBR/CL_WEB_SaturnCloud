<?php

namespace App\Http\Controllers;

use App\Models\CategoriaProd;
use App\Models\Product;

class HomeController extends Controller
{
    public function __invoke()
    {
        try {
            $categories = CategoriaProd::whereIn('nome', ['Hospedagem Web', 'VPS'])->pluck('id', 'nome');
            $webProducts = isset($categories['Hospedagem Web'])
                ? Product::where('categoria_id', $categories['Hospedagem Web'])->orderBy('sort_order')->orderBy('preco')->get()
                : collect();
            $vpsProducts = isset($categories['VPS'])
                ? Product::where('categoria_id', $categories['VPS'])->orderBy('sort_order')->orderBy('preco')->get()
                : collect();
        } catch (\Throwable $exception) {
            report($exception);
            $webProducts = collect();
            $vpsProducts = collect();
        }

        return view('home', compact('webProducts', 'vpsProducts'));
    }
}
