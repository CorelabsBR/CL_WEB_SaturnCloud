@extends('layouts.app')

@section('title', 'Código de Recuperação - Saturn Cloud')
@section('body_class', 'auth-body')
@section('hide_header', true)
@section('hide_footer', true)
@section('main_class', 'auth-main')

@section('content')
<a href="{{ route('password.request') }}" class="auth-back" title="Voltar">
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

            <h2 class="auth-panel-title">Confirme o código.</h2>

            <p class="auth-panel-sub">
                Essa etapa protege sua conta<br>
                antes de liberar a troca de senha.
            </p>

            <div class="auth-stats">
                <div class="auth-stat">
                    <span class="auth-stat-value">6</span>
                    <span class="auth-stat-label">Dígitos</span>
                </div>
                <div class="auth-stat-divider"></div>
                <div class="auth-stat">
                    <span class="auth-stat-value">1</span>
                    <span class="auth-stat-label">E-mail</span>
                </div>
                <div class="auth-stat-divider"></div>
                <div class="auth-stat">
                    <span class="auth-stat-value">60min</span>
                    <span class="auth-stat-label">Validade</span>
                </div>
            </div>
        </div>

        <div class="auth-panel-grid"></div>
    </div>

    <div class="auth-form-side">
        <div class="auth-card">
            <h1 class="auth-title">Digite o código</h1>
            <p class="auth-subtitle">
                Enviamos um código de 6 números para {{ $email }}.
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

            <form method="POST" action="{{ route('password.code.verify') }}" novalidate>
                @csrf

                <div class="form-group">
                    <label class="form-label" for="code">Código</label>
                    <input class="form-input @error('code') is-error @enderror"
                        type="text"
                        id="code"
                        name="code"
                        value="{{ old('code') }}"
                        required
                        inputmode="numeric"
                        maxlength="6"
                        autocomplete="one-time-code"
                        placeholder="000000">
                </div>

                <button class="btn btn-primary btn-full" type="submit">
                    Confirmar código
                </button>
            </form>

            <form method="POST" action="{{ route('password.email') }}" style="margin-top: 1rem;">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                <button class="btn btn-ghost btn-full" type="submit">
                    Reenviar código
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
