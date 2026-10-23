@extends('layouts.app')
@section('title', 'Conteúdos técnicos | Saturn Cloud')
@section('content')
<section class="catalog-section"><div class="container"><h1 class="section-title">Conteúdos e tutoriais</h1><p>Consulte os guias disponíveis na nossa Central de Ajuda.</p><a class="btn btn-primary" href="{{ route('help.index') }}">Consultar guias</a></div></section>
@endsection
