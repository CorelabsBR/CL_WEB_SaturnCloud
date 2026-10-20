@extends('layouts.app')

@section('title', 'Registro - Saturn Cloud')
@section('body_class', 'register-body')
@section('hide_header', true)
@section('hide_footer', true)
@section('main_class', 'register-main')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/register.css') }}?v={{ filemtime(public_path('css/register.css')) }}">
@endpush

@section('content')

<a href="{{ url('/') }}" class="register-back" title="Voltar">
    ← Voltar
</a>

<section class="register-split">
    <aside class="register-panel">
        <div class="register-panel-inner">
            <img src="{{ asset('img/saturn-cloud-logo-full.svg') }}" alt="Saturn Cloud" class="register-logo">

            <h2 class="register-panel-title">Comece com estrutura de verdade.</h2>

            <p class="register-panel-sub">
                Crie sua conta e depois complete seu cadastro para contratar serviços.
            </p>

            <div class="register-stats">
                <div>
                    <strong>01</strong>
                    <span>Cadastro</span>
                </div>
                <div>
                    <strong>02</strong>
                    <span>Validação</span>
                </div>
                <div>
                    <strong>03</strong>
                    <span>Contratação</span>
                </div>
            </div>
        </div>
    </aside>

    <div class="register-form-side">
        <div class="register-card">
            <h1 class="register-title">Criar conta</h1>
            <p class="register-subtitle">Primeiro, crie seu acesso. Depois você completa seus dados.</p>

            @if (session('error'))
                <div class="register-alert register-alert-error">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('success'))
                <div class="register-alert register-alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('registro.post') }}" class="register-form" novalidate>
                @csrf

                <div class="register-grid">
                    <div class="register-group full">
                        <label class="register-label" for="email">E-mail</label>
                        <input class="register-input" type="email" id="email" name="email"
                               value="{{ old('email') }}" required placeholder="seu@email.com">

                        @error('email')
                            <small class="register-error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="register-group full">
                        <label class="register-label" for="password">Senha</label>
                        <div class="register-password-wrapper">
                            <input class="register-input" type="password" id="password" name="password"
                                   required placeholder="Mínimo 8 caracteres">
                            <button type="button" class="register-password-toggle" onclick="toggleRegisterPassword('password')" aria-label="Mostrar ou ocultar senha">Mostrar</button>
                        </div>

                        @error('password')
                            <small class="register-error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="register-group full">
                        <label class="register-label" for="password_confirmation">Confirmar senha</label>
                        <div class="register-password-wrapper">
                            <input class="register-input" type="password" id="password_confirmation"
                                   name="password_confirmation" required placeholder="Repita a senha">
                            <button type="button" class="register-password-toggle" onclick="toggleRegisterPassword('password_confirmation')" aria-label="Mostrar ou ocultar confirmação da senha">Mostrar</button>
                        </div>
                    </div>
                </div>

                <button class="register-submit" type="submit">
                    Criar conta
                </button>
            </form>

            <p class="register-note">
                Já tem uma conta?
                <a href="{{ route('login') }}">Entrar</a>
            </p>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
function toggleRegisterPassword(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
@endpush
