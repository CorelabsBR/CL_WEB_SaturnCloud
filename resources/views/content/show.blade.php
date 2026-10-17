@extends('layouts.app')
@section('title', ($content->seo_title ?: $content->title) . ' | VortexHost')
@section('meta_description', $content->seo_description ?: $content->excerpt)
@section('content')<article class="catalog-section"><div class="container" style="max-width:860px"><p class="eyebrow">{{ $content->category ?: $content->type }}</p><h1 class="section-title">{{ $content->title }}</h1><p class="section-sub">Publicado em {{ $content->published_at->format('d/m/Y') }}</p><div style="margin-top:3rem;color:var(--text);line-height:1.8;white-space:pre-wrap">{{ $content->body_markdown }}</div></div></article>@endsection
