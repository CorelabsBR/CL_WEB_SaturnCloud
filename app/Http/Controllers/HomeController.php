<?php

namespace App\Http\Controllers;

use App\Services\Catalog;

class HomeController extends Controller
{
    public function __invoke(Catalog $catalog)
    {
        return view('home', ['webProducts' => $catalog->products('web'), 'vpsProducts' => $catalog->products('vps')]);
    }
}
