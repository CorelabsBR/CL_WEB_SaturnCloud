@extends('layouts.app')

@section('title', 'Acesso negado | Saturn Cloud')
@section('meta_description', 'Acesso negado. Área restrita da estação Saturn Cloud.')
@section('body_class', 'error403')
@section('main_class', 'error403__main')
@section('hide_footer', true)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/error403.css') }}?v={{ filemtime(public_path('css/error403.css')) }}">
@endpush

@section('content')
    <section class="error403__scene" aria-labelledby="error403-title">
        <div class="container error403__layout">
            <div class="error403__copy">
                <p class="error403__code">Erro 403 · Acesso não autorizado</p>
                <h1 id="error403-title">
                    <span class="error403__title-line">Acesso negado.</span>
                    <span class="error403__title-line">Área restrita da estação.</span>
                </h1>
                <p class="error403__description">
                    Você não tem permissão para acessar esta área.
                    Volte ao início ou encontre ajuda para continuar.
                </p>
                <div class="error403__actions">
                    <a class="error403__home" href="{{ route('home') }}">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path d="m14 6-6 6 6 6M8 12h12" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Voltar ao início
                    </a>
                    <a class="error403__help" href="{{ route('help.index') }}">Encontrar ajuda</a>
                </div>
            </div>
            <div class="error403__illustration">
                <img
                    class="error403__guard"
                    src="{{ asset('img/station-guard-403.png') }}"
                    alt="Militar da estação com armadura escura e os antebraços cruzados em X, sinalizando acesso negado."
                    width="1024"
                    height="1536"
                    fetchpriority="high"
                    decoding="async"
                >
            </div>
        </div>
    </section>
@endsection
