<?php

namespace App\Http\Controllers;

use App\Services\Catalog;

class VpsController extends Controller
{
    public function index(Catalog $catalog)
    {
        return view('vps.index', ['produtos' => $catalog->products('vps')]);
    }
}
