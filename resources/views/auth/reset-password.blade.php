@extends('layouts.app')

@section('title', 'Nova Senha - VortexHost')
@section('body_class', 'auth-body')
@section('hide_header', true)
@section('hide_footer', true)
@section('main_class', 'auth-main')

@section('content')
<a href="{{ route('password.code') }}" class="auth-back" title="Voltar">
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
            <img src="{{ asset('img/vortexhost-logo-full.png') }}" alt="VortexHost" class="auth-logo">

            <h2 class="auth-panel-title">Crie uma nova senha.</h2>

            <p class="auth-panel-sub">
                Depois disso, você já pode entrar<br>
                normalmente na área do cliente.
            </p>
        </div>

        <div class="auth-panel-grid"></div>
    </div>

    <div class="auth-form-side">
        <div class="auth-card">
            <h1 class="auth-title">Nova senha</h1>
            <p class="auth-subtitle">
                Escolha uma senha segura para recuperar o acesso à sua conta.
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

            @if (session('error'))
                <div class="auth-error-box">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" novalidate>
                @csrf

                <div class="form-group">
                    <label class="form-label" for="password">Nova senha</label>
                    <input class="form-input @error('password') is-error @enderror"
                           type="password"
                           id="password"
                           name="password"
                           required
                           autocomplete="new-password"
                           placeholder="••••••••">
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Confirmar senha</label>
                    <input class="form-input"
                           type="password"
                           id="password_confirmation"
                           name="password_confirmation"
                           required
                           autocomplete="new-password"
                           placeholder="Repita a senha">
                </div>

                <button class="btn btn-primary btn-full" type="submit">
                    Trocar senha
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
