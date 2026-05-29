<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileIsComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->hasCompletedProfile()) {
            return redirect()
                ->route('cadastro.completar')
                ->with('error', 'Complete seu cadastro antes de contratar serviços.');
        }

        if (!$user->email_verified_at) {
            $request->session()->put('pending_email_verification_user_id', $user->id);

            return redirect()
                ->route('cadastro.verificar.email')
                ->with('error', 'Verifique seu e-mail antes de continuar.');
        }

        return $next($request);
    }
}
