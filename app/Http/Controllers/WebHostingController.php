<?php

namespace App\Http\Controllers;

use App\Models\CategoriaProd;
use App\Models\Product;

class WebHostingController extends Controller
{
    public function index()
    {
        $categoria = CategoriaProd::firstOrCreate([
            'nome' => 'Hospedagem Web',
        ]);

        $produtos = Product::where('categoria_id', $categoria->id)
            ->orderBy('sort_order')
            ->orderBy('preco')
            ->get();

        return view('web.index', compact('produtos'));
    }
}
