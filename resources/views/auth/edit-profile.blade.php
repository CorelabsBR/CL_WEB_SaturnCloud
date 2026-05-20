@extends('layouts.app')

@section('title', 'Editar Perfil - VortexHost')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/profile-edit.css') }}?v={{ filemtime(public_path('css/profile-edit.css')) }}">
@endpush

@section('content')
@php
    $profileName = old('name', $user?->nome ?? 'Cliente');
    $profileEmail = old('email', $user?->email ?? '');
    $profileInitial = strtoupper(substr(trim($profileName) ?: 'C', 0, 1));
@endphp

<div class="client-page">
    <section class="profile-edit-page">
        <div class="container">
            <div class="profile-edit-panel">
                <aside class="profile-edit-summary">
                    <div class="profile-edit-avatar" aria-hidden="true">
                        {{ $profileInitial }}
                    </div>
                    <div>
                        <span>Conta</span>
                        <h2>{{ $profileName }}</h2>
                        <p>{{ $profileEmail ?: 'E-mail não informado' }}</p>
                    </div>

                    <div class="profile-edit-note">
                        <strong>Dados permitidos</strong>
                        <span>Você pode alterar somente nome e e-mail. CPF e dados cadastrais sensíveis ficam protegidos.</span>
                    </div>
                </aside>

                <div class="profile-edit-content">
                    <div class="profile-edit-head">
                        <span>Perfil</span>
                        <h2>Editar perfil</h2>
                        <p>Atualize as informações básicas usadas para identificar sua conta no painel.</p>
                    </div>

                    @if ($errors->any())
                        <div class="flash flash-error">
                            Revise os campos e tente novamente.
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="flash flash-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('perfil.update') }}" class="profile-edit-form">
                        @csrf
                        @method('PATCH')

                        <label class="profile-field" for="name">
                            <span>Nome</span>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                value="{{ old('name', $user?->nome) }}"
                                required
                                maxlength="255"
                            >
                            @error('name')
                                <small>{{ $message }}</small>
                            @enderror
                        </label>

                        <label class="profile-field" for="email">
                            <span>E-mail</span>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email', $user?->email) }}"
                                required
                                maxlength="255"
                            >
                            @error('email')
                                <small>{{ $message }}</small>
                            @enderror
                        </label>

                        <div class="profile-edit-actions">
                            <a href="{{ route('dashboard.index') }}#perfil" class="btn btn-ghost">
                                Voltar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Salvar alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
