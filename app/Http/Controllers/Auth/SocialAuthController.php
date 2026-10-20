<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirect(string $provider)
    {
        abort_unless(in_array($provider, ['google', 'discord']), 404);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        abort_unless(in_array($provider, ['google', 'discord']), 404);

        $socialUser = Socialite::driver($provider)->user();

        $providerIdColumn = $provider . '_id';

        $providerUser = User::where($providerIdColumn, $socialUser->getId())->first();
        $emailUser = $socialUser->getEmail()
            ? User::where('email', $socialUser->getEmail())->first()
            : null;

        if ($providerUser && $emailUser && $providerUser->id !== $emailUser->id) {
            throw ValidationException::withMessages([
                'email' => 'Este Google já está vinculado a outra conta.',
            ]);
        }

        $user = $providerUser ?: $emailUser;

        if (!$user) {
            $user = User::create([
                'nome' => $socialUser->getName()
                    ?: $socialUser->getNickname()
                    ?: 'Cliente Saturn Cloud',

                'email' => $socialUser->getEmail(),

                'senha' => null,

                $providerIdColumn => $socialUser->getId(),
                'provider' => $provider,
                'avatar' => $socialUser->getAvatar(),
                'email_verified_at' => now(),

                'tipo_usuario' => 'cliente',
                'profile_completed_at' => null,
            ]);
        } else {
            $user->update([
                $providerIdColumn => $socialUser->getId(),
                'provider' => $provider,
                'avatar' => $socialUser->getAvatar(),
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);
        }

        Auth::login($user, true);

        request()->session()->regenerate();

        if (! $user->hasCompletedProfile()) {
            return redirect()
                ->route('cadastro.completar')
                ->with('success', 'Conta conectada. Complete seu cadastro para continuar.');
        }

        if (empty($user->profile_completed_at)) {
            $user->update(['profile_completed_at' => now()]);
        }

        return redirect()->intended('/dashboard');
    }
}
