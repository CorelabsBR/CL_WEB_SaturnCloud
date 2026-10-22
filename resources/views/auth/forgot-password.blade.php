@extends('layouts.app')

@section('title', 'Recuperar Senha - Saturn Cloud')
@section('body_class', 'auth-body')
@section('hide_header', true)
@section('hide_footer', true)
@section('main_class', 'auth-main')

@section('content')
<a href="{{ route('login') }}" class="auth-back" title="Voltar">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
        fill="none" stroke="currentColor" stroke-width="2.5"
        stroke-linecap="round" stroke-linejoin="round">
        <path d="M19 12H5"/>
        <path d="M12 19l-7-7 7-7"/>
    </svg>
    Voltar
</a>

<div class="auth-split">
    <div class="auth-panel">
        <div class="auth-panel-inner">
            <img src="{{ asset('img/logo-128.png') }}" alt="Saturn Cloud" class="auth-logo">

            <h2 class="auth-panel-title">Recupere o acesso.</h2>

            <p class="auth-panel-sub">
                Enviaremos um código de segurança<br>
                para o e-mail da sua conta.
            </p>

            <div class="auth-stats">
                <div class="auth-stat">
                    <span class="auth-stat-value">6</span>
                    <span class="auth-stat-label">Dígitos</span>
                </div>
                <div class="auth-stat-divider"></div>
                <div class="auth-stat">
                    <span class="auth-stat-value">5 Min</span>
                    <span class="auth-stat-label">Validade</span>
                </div>
                <div class="auth-stat-divider"></div>
                <div class="auth-stat">
                    <span class="auth-stat-value">E-mail</span>
                    <span class="auth-stat-label">Seguro</span>
                </div>
            </div>
        </div>

        <div class="auth-panel-grid"></div>
    </div>

    <div class="auth-form-side">
        <div class="auth-card">
            <h1 class="auth-title">Esqueceu sua senha?</h1>
            <p class="auth-subtitle">
                Informe seu e-mail para receber o código que permite criar uma nova senha.
            </p>

            @if ($errors->any())
                <div class="auth-error-box">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="auth-success-box">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" novalidate>
                @csrf

                <div class="form-group">
                    <label class="form-label" for="email">E-mail</label>
                    <input class="form-input @error('email') is-error @enderror"
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                        placeholder="seu@email.com">
                </div>

                <button class="btn btn-primary btn-full" type="submit">
                    Enviar código
                </button>
            </form>

            <p class="auth-note">
                Lembrou sua senha?
                <a href="{{ route('login') }}">Entrar na conta</a>
            </p>
        </div>
    </div>
</div>
@endsection
