@extends('layouts.app')
@section('title', $content[0] . ' | Vortex')
@section('meta_description', $content[1])
@section('content')
<section class="institutional-hero"><div class="container"><p class="eyebrow">Vortex / {{ $content[0] }}</p><h1>{{ $content[1] }}</h1><p>{{ $content[2] }}</p>
    <div class="institutional-actions">
        @if($page === 'dominios')<a class="btn btn-primary" href="https://registro.br/tecnologia/ferramentas/whois/" target="_blank" rel="noopener">Consultar no Registro.br</a>@else<a class="btn btn-primary" href="{{ auth()->check() ? route('ticket.index') : route('registro') }}">{{ auth()->check() ? 'Abrir chamado' : 'Criar conta' }}</a>@endif
        <a class="btn btn-ghost" href="{{ route('home') }}">Voltar ao início</a>
    </div>
</div></section>
<section class="institutional-body"><div class="container"><div class="institutional-grid">@foreach($content[3] as $index => $item)<article><span>0{{ $index + 1 }}</span><h2>{{ $item }}</h2></article>@endforeach</div>
@if($page === 'contato')<div class="institutional-contact"><a href="mailto:contato@corelabs.dev.br">contato@corelabs.dev.br</a></div>@endif
</div></section>
@endsection
