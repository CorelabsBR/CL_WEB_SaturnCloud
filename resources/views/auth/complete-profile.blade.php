@extends('layouts.app')

@section('title', 'Completar Cadastro - VortexHost')
@section('body_class', 'register-body complete-profile-body')
@section('hide_header', true)
@section('hide_footer', true)
@section('main_class', 'register-main')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/register.css') }}?v={{ filemtime(public_path('css/register.css')) }}">
@endpush

@section('content')
@php
    $profileUser = $profileUser ?? auth()->user();
@endphp

<section class="complete-profile-shell">
    <header class="complete-profile-header">
        <div class="complete-profile-brand">
            <img src="{{ asset('img/vortexhost-logo-full.png') }}" alt="VortexHost" class="complete-profile-logo">
        </div>
        <div class="complete-profile-intro">
            <span class="complete-profile-eyebrow">Última etapa</span>
            <h1 class="register-title">Complete seu cadastro</h1>
            <p class="register-subtitle">Precisamos destas informações para liberar sua conta e emitir seus serviços.</p>
        </div>
        <span class="complete-profile-progress">Cadastro <strong>2 de 2</strong></span>
    </header>

    <div class="complete-profile-content">
        <div class="register-card">
@if ($errors->any())
    <div class="register-alert register-alert-error">
        <strong>Erro ao completar cadastro:</strong>

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
            <form method="POST" action="{{ route('cadastro.completar.post') }}" class="register-form" novalidate>
                @csrf

                <fieldset class="profile-section">
                    <legend>
                        <strong>Informações pessoais</strong>
                        <small>Dados do titular da conta</small>
                    </legend>

                    <div class="register-grid profile-personal-grid">
                    <div class="register-group profile-field-name">
                        <label class="register-label" for="name">Nome completo</label>
                        <input class="register-input @error('name') is-invalid @enderror" type="text" id="name" name="name"
                               value="{{ old('name', $profileUser?->nome) }}" required autocomplete="name">
                    </div>

                    <div class="register-group profile-field-document">
                        <label class="register-label" for="cpf_cnpj">CPF / CNPJ</label>
                        <input class="register-input @error('cpf_cnpj') is-invalid @enderror" type="text" id="cpf_cnpj" name="cpf_cnpj"
                               value="{{ old('cpf_cnpj', $profileUser?->cpf_cnpj) }}" required inputmode="numeric" maxlength="18" data-mask="document">
                    </div>

                    <div class="register-group profile-field-phone">
                        <label class="register-label" for="celular">Celular</label>
                        <input class="register-input @error('celular') is-invalid @enderror" type="text" id="celular" name="celular"
                               value="{{ old('celular', $profileUser?->celular) }}" required inputmode="numeric" maxlength="15" autocomplete="tel" data-mask="phone">
                    </div>

                    <div class="register-group profile-field-birth">
                        <label class="register-label" for="data_nasc">Data de nascimento</label>
                        <input class="register-input @error('data_nasc') is-invalid @enderror" type="date" id="data_nasc" name="data_nasc"
                               value="{{ old('data_nasc', optional($profileUser?->data_nasc)->format('Y-m-d')) }}" required>
                    </div>
                    </div>
                </fieldset>

                <fieldset class="profile-section">
                    <legend>
                        <strong>Endereço de cobrança</strong>
                        <small>O CEP completa os campos automaticamente</small>
                    </legend>

                    <div class="register-grid profile-address-grid">
                    <div class="register-group profile-cep-group">
                        <label class="register-label" for="cep">CEP</label>
                        <input class="register-input @error('cep') is-invalid @enderror" type="text" id="cep" name="cep"
                               value="{{ old('cep', $profileUser?->cep) }}" required inputmode="numeric" maxlength="9" autocomplete="postal-code" data-mask="cep" aria-describedby="cep-status">
                        <span id="cep-status" class="cep-status" role="status" aria-live="polite">Informe os 8 números do CEP.</span>
                    </div>

                    <div class="register-group profile-field-street">
                        <label class="register-label" for="rua">Rua</label>
                        <input class="register-input @error('rua') is-invalid @enderror" type="text" id="rua" name="rua"
                               value="{{ old('rua', $profileUser?->rua) }}" required autocomplete="address-line1">
                    </div>

                    <div class="register-group profile-field-number">
                        <label class="register-label" for="numero">Número</label>
                        <input class="register-input @error('numero') is-invalid @enderror" type="text" id="numero" name="numero"
                               value="{{ old('numero', $profileUser?->numero) }}" required autocomplete="address-line2">
                    </div>

                    <div class="register-group profile-field-complement">
                        <label class="register-label" for="complemento">Complemento</label>
                        <input class="register-input @error('complemento') is-invalid @enderror" type="text" id="complemento" name="complemento"
                               value="{{ old('complemento', $profileUser?->complemento) }}" autocomplete="address-line3">
                    </div>

                    <div class="register-group profile-field-district">
                        <label class="register-label" for="bairro">Bairro</label>
                        <input class="register-input @error('bairro') is-invalid @enderror" type="text" id="bairro" name="bairro"
                               value="{{ old('bairro', $profileUser?->bairro) }}" required autocomplete="address-level3">
                    </div>

                    <div class="register-group profile-field-city">
                        <label class="register-label" for="cidade">Cidade</label>
                        <input class="register-input @error('cidade') is-invalid @enderror" type="text" id="cidade" name="cidade"
                               value="{{ old('cidade', $profileUser?->cidade) }}" required autocomplete="address-level2">
                    </div>

                    <div class="register-group profile-field-state">
                        <label class="register-label" for="estado">Estado</label>
                        <input class="register-input @error('estado') is-invalid @enderror" type="text" id="estado" name="estado"
                               value="{{ old('estado', $profileUser?->estado) }}" required autocomplete="address-level1" maxlength="2">
                    </div>

                    <div class="register-group profile-field-country">
                        <label class="register-label" for="pais">País</label>
                        <input class="register-input @error('pais') is-invalid @enderror" type="text" id="pais" name="pais"
                               value="{{ old('pais', $profileUser?->pais ?? 'Brasil') }}" required autocomplete="country-name">
                    </div>
                    </div>
                </fieldset>

                <div class="profile-submit-row">
                    <p>Seus dados ficam protegidos e são usados apenas na prestação dos serviços.</p>
                    <button class="register-submit" type="submit">Concluir cadastro <span aria-hidden="true">→</span></button>
                </div>
            </form>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script src="{{ asset('js/profile-masks.js') }}?v={{ filemtime(public_path('js/profile-masks.js')) }}"></script>
@endpush
