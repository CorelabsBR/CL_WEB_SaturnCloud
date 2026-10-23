@extends('layouts.app')
@section('title', 'Central de Ajuda | Saturn Cloud')
@section('meta_description', 'Tutoriais de hospedagem de sites, VPS, domínios, DNS, pagamentos e segurança explicados de forma simples.')
@push('styles')<link rel="stylesheet" href="{{ asset('css/help.css') }}?v={{ filemtime(public_path('css/help.css')) }}">@endpush
@section('body_class', 'saturn-help')
@section('content')
<section class="help-hero"><div class="container"><p class="eyebrow">Base de conhecimento</p><h1>Como podemos ajudar?</h1><p>Respostas claras para configurar sua hospedagem, domínio ou VPS — mesmo que seja sua primeira vez.</p><div class="help-search"><label class="sr-only" for="help-search">Buscar na Central de Ajuda</label><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg><input id="help-search" type="search" placeholder="Busque por DNS, SSL, Pix, VPS..." autocomplete="off" data-help-search></div><p class="help-search-status" role="status" aria-live="polite" data-help-status></p></div></section>
<div class="container support-paths"><a href="{{ route('pages.show', 'contato') }}"><h2>Suporte com contexto.</h2><p>Encontre os canais de contato com nossa equipe.</p><span>Acessar atendimento ↗</span></a><a href="{{ route('pages.show', 'contato') }}"><h2>Vamos falar do seu projeto.</h2><p>Entre em contato com a equipe para orientação comercial.</p><span>Falar com a Saturn ↗</span></a></div>
<section class="help-catalog"><div class="container" data-help-catalog>
@foreach($categories as $categorySlug => $category)
    @php($categoryArticles = $articles->filter(fn($article) => $article['category'] === $categorySlug))
    @if($categoryArticles->isNotEmpty())<section class="help-category" data-help-category><div class="help-category-head"><span>{{ str_pad((string)$loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><div><h2>{{ $category['name'] }}</h2><p>{{ $category['description'] }}</p></div></div><div class="help-article-grid">
    @foreach($categoryArticles as $slug => $article)<a class="help-article-card" href="{{ route('help.show', $slug) }}" data-help-article data-search="{{ Str::lower($article['title'].' '.$article['excerpt'].' '.$category['name']) }}"><h3>{{ $article['title'] }}</h3><p>{{ $article['excerpt'] }}</p><span>Ler artigo <b aria-hidden="true">→</b></span></a>@endforeach
    </div></section>@endif
@endforeach
<div class="help-empty" data-help-empty hidden><h2>Nenhum artigo encontrado</h2><p>Tente palavras mais curtas ou abra um chamado com a equipe.</p></div>
</div></section>
@endsection
@push('scripts')<script src="{{ asset('js/help-search.js') }}?v={{ filemtime(public_path('js/help-search.js')) }}" defer></script>@endpush
