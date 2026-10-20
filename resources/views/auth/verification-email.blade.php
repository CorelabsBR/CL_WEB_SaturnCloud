@extends('layouts.app')

@section('title', 'Verificar E-mail - Saturn Cloud')
@section('body_class', 'register-body')
@section('hide_header', true)
@section('hide_footer', true)
@section('main_class', 'register-main')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/register.css') }}?v={{ filemtime(public_path('css/register.css')) }}">
@endpush

@section('content')

<a href="{{ route('login') }}" class="register-back" title="Voltar">
    Voltar
</a>

<section class="register-split">
    <aside class="register-panel">
        <div class="register-panel-inner">
            <img src="{{ asset('img/saturno_branco_quadrado.svg') }}" alt="Saturn Cloud" class="register-logo">

            <h2 class="register-panel-title">Confirme seu e-mail.</h2>

            <p class="register-panel-sub">
                Digite o código enviado para liberar o acesso à sua conta.
            </p>
        </div>
    </aside>

    <div class="register-form-side">
        <div class="register-card">
            <h1 class="register-title">Verificar e-mail</h1>
            <p class="register-subtitle">
                Enviamos um código de 6 números para {{ $email }}.
            </p>

            @if ($errors->any())
                <div class="register-alert register-alert-error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="register-alert register-alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="register-alert register-alert-error">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('cadastro.verificar.email.post') }}" class="register-form" novalidate>
                @csrf

                <div class="register-grid">
                    <div class="register-group full">
                        <label class="register-label" for="code">Código</label>
                        <input class="register-input @error('code') is-invalid @enderror"
                               type="text"
                               id="code"
                               name="code"
                               value="{{ old('code') }}"
                               required
                               inputmode="numeric"
                               maxlength="6"
                               autocomplete="one-time-code"
                               placeholder="000000">

                        @error('code')
                            <small class="register-error">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <button class="register-submit" type="submit">
                    Verificar e entrar
                </button>
            </form>

            <form method="POST" action="{{ route('cadastro.verificar.email.reenviar') }}">
                @csrf
                <button class="register-link-button" type="submit">
                    Reenviar código
                </button>
            </form>
        </div>
    </div>
</section>

@endsection
