@extends('layouts.app')

@section('title', 'Página não encontrada | Saturn Cloud')
@section('meta_description', 'Parece que saímos de órbita. Volte ao início da Saturn Cloud ou encontre ajuda.')
@section('body_class', 'error404')
@section('main_class', 'error404__main')
@section('hide_footer', true)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/error404.css') }}?v={{ filemtime(public_path('css/error404.css')) }}">
@endpush

@section('content')
    <section class="error404__scene" aria-labelledby="error404-title">
        <div class="container error404__layout">
            <div class="error404__copy">
                <p class="error404__code">Erro 404 · Página não encontrada</p>
                <h1 id="error404-title">
                    <span class="error404__title-line">Tentando estabelecer</span>
                    <span class="error404__title-line">Contato com a nave</span>
                </h1>
                <p class="error404__description">
                    A página que você procura não está por aqui.
                    Vamos voltar para um lugar conhecido?
                </p>
                <div class="error404__actions">
                    <a class="error404__home" href="{{ route('home') }}">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path d="m14 6-6 6 6 6M8 12h12" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Voltar ao início
                    </a>
                    <a class="error404__help" href="{{ route('help.index') }}">Encontrar ajuda</a>
                </div>
            </div>
            <div class="error404__illustration">
                <img
                    class="error404__astronaut"
                    src="{{ asset('img/astronaut-brazil-cutout-404.png') }}"
                    alt="Astronauta com a bandeira do Brasil no braço, tocando o capacete como quem tenta restabelecer contato."
                    width="1024"
                    height="1536"
                    fetchpriority="high"
                    decoding="async"
                >
            </div>
        </div>
    </section>
@endsection
