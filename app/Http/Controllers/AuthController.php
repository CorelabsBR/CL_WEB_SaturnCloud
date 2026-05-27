<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard.index');
        }

        return view('auth.login');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard.index');
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

        $user = User::create([
            'email' => $data['email'],
            'senha' => Hash::make($data['password']),
            'tipo_usuario' => 'cliente',
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()
            ->route('cadastro.completar')
            ->with('success', 'Conta criada. Complete seu cadastro para continuar.');
    }

    public function showCompleteProfile()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->hasCompletedProfile()) {
            if (empty($user->profile_completed_at)) {
                $user->update(['profile_completed_at' => now()]);
            }

            return redirect()->route('dashboard.index');
        }

        return view('auth.complete-profile');
    }

    public function completeProfile(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
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
                'unique:user,cpf_cnpj,' . $user->id,
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

        $user->update([
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
        ]);

        return redirect()
            ->route('dashboard.index')
            ->with('success', 'Cadastro completo com sucesso.');
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
            'pterodactyl_fivem_license' => ['nullable', 'string', 'max:255'],
            'pterodactyl_cfx_license' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update([
            'nome' => $data['name'],
            'email' => $data['email'],
            'pterodactyl_fivem_license' => $data['pterodactyl_fivem_license'] ?? null,
            'pterodactyl_cfx_license' => $data['pterodactyl_cfx_license'] ?? null,
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
            'email.required' => 'Mete teu e-mail.',
            'email.email' => 'E-mail inválido.',
            'password.required' => 'Mete tua senha.',
        ]);

        $remember = $request->boolean('remember');

        if (!Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], $remember)) {
            throw ValidationException::withMessages([
                'email' => 'Credenciais inválidas, товарищ.',
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();

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
