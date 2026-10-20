@extends('layouts.app')

@section('title', 'Pagina nao encontrada - Saturn Cloud')
@section('main_class', 'not-found-main')

@section('content')
<section class="not-found-page">
    <div class="not-found-bg" aria-hidden="true"></div>

    <div class="container">
        <div class="not-found-panel">
            <div class="not-found-copy">
                <span class="not-found-code">404</span>
                <h1>Página Não Encontrada</h1>
                <p>
                    O endereço que você tentou acessar pode ter sido alterado, expirado ou não estar mais disponível. Retorne para uma área segura da Saturn Cloud.
                </p>

                <div class="not-found-actions">
                    <a class="btn btn-primary btn-lg" href="{{ route('home') }}">Ir para o ínicio</a>
                </div>
            </div>

            <div class="not-found-brand" aria-hidden="true">
                <img src="{{ asset('img/saturn-cloud-mark.svg') }}" alt="">
                <div class="not-found-orbit"></div>
            </div>
        </div>
    </div>
</section>
@endsection
