<?php

namespace App\Http\Controllers;

use App\Models\CategoriaProd;
use App\Models\Product;

class VpsController extends Controller
{
    public function index()
    {
        try {
            $categoria = CategoriaProd::where('nome', 'VPS')->first();
            $produtos = $categoria
                ? Product::where('categoria_id', $categoria->id)
                    ->orderBy('sort_order')
                    ->orderBy('preco')
                    ->get()
                : collect();
        } catch (\Throwable $exception) {
            report($exception);
            $produtos = collect();
        }

        return view('vps.index', compact('produtos'));
    }
}
