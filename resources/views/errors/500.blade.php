@extends('layouts.app')

@section('title', 'Erro de Servidor - VortexHost')
@section('main_class', 'not-found-main')
@section('hide_header', true)
@section('hide_footer', true)

@section('content')
<section class="not-found-page">
    <div class="not-found-bg" aria-hidden="true"></div>

    <div class="container">
        <div class="not-found-panel">
            <div class="not-found-copy">
                <span class="not-found-code">500</span>
                <h1>Erro Interno</h1>
                <p>
                    Houve um problema de nossa parte. Pedimos desculpas pelo ocorrido e agradecemos pela compreensão.
                </p>
            </div>

            <div class="not-found-brand" aria-hidden="true">
                <img src="{{ asset('img/vortex-logo-mark.png') }}" alt="">
                <div class="not-found-orbit"></div>
            </div>
        </div>
    </div>
</section>
@endsection
