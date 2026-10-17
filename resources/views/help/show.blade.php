@extends('layouts.app')
@section('title', $article['title'].' | Central de Ajuda Vortex')
@section('meta_description', $article['excerpt'])
@push('styles')<link rel="stylesheet" href="{{ asset('css/help.css') }}?v={{ filemtime(public_path('css/help.css')) }}">@endpush
@section('content')
<div class="help-article-layout container">
    <nav class="help-breadcrumb" aria-label="Navegação estrutural"><a href="{{ route('help.index') }}">Central de Ajuda</a><span>/</span><span>{{ config('help.categories.'.$article['category'].'.name') }}</span></nav>
    <article class="help-article"><header><p class="eyebrow">{{ config('help.categories.'.$article['category'].'.name') }}</p><h1>{{ $article['title'] }}</h1><p>{{ $article['excerpt'] }}</p></header>
    <div class="help-article-content">@foreach($article['sections'] as $section)<section>@if($section['heading'])<h2>{{ $section['heading'] }}</h2>@endif @foreach($section['paragraphs'] as $paragraph)<p>{{ $paragraph }}</p>@endforeach @if($section['items'])<ol>@foreach($section['items'] as $item)<li>{{ $item }}</li>@endforeach</ol>@endif</section>@endforeach</div>
    <aside class="help-support-cta"><div><strong>Ainda com dúvida?</strong><p>Entre na sua conta e envie os detalhes para nossa equipe.</p></div><a class="btn btn-primary" href="{{ auth()->check() ? route('ticket.index') : route('login') }}">Abrir um chamado</a></aside></article>
    @if($related->isNotEmpty())<section class="help-related"><h2>Continue nesta categoria</h2><div>@foreach($related as $relatedSlug => $item)<a href="{{ route('help.show', $relatedSlug) }}">{{ $item['title'] }} <span>→</span></a>@endforeach</div></section>@endif
</div>
<script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@type'=>'TechArticle','headline'=>$article['title'],'description'=>$article['excerpt'],'publisher'=>['@type'=>'Organization','name'=>'Vortex'],'mainEntityOfPage'=>url()->current()], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
@endsection
