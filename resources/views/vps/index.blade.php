@extends('layouts.app')
@section('title', 'Servidores VPS | Saturn Cloud')
@section('meta_description', 'Servidores VPS para aplicações, sites e ambientes profissionais, com gestão centralizada pela área do cliente.')
@section('content')
<section class="vps-hero">
    <div class="container vps-hero-grid">
        <div>
            <p class="eyebrow">Infraestrutura virtual</p>
            <h1>VPS para colocar projetos sérios em produção.</h1>
            <p>Recursos dedicados para aplicações, APIs, bancos de dados e múltiplos sites. Escolha a configuração e acompanhe tudo pela área do cliente.</p>
            <div class="vps-hero-actions">
                <a class="btn btn-primary btn-lg" href="#planos-vps">Ver configurações</a>
                <a class="btn btn-ghost btn-lg" href="{{ auth()->check() ? route('ticket.index') : route('login') }}">Falar com suporte</a>
            </div>
        </div>
        <div class="vps-terminal" aria-label="Características da infraestrutura VPS">
            <div class="vps-terminal-head"><span></span><span></span><span></span><strong>SATURN CLOUD</strong></div>
            <dl>
                <div><dt>Ambiente</dt><dd>Isolado</dd></div>
                <div><dt>Armazenamento</dt><dd>SSD / NVMe*</dd></div>
                <div><dt>Acesso</dt><dd>Administrativo</dd></div>
                <div><dt>Gestão</dt><dd>Área do cliente</dd></div>
            </dl>
            <small>* Consulte a especificação publicada em cada plano.</small>
        </div>
    </div>
</section>

<section class="vps-plans" id="planos-vps" aria-labelledby="vps-plans-title">
    <div class="container">
        <div class="vps-section-head">
            <div><p class="eyebrow">Planos VPS</p><h2 id="vps-plans-title">Escolha os recursos do seu servidor</h2></div>
            <p>Todos os limites e valores disponíveis são exibidos antes da contratação.</p>
        </div>
        <div class="vps-plan-grid">
            @forelse ($produtos as $produto)
                <article class="vps-plan {{ $produto->featured ? 'is-featured' : '' }}">
                    @if($produto->featured)<span class="vps-plan-badge">Mais escolhido</span>@endif
                    <p class="vps-plan-type">Servidor virtual</p>
                    <h3>{{ $produto->nome }}</h3>
                    <p class="vps-plan-description">{{ $produto->descricao ?: 'Configuração VPS para workloads profissionais.' }}</p>
                    <div class="vps-price"><strong>R$ {{ number_format($produto->preco, 2, ',', '.') }}</strong><span>/mês</span></div>
                    <ul>
                        <li><span>Memória</span><strong>{{ $produto->ram ?: 'Consulte' }}</strong></li>
                        <li><span>Armazenamento</span><strong>{{ $produto->armazenamento ?: 'Consulte' }}</strong></li>
                        <li><span>Processamento</span><strong>{{ $produto->processamento ?: 'Consulte' }}</strong></li>
                        <li><span>Proteção DDoS</span><strong>{{ $produto->ddos_protection ? 'Incluída' : 'Conforme plano' }}</strong></li>
                    </ul>
                    <form method="POST" action="{{ route('cart.addProduct', $produto->id) }}">@csrf<button class="btn btn-primary" type="submit">Contratar VPS</button></form>
                </article>
            @empty
                <div class="vps-empty">
                    <p class="eyebrow">Atendimento comercial</p>
                    <h3>Configurações sob consulta</h3>
                    <p>Os planos públicos ainda não foram cadastrados. Fale com nossa equipe para avaliar a configuração adequada ao seu projeto.</p>
                    <a class="btn btn-primary" href="{{ auth()->check() ? route('ticket.index') : route('login') }}">Solicitar configuração</a>
                </div>
            @endforelse
        </div>
    </div>
</section>

<section class="vps-use-cases"><div class="container">
    <article><span>01</span><h3>Aplicações e APIs</h3><p>Hospede serviços web, workers e integrações com liberdade de configuração.</p></article>
    <article><span>02</span><h3>Múltiplos sites</h3><p>Centralize projetos em um ambiente com recursos definidos para sua operação.</p></article>
    <article><span>03</span><h3>Bancos de dados</h3><p>Crie ambientes privados e ajuste o software conforme a necessidade do projeto.</p></article>
</div></section>
@endsection
