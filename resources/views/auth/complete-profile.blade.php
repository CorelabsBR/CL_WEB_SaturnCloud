@extends('layouts.app')

@section('title', 'Completar Cadastro - VortexHost')
@section('body_class', 'register-body')
@section('hide_header', true)
@section('hide_footer', true)
@section('main_class', 'register-main')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/register.css') }}?v={{ filemtime(public_path('css/register.css')) }}">
@endpush

@section('content')

<section class="register-split">
    <aside class="register-panel">
        <div class="register-panel-inner">
            <img src="{{ asset('img/logo_vtxhost.png') }}" alt="VortexHost" class="register-logo">

            <h2 class="register-panel-title">Finalize seu cadastro.</h2>

            <p class="register-panel-sub">
                Antes de contratar qualquer serviço, precisamos dos seus dados cadastrais.
            </p>
        </div>
    </aside>

    <div class="register-form-side">
        <div class="register-card">
            <h1 class="register-title">Completar cadastro</h1>
            <p class="register-subtitle">
                Isso é obrigatório para contratar hospedagem, VPS ou servidores de jogos.
            </p>
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

                <div class="register-grid">
                    <div class="register-group full">
                        <label class="register-label" for="name">Nome completo</label>
                        <input class="register-input @error('name') is-invalid @enderror" type="text" id="name" name="name"
                               value="{{ old('name', auth()->user()->nome) }}" required>
                    </div>

                    <div class="register-group">
                        <label class="register-label" for="cpf_cnpj">CPF / CNPJ</label>
                        <input class="register-input @error('cpf_cnpj') is-invalid @enderror" type="text" id="cpf_cnpj" name="cpf_cnpj"
                               value="{{ old('cpf_cnpj', auth()->user()->cpf_cnpj) }}" required>
                    </div>

                    <div class="register-group">
                        <label class="register-label" for="celular">Celular</label>
                        <input class="register-input @error('celular') is-invalid @enderror" type="text" id="celular" name="celular"
                               value="{{ old('celular', auth()->user()->celular) }}" required>
                    </div>

                    <div class="register-group">
                        <label class="register-label" for="data_nasc">Data de nascimento</label>
                        <input class="register-input @error('data_nasc') is-invalid @enderror" type="date" id="data_nasc" name="data_nasc"
                               value="{{ old('data_nasc', optional(auth()->user()->data_nasc)->format('Y-m-d')) }}" required>
                    </div>

                    <div class="register-group">
                        <label class="register-label" for="cep">CEP</label>
                        <input class="register-input @error('name') is-invalid @enderror" type="text" id="cep" name="cep"
                               value="{{ old('cep', auth()->user()->cep) }}" required>
                    </div>

                    <div class="register-group full">
                        <label class="register-label" for="rua">Rua</label>
                        <input class="register-input @error('name') is-invalid @enderror" type="text" id="rua" name="rua"
                               value="{{ old('rua', auth()->user()->rua) }}" required>
                    </div>

                    <div class="register-group">
                        <label class="register-label" for="numero">Número</label>
                        <input class="register-input @error('name') is-invalid @enderror" type="text" id="numero" name="numero"
                               value="{{ old('numero', auth()->user()->numero) }}" required>
                    </div>

                    <div class="register-group">
                        <label class="register-label" for="complemento">Complemento</label>
                        <input class="register-input @error('name') is-invalid @enderror" type="text" id="complemento" name="complemento"
                               value="{{ old('complemento', auth()->user()->complemento) }}">
                    </div>

                    <div class="register-group">
                        <label class="register-label" for="bairro">Bairro</label>
                        <input class="register-input @error('name') is-invalid @enderror" type="text" id="bairro" name="bairro"
                               value="{{ old('bairro', auth()->user()->bairro) }}" required>
                    </div>

                    <div class="register-group">
                        <label class="register-label" for="cidade">Cidade</label>
                        <input class="register-input @error('name') is-invalid @enderror" type="text" id="cidade" name="cidade"
                               value="{{ old('cidade', auth()->user()->cidade) }}" required>
                    </div>

                    <div class="register-group">
                        <label class="register-label" for="estado">Estado</label>
                        <input class="register-input @error('name') is-invalid @enderror" type="text" id="estado" name="estado"
                               value="{{ old('estado', auth()->user()->estado) }}" required>
                    </div>

                    <div class="register-group">
                        <label class="register-label" for="pais">País</label>
                        <input class="register-input @error('name') is-invalid @enderror" type="text" id="pais" name="pais"
                               value="{{ old('pais', auth()->user()->pais ?? 'Brasil') }}" required>
                    </div>
                </div>

                <button class="register-submit" type="submit">
                    Salvar cadastro
                </button>
            </form>
        </div>
    </div>
</section>

@endsection
