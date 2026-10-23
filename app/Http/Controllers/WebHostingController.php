<?php

namespace App\Http\Controllers;

use App\Services\Catalog;

class WebHostingController extends Controller
{
    public function index(Catalog $catalog)
    {
        return view('web.index', ['produtos' => $catalog->products('web')]);
    }
}
