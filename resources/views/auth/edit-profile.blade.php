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
                        <span>Você pode alterar seus dados de contato e endereço.</span>
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

                        <label class="profile-field" for="celular">
                            <span>Celular</span>
                            <input
                                id="celular"
                                name="celular"
                                type="text"
                                value="{{ old('celular', $user?->celular) }}"
                                required
                                inputmode="numeric"
                                maxlength="15"
                                autocomplete="tel"
                                data-mask="phone"
                            >
                            @error('celular')
                                <small>{{ $message }}</small>
                            @enderror
                        </label>

                        <div class="profile-edit-grid">
                            <label class="profile-field" for="cep">
                                <span>CEP</span>
                                <input
                                    id="cep"
                                    name="cep"
                                    type="text"
                                    value="{{ old('cep', $user?->cep) }}"
                                    required
                                    inputmode="numeric"
                                    maxlength="9"
                                    autocomplete="postal-code"
                                    data-mask="cep"
                                >
                                @error('cep')
                                    <small>{{ $message }}</small>
                                @enderror
                            </label>

                            <label class="profile-field" for="numero">
                                <span>Número</span>
                                <input
                                    id="numero"
                                    name="numero"
                                    type="text"
                                    value="{{ old('numero', $user?->numero) }}"
                                    required
                                    maxlength="20"
                                >
                                @error('numero')
                                    <small>{{ $message }}</small>
                                @enderror
                            </label>
                        </div>

                        <label class="profile-field" for="rua">
                            <span>Rua</span>
                            <input
                                id="rua"
                                name="rua"
                                type="text"
                                value="{{ old('rua', $user?->rua) }}"
                                required
                                maxlength="255"
                            >
                            @error('rua')
                                <small>{{ $message }}</small>
                            @enderror
                        </label>

                        <label class="profile-field" for="complemento">
                            <span>Complemento</span>
                            <input
                                id="complemento"
                                name="complemento"
                                type="text"
                                value="{{ old('complemento', $user?->complemento) }}"
                                maxlength="255"
                            >
                            @error('complemento')
                                <small>{{ $message }}</small>
                            @enderror
                        </label>

                        <div class="profile-edit-grid">
                            <label class="profile-field" for="bairro">
                                <span>Bairro</span>
                                <input
                                    id="bairro"
                                    name="bairro"
                                    type="text"
                                    value="{{ old('bairro', $user?->bairro) }}"
                                    required
                                    maxlength="255"
                                >
                                @error('bairro')
                                    <small>{{ $message }}</small>
                                @enderror
                            </label>

                            <label class="profile-field" for="cidade">
                                <span>Cidade</span>
                                <input
                                    id="cidade"
                                    name="cidade"
                                    type="text"
                                    value="{{ old('cidade', $user?->cidade) }}"
                                    required
                                    maxlength="255"
                                >
                                @error('cidade')
                                    <small>{{ $message }}</small>
                                @enderror
                            </label>
                        </div>

                        <div class="profile-edit-grid">
                            <label class="profile-field" for="estado">
                                <span>Estado</span>
                                <input
                                    id="estado"
                                    name="estado"
                                    type="text"
                                    value="{{ old('estado', $user?->estado) }}"
                                    required
                                    maxlength="255"
                                >
                                @error('estado')
                                    <small>{{ $message }}</small>
                                @enderror
                            </label>

                            <label class="profile-field" for="pais">
                                <span>País</span>
                                <input
                                    id="pais"
                                    name="pais"
                                    type="text"
                                    value="{{ old('pais', $user?->pais ?? 'Brasil') }}"
                                    required
                                    maxlength="255"
                                >
                                @error('pais')
                                    <small>{{ $message }}</small>
                                @enderror
                            </label>
                        </div>

                        <div class="profile-edit-actions">
                            <a href="{{ route('dashboard.index') }}" class="btn btn-ghost">
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

@push('scripts')
    <script src="{{ asset('js/profile-masks.js') }}?v={{ filemtime(public_path('js/profile-masks.js')) }}"></script>
@endpush
