<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        if (Auth::check()) {
            if ($this->expireUnverifiedUserIfNeeded($request, Auth::user())) {
                return redirect()
                    ->route('registro')
                    ->with('error', 'Seu cadastro expirou porque o e-mail não foi verificado em até 1 dia. Faça o cadastro novamente.');
            }

            if (!Auth::user()?->email_verified_at) {
                return redirect()->route('cadastro.verificar.email');
            }

            return redirect()->route('dashboard.index');
        }

        return view('auth.login');
    }

    public function showRegister(Request $request)
    {
        if (Auth::check()) {
            if ($this->expireUnverifiedUserIfNeeded($request, Auth::user())) {
                return redirect()
                    ->route('registro')
                    ->with('error', 'Seu cadastro expirou porque o e-mail não foi verificado em até 1 dia. Faça o cadastro novamente.');
            }

            if (!Auth::user()?->email_verified_at) {
                return redirect()->route('cadastro.verificar.email');
            }

            return Auth::user()->hasCompletedProfile()
                ? redirect()->route('dashboard.index')
                : redirect()->route('cadastro.completar');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('user', 'email'),
            ],
            'password' => ['required', 'confirmed', 'min:6'],
        ], [
            'email.unique' => 'Já existe uma conta com este e-mail. Entre na sua conta ou use outro e-mail.',
            'password.confirmed' => 'A confirmação da senha não confere.',
            'password.min' => 'A senha precisa ter pelo menos 6 caracteres.',
        ]);

        $request->session()->put('pending_registration', [
            'email' => $data['email'],
            'senha' => Hash::make($data['password']),
            'tipo_usuario' => 'cliente',
        ]);
        $request->session()->regenerate();

        return redirect()
            ->route('cadastro.completar')
            ->with('success', 'Agora complete seu cadastro para criar sua conta.');
    }

    public function showCompleteProfile()
    {
        $user = Auth::user();
        $pendingRegistration = session('pending_registration');

        if (!$user && !$pendingRegistration) {
            return redirect()->route('login');
        }

        if ($user && $user->hasCompletedProfile()) {
            if (empty($user->profile_completed_at)) {
                $user->update(['profile_completed_at' => now()]);
            }

            return redirect()->route('dashboard.index');
        }

        return view('auth.complete-profile', [
            'profileUser' => $user,
            'pendingRegistration' => $pendingRegistration,
        ]);
    }

    public function completeProfile(Request $request)
    {
        $user = Auth::user();
        $pendingRegistration = $request->session()->get('pending_registration');

        if (!$user && !$pendingRegistration) {
            return redirect()->route('login');
        }

        $request->merge([
            'cpf_cnpj' => preg_replace('/\D+/', '', (string) $request->input('cpf_cnpj')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'cpf_cnpj' => [
                'required',
                'string',
                'digits_between:11,14',
                Rule::unique('user', 'cpf_cnpj')->ignore($user?->id),
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (!$this->isValidCpfCnpj((string) $value)) {
                        $fail('Informe um CPF ou CNPJ válido.');
                    }
                },
            ],
            'celular' => ['required', 'string', 'max:20'],
            'data_nasc' => ['required', 'date'],

            'cep' => ['required', 'string', 'max:20'],
            'rua' => ['required', 'string', 'max:255'],
            'numero' => ['required', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:255'],
            'bairro' => ['required', 'string', 'max:255'],
            'cidade' => ['required', 'string', 'max:255'],
            'estado' => ['required', 'string', 'max:255'],
            'pais' => ['required', 'string', 'max:255'],
        ]);

        $profileData = [
            'nome' => $data['name'],
            'cpf_cnpj' => $data['cpf_cnpj'],
            'celular' => $data['celular'],
            'data_nasc' => $data['data_nasc'],

            'cep' => $data['cep'],
            'rua' => $data['rua'],
            'numero' => $data['numero'],
            'complemento' => $data['complemento'] ?? null,
            'bairro' => $data['bairro'],
            'cidade' => $data['cidade'],
            'estado' => $data['estado'],
            'pais' => $data['pais'],

            'profile_completed_at' => now(),
        ];

        if ($user) {
            $user->update($profileData);

            if (!$user->email_verified_at) {
                $user->update([
                    'email_verification_code' => $this->generateEmailVerificationCode(),
                ]);

                $this->sendEmailVerificationCode($user);

                return redirect()
                    ->route('cadastro.verificar.email')
                    ->with('success', 'Cadastro completo! Enviamos um código para seu e-mail.');
            }
        } else {
            if (User::where('email', $pendingRegistration['email'])->exists()) {
                $request->session()->forget('pending_registration');

                throw ValidationException::withMessages([
                    'email' => 'Já existe uma conta com este e-mail. Entre na sua conta para continuar.',
                ]);
            }

            $user = User::create($pendingRegistration + $profileData + [
                'email_verification_code' => $this->generateEmailVerificationCode(),
                'email_verified_at' => null,
            ]);

            $this->sendEmailVerificationCode($user);

            $request->session()->forget('pending_registration');
            $request->session()->put('pending_email_verification_user_id', $user->id);
            $request->session()->regenerate();

            return redirect()
                ->route('cadastro.verificar.email')
                ->with('success', 'Cadastro criado! Enviamos um código para seu e-mail.');
        }

        return redirect()
            ->route('dashboard.index')
            ->with('success', 'Cadastro completo com sucesso.');
    }

    public function showEmailVerification(Request $request)
    {
        $user = $this->pendingEmailVerificationUser($request);

        if (!$user) {
            return redirect()->route('login');
        }

        if ($this->expireUnverifiedUserIfNeeded($request, $user)) {
            return redirect()
                ->route('registro')
                ->with('error', 'Seu cadastro expirou porque o e-mail não foi verificado em até 1 dia. Faça o cadastro novamente.');
        }

        if ($user->email_verified_at) {
            if (!Auth::check()) {
                Auth::login($user);
                $request->session()->regenerate();
            }

            return redirect()->route(
                $user->hasCompletedProfile() ? 'dashboard.index' : 'cadastro.completar'
            );
        }

        return view('auth.verification-email', [
            'email' => $user->email,
        ]);
    }

    public function verifyEmail(Request $request)
    {
        $user = $this->pendingEmailVerificationUser($request);

        if (!$user) {
            return redirect()->route('login');
        }

        if ($this->expireUnverifiedUserIfNeeded($request, $user)) {
            return redirect()
                ->route('registro')
                ->with('error', 'Seu cadastro expirou porque o e-mail não foi verificado em até 1 dia. Faça o cadastro novamente.');
        }

        $data = $request->validate([
            'code' => ['required', 'digits:6'],
        ], [
            'code.required' => 'Informe o código enviado para seu e-mail.',
            'code.digits' => 'O código precisa ter 6 números.',
        ]);

        if (!hash_equals((string) $user->email_verification_code, (string) $data['code'])) {
            throw ValidationException::withMessages([
                'code' => 'Código inválido. Confira o e-mail e tente novamente.',
            ]);
        }

        $user->update([
            'email_verified_at' => now(),
            'email_verification_code' => null,
        ]);

        Auth::login($user);
        $request->session()->forget('pending_email_verification_user_id');
        $request->session()->regenerate();

        return redirect()
            ->route($user->hasCompletedProfile() ? 'dashboard.index' : 'cadastro.completar')
            ->with('success', 'E-mail verificado com sucesso. Você já está logado.');
    }

    public function resendEmailVerificationCode(Request $request)
    {
        $user = $this->pendingEmailVerificationUser($request);

        if (!$user) {
            return redirect()->route('login');
        }

        if ($this->expireUnverifiedUserIfNeeded($request, $user)) {
            return redirect()
                ->route('registro')
                ->with('error', 'Seu cadastro expirou porque o e-mail não foi verificado em até 1 dia. Faça o cadastro novamente.');
        }

        if ($user->email_verified_at) {
            return redirect()->route('dashboard.index');
        }

        $user->update([
            'email_verification_code' => $this->generateEmailVerificationCode(),
        ]);

        $this->sendEmailVerificationCode($user);

        return back()->with('success', 'Enviamos um novo código para seu e-mail.');
    }

    public function showForgotPassword()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard.index');
        }

        return view('auth.forgot-password');
    }

    public function sendPasswordResetCode(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ], [
            'email.required' => 'Informe o e-mail da sua conta.',
            'email.email' => 'Informe um e-mail válido.',
        ]);

        $email = mb_strtolower($data['email']);
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => 'Não encontramos uma conta com este e-mail.',
            ]);
        }

        if (blank($user->senha)) {
            $provider = match (true) {
                $user->provider === 'google' || filled($user->google_id) => 'Google',
                $user->provider === 'discord' || filled($user->discord_id) => 'Discord',
                default => 'login social',
            };

            throw ValidationException::withMessages([
                'email' => "Essa conta foi criada pelo {$provider}. Entre usando {$provider}.",
            ]);
        }

        $code = $this->generateEmailVerificationCode();

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($code),
                'created_at' => now(),
            ]
        );

        $request->session()->put('password_reset_email', $user->email);
        $request->session()->forget('password_reset_code_verified');
        $this->sendPasswordResetCodeEmail($user, $code);

        return redirect()
            ->route('password.code')
            ->with('success', 'Enviamos um código de 6 números para seu e-mail.');
    }

    public function showPasswordResetCode(Request $request)
    {
        $email = $request->session()->get('password_reset_email');

        if (!$email) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-code', [
            'email' => $email,
        ]);
    }

    public function verifyPasswordResetCode(Request $request)
    {
        $email = $request->session()->get('password_reset_email');

        if (!$email) {
            return redirect()->route('password.request');
        }

        $data = $request->validate([
            'code' => ['required', 'digits:6'],
        ], [
            'code.required' => 'Informe o código enviado para seu e-mail.',
            'code.digits' => 'O código precisa ter 6 números.',
        ]);

        $reset = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$reset || Carbon::parse($reset->created_at)->lt(now()->subMinutes(5))) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            $request->session()->forget(['password_reset_email', 'password_reset_code_verified']);

            return redirect()
                ->route('password.request')
                ->with('error', 'O código expirou. Peça um novo código para trocar sua senha.');
        }

        if (!Hash::check($data['code'], $reset->token)) {
            throw ValidationException::withMessages([
                'code' => 'Código inválido. Confira o e-mail e tente novamente.',
            ]);
        }

        $request->session()->put('password_reset_code_verified', true);

        return redirect()
            ->route('password.reset.form')
            ->with('success', 'Código confirmado. Agora crie sua nova senha.');
    }

    public function showResetPasswordForm(Request $request)
    {
        if (!$request->session()->get('password_reset_email')) {
            return redirect()->route('password.request');
        }

        if (!$request->session()->get('password_reset_code_verified')) {
            return redirect()->route('password.code');
        }

        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $email = $request->session()->get('password_reset_email');

        if (!$email) {
            return redirect()->route('password.request');
        }

        if (!$request->session()->get('password_reset_code_verified')) {
            return redirect()->route('password.code');
        }

        $data = $request->validate([
            'password' => ['required', 'confirmed', 'min:6'],
        ], [
            'password.required' => 'Informe sua nova senha.',
            'password.confirmed' => 'A confirmação da senha não confere.',
            'password.min' => 'A senha precisa ter pelo menos 6 caracteres.',
        ]);

        $reset = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$reset || Carbon::parse($reset->created_at)->lt(now()->subMinutes(5))) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            $request->session()->forget(['password_reset_email', 'password_reset_code_verified']);

            return redirect()
                ->route('password.request')
                ->with('error', 'O código expirou. Peça um novo código para trocar sua senha.');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $request->session()->forget(['password_reset_email', 'password_reset_code_verified']);

            return redirect()->route('password.request');
        }

        $user->update([
            'senha' => Hash::make($data['password']),
        ]);

        DB::table('password_reset_tokens')->where('email', $email)->delete();
        $request->session()->forget(['password_reset_email', 'password_reset_code_verified']);

        return redirect()
            ->route('login')
            ->with('success', 'Senha atualizada com sucesso. Entre com sua nova senha.');
    }

    public function showEditProfile()
    {
        return view('auth.edit-profile', [
            'user' => Auth::user(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('user', 'email')->ignore($user->id),
            ],
            'celular' => ['required', 'string', 'max:20'],
            'cep' => ['required', 'string', 'max:20'],
            'rua' => ['required', 'string', 'max:255'],
            'numero' => ['required', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:255'],
            'bairro' => ['required', 'string', 'max:255'],
            'cidade' => ['required', 'string', 'max:255'],
            'estado' => ['required', 'string', 'max:255'],
            'pais' => ['required', 'string', 'max:255'],
        ]);

        $user->update([
            'nome' => $data['name'],
            'email' => $data['email'],
            'celular' => $data['celular'],
            'cep' => $data['cep'],
            'rua' => $data['rua'],
            'numero' => $data['numero'],
            'complemento' => $data['complemento'] ?? null,
            'bairro' => $data['bairro'],
            'cidade' => $data['cidade'],
            'estado' => $data['estado'],
            'pais' => $data['pais'],
        ]);

        return redirect()
            ->route('dashboard.index')
            ->with('success', 'Perfil atualizado com sucesso.');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Informe seu e-mail.',
            'email.email' => 'E-mail inválido.',
            'password.required' => 'Informe sua senha.',
        ]);

        $remember = $request->boolean('remember');

        if (!Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], $remember)) {
            throw ValidationException::withMessages([
                'email' => 'E-mail ou senha incorretos.',
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if (!$user->email_verified_at) {
            if ($this->expireUnverifiedUserIfNeeded($request, $user)) {
                throw ValidationException::withMessages([
                    'email' => 'Seu cadastro expirou porque o e-mail não foi verificado em até 1 dia. Faça o cadastro novamente.',
                ]);
            }

            $user->update([
                'email_verification_code' => $this->generateEmailVerificationCode(),
            ]);

            $this->sendEmailVerificationCode($user);

            Auth::logout();
            $request->session()->put('pending_email_verification_user_id', $user->id);

            return redirect()
                ->route('cadastro.verificar.email')
                ->with('success', 'Antes de entrar, confirme seu e-mail. Enviamos um novo código.');
        }

        if (! $user->hasCompletedProfile()) {
            return redirect()->route('cadastro.completar');
        }

        if (empty($user->profile_completed_at)) {
            $user->update(['profile_completed_at' => now()]);
        }

        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function pendingEmailVerificationUser(Request $request): ?User
    {
        if (Auth::check()) {
            return Auth::user();
        }

        $userId = $request->session()->get('pending_email_verification_user_id');

        if (!$userId) {
            return null;
        }

        return User::find($userId);
    }

    private function expireUnverifiedUserIfNeeded(Request $request, ?User $user): bool
    {
        if (!$user || $user->email_verified_at || !$this->emailVerificationExpired($user)) {
            return false;
        }

        if (Auth::id() === $user->id) {
            Auth::logout();
        }

        $request->session()->forget('pending_email_verification_user_id');
        $user->delete();

        return true;
    }

    private function emailVerificationExpired(User $user): bool
    {
        return Carbon::parse($user->criado_em)->lte(now()->subDay());
    }

    private function generateEmailVerificationCode(): string
    {
        return (string) random_int(100000, 999999);
    }

    private function sendEmailVerificationCode(User $user): void
    {
            $code = $user->email_verification_code;
            $name = e($user->nome ?: 'cliente');

            $html = <<<HTML
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <title>Código de verificação - Saturn Cloud</title>
    </head>
    <body style="margin:0;padding:0;background-color:#050c08;font-family:Arial,sans-serif;color:#f0fdf6;">
        <table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 20px;">
            <tr>
                <td align="center">
                    <table width="600" cellpadding="0" cellspacing="0" style="background:#071009;border-radius:12px;overflow:hidden;border:1px solid rgba(61,255,140,0.28);">

                        <tr>
                            <td style="background:linear-gradient(135deg,#0d2e1a 0%,#0a2416 100%);padding:28px 32px;text-align:center;border-bottom:1px solid rgba(61,255,140,0.28);">
                                <h1 style="margin:0;font-size:28px;color:#8B5CF6;">
                                    Saturn Cloud
                                </h1>
                                <p style="margin:8px 0 0 0;color:#87b89d;font-size:14px;">
                                    Verificação de e-mail
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <td style="padding:32px;">

                                <p style="margin:0 0 20px 0;font-size:16px;line-height:1.6;color:#f0fdf6;">
                                    Olá, <strong>{$name}</strong>.
                                </p>

                                <p style="margin:0 0 28px 0;font-size:15px;line-height:1.7;color:#87b89d;">
                                    Use o código abaixo para verificar seu endereço de e-mail.
                                    Este código é válido por <strong style="color:#f0fdf6;">15 minutos</strong>.
                                </p>

                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td align="center">
                                            <div style="display:inline-block;background:#0c1a11;border:1px solid rgba(61,255,140,0.28);border-radius:12px;padding:24px 48px;text-align:center;">
                                                <p style="margin:0 0 8px 0;font-size:12px;color:#87b89d;letter-spacing:2px;text-transform:uppercase;">
                                                    Seu código
                                                </p>
                                                <p style="margin:0;font-size:36px;font-weight:bold;color:#8B5CF6;letter-spacing:10px;">
                                                    {$code}
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                </table>

                                <p style="margin:32px 0 0 0;font-size:14px;line-height:1.7;color:#87b89d;">
                                    Se você não solicitou este código, ignore este e-mail. Sua conta permanece segura.
                                </p>

                            </td>
                        </tr>

                        <tr>
                            <td style="padding:24px 32px;background:#050c08;border-top:1px solid rgba(61,255,140,0.1);">
                                <p style="margin:0;font-size:13px;color:#A1A1AA;text-align:center;">
                                    © Saturn Cloud — Infraestrutura de alta performance.
                                </p>
                            </td>
                        </tr>

                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>
    HTML;

        Mail::html($html, function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Código de verificação - Saturn Cloud');
        });
    }

    private function sendPasswordResetCodeEmail(User $user, string $code): void
    {
        $name = e($user->nome ?: 'cliente');

        $html = <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Código para trocar senha - Saturn Cloud</title>
</head>
<body style="margin:0;padding:0;background-color:#050c08;font-family:Arial,sans-serif;color:#f0fdf6;">
    <table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background:#071009;border-radius:12px;overflow:hidden;border:1px solid rgba(61,255,140,0.28);">
                    <tr>
                        <td style="background:linear-gradient(135deg,#0d2e1a 0%,#0a2416 100%);padding:28px 32px;text-align:center;border-bottom:1px solid rgba(61,255,140,0.28);">
                            <h1 style="margin:0;font-size:28px;color:#8B5CF6;">Saturn Cloud</h1>
                            <p style="margin:8px 0 0 0;color:#87b89d;font-size:14px;">Recuperação de senha</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 20px 0;font-size:16px;line-height:1.6;color:#f0fdf6;">
                                Olá, <strong>{$name}</strong>.
                            </p>

                            <p style="margin:0 0 28px 0;font-size:15px;line-height:1.7;color:#87b89d;">
                                Use o código abaixo para continuar a troca da sua senha.
                                Este código é válido por <strong style="color:#f0fdf6;">5 Minutos </strong>.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <div style="display:inline-block;background:#0c1a11;border:1px solid rgba(61,255,140,0.28);border-radius:12px;padding:24px 48px;text-align:center;">
                                            <p style="margin:0 0 8px 0;font-size:12px;color:#87b89d;letter-spacing:2px;text-transform:uppercase;">Seu código</p>
                                            <p style="margin:0;font-size:36px;font-weight:bold;color:#8B5CF6;letter-spacing:10px;">{$code}</p>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:32px 0 0 0;font-size:14px;line-height:1.7;color:#87b89d;">
                                Se você não pediu para trocar a senha, ignore este e-mail.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 32px;background:#050c08;border-top:1px solid rgba(61,255,140,0.1);">
                            <p style="margin:0;font-size:13px;color:#A1A1AA;text-align:center;">© Saturn Cloud — Infraestrutura de alta performance.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

        Mail::html($html, function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Código para trocar senha - Saturn Cloud');
        });
    }

    private function isValidCpfCnpj(string $value): bool
    {
        $digits = preg_replace('/\D+/', '', $value);

        if (strlen($digits) === 11) {
            return $this->isValidCpf($digits);
        }

        if (strlen($digits) === 14) {
            return $this->isValidCnpj($digits);
        }

        return false;
    }

    private function isValidCpf(string $cpf): bool
    {
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $sum = 0;

            for ($i = 0; $i < $t; $i++) {
                $sum += (int) $cpf[$i] * (($t + 1) - $i);
            }

            $digit = ((10 * $sum) % 11) % 10;

            if ((int) $cpf[$t] !== $digit) {
                return false;
            }
        }

        return true;
    }

    private function isValidCnpj(string $cnpj): bool
    {
        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        $weights = [
            [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
            [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
        ];

        for ($step = 0; $step < 2; $step++) {
            $sum = 0;

            foreach ($weights[$step] as $index => $weight) {
                $sum += (int) $cnpj[$index] * $weight;
            }

            $rest = $sum % 11;
            $digit = $rest < 2 ? 0 : 11 - $rest;

            if ((int) $cnpj[12 + $step] !== $digit) {
                return false;
            }
        }

        return true;
    }
}
