<?php

namespace App\Http\Controllers;

use App\Models\CustomerService;
use App\Models\PterodactylUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class PteroPanelController extends Controller
{
    public function __invoke(CustomerService $service)
    {
        abort_if((int) $service->user_id !== (int) Auth::id(), 403);

        $pteroUser = PterodactylUser::where('user_id', Auth::id())->first();

        $password = $pteroUser?->encrypted_password
            ? Crypt::decryptString($pteroUser->encrypted_password)
            : null;

        return view('pterodactyl.panel', [
            'username' => $pteroUser?->username ?? Auth::user()->username,
            'password' => $password,
            'url' => rtrim(config('services.pterodactyl.url'), '/'),
        ]);
    }
}