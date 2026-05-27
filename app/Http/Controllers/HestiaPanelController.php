<?php

namespace App\Http\Controllers;

use App\Models\CustomerService;
use Illuminate\Support\Facades\Auth;

class HestiaPanelController extends Controller
{
    public function __invoke(CustomerService $service)
    {
        abort_if((int) $service->user_id !== (int) Auth::id(), 403);
        abort_if($service->type !== 'web', 404);

        $service->loadMissing('hestiaAccount');
        $account = $service->hestiaAccount;

        $password = $account?->password ?: data_get($account?->raw_response, 'password');

        if (! $account || ! $account->hestia_user || ! $password) {
            return redirect()
                ->route('dashboard.index')
                ->with('error', 'A conta Hestia ainda não está pronta para acesso automático.');
        }

        $url = rtrim((string) config('services.hestia.url'), '/');

        if (config('services.hestia.autologin', true)) {
            return response()->view('hestia.autologin', [
                'url' => $url . '/login/',
                'user' => $account->hestia_user,
                'password' => $password,
            ]);
        }

        return response()->view('hestia.panel', [
            'url' => $url,
            'user' => $account->hestia_user,
            'password' => $password,
        ]);
    }
}
