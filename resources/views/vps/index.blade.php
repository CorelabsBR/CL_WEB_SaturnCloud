@extends('layouts.app')
@section('title', 'Servidores VPS | Saturn Cloud')
@section('meta_description', 'Compare os servidores VPS disponíveis na Saturn Cloud e escolha uma configuração para aplicações, sites e serviços digitais.')
@section('body_class', 'saturn-vps')

@section('content')
<section class="vps-hero" aria-labelledby="vps-title">

    <div class="container vps-hero-layout">
        <div class="vps-hero-copy">
            <p class="vps-kicker"><span></span> Servidores virtuais</p>
            <h1 id="vps-title">POTÊNCIA.<br>AUTONOMIA.<br>SEU VPS.</h1>
            <p>Seu ambiente para aplicações, automações e novos projetos. Escolha uma configuração e assuma o próximo passo.</p>
            <div class="vps-actions"><a class="btn btn-primary btn-lg" href="#planos-vps">Comparar planos <span aria-hidden="true">↓</span></a><a class="vps-inline-link" href="#como-funciona">Entender o produto <span aria-hidden="true">→</span></a></div>
            <ul class="vps-quick"><li>Recursos descritos por plano</li><li>Pagamento em reais</li><li>Gestão pela área do cliente</li></ul>
        </div>
        <div class="vps-art" aria-hidden="true"><div class="compute-stack"><div><span>Compute</span><i></i></div><div><span>Memory</span><i></i></div><div><span>Storage</span><i></i></div><div><span>Saturn Cloud</span><i></i></div></div><span class="compute-caption">Seu espaço para construir.</span></div>
    </div>
</section>

<section class="vps-principles" aria-label="Características da contratação"><div class="container"><div><span>01</span><strong>Escolha objetiva</strong><small>Compare o que está cadastrado</small></div><div><span>02</span><strong>Contratação integrada</strong><small>Pedido no fluxo existente</small></div><div><span>03</span><strong>Gestão central</strong><small>Serviços na sua conta</small></div></div></section>

<section class="vps-plans" id="planos-vps" aria-labelledby="plans-title"><div class="container">
    <header class="vps-section-head"><div><p class="vps-kicker">Configurações disponíveis</p><h2 id="plans-title">Recursos que você<br>pode comparar.</h2></div><p>Valores e especificações abaixo vêm diretamente dos produtos cadastrados na plataforma.</p></header>
    <div class="vps-plan-grid">
        @forelse ($produtos as $produto)
            <article class="vps-plan {{ $produto->featured ? 'is-featured' : '' }}">
                <div class="vps-plan-top">@if($produto->featured)<span class="vps-plan-badge">Em destaque</span>@else<span class="vps-plan-index">VPS / {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>@endif<h3>{{ $produto->nome }}</h3><p>{{ $produto->descricao ?: 'Servidor virtual para workloads e projetos digitais.' }}</p></div>
                <div class="vps-price"><span>R$</span><strong>{{ number_format($produto->preco, 2, ',', '.') }}</strong><small>/mês</small></div>
                <dl class="vps-specs">
                    <div><dt>Memória RAM</dt><dd>{{ $produto->ram ?: 'Consulte' }}</dd></div>
                    <div><dt>Armazenamento</dt><dd>{{ $produto->armazenamento ?: 'Consulte' }}</dd></div>
                    <div><dt>Processamento</dt><dd>{{ $produto->processamento ?: 'Consulte' }}</dd></div>
                    <div><dt>Proteção DDoS</dt><dd>{{ $produto->ddos_protection ? 'Incluída' : 'Conforme plano' }}</dd></div>
                </dl>
                <form method="POST" action="{{ route('cart.addProduct', $produto->id) }}">@csrf<button class="btn btn-primary" type="submit">Escolher {{ $produto->nome }} <span aria-hidden="true">→</span></button></form>
            </article>
        @empty
            <div class="vps-empty"><div class="empty-orbit" aria-hidden="true"><span></span></div><p class="vps-kicker">Atendimento comercial</p><h3>Planos públicos em preparação.</h3><p>Ainda não há configurações VPS cadastradas para contratação direta. Fale com a equipe para avaliar a necessidade do seu projeto sem assumir recursos ou preços inexistentes.</p><a class="btn btn-primary" href="{{ route('pages.show', 'contato') }}">Falar com a equipe <span aria-hidden="true">→</span></a></div>
        @endforelse
    </div>
    @if($produtos->isNotEmpty())
    <div class="vps-compare" aria-labelledby="compare-title"><div class="compare-title"><p class="vps-kicker">Visão comparativa</p><h3 id="compare-title">Todos os planos,<br>lado a lado.</h3></div><div class="compare-scroll"><table><thead><tr><th>Plano</th><th>RAM</th><th>Armazenamento</th><th>Processamento</th><th>Preço mensal</th></tr></thead><tbody>@foreach($produtos as $produto)<tr><th>{{ $produto->nome }}</th><td>{{ $produto->ram ?: 'Consulte' }}</td><td>{{ $produto->armazenamento ?: 'Consulte' }}</td><td>{{ $produto->processamento ?: 'Consulte' }}</td><td>R$ {{ number_format($produto->preco, 2, ',', '.') }}</td></tr>@endforeach</tbody></table></div></div>
    @endif
    <p class="vps-data-note">Tráfego, localização, sistema operacional e demais condições devem ser confirmados na oferta ou com a equipe quando não estiverem publicados.</p>
</div></section>

<section class="vps-explainer" id="como-funciona"><div class="container explainer-layout">
    <div class="explainer-copy"><p class="vps-kicker">Entenda a escolha</p><h2>Quando um VPS faz sentido?</h2><p>Quando seu projeto pede um ambiente virtual próprio, mais autonomia de configuração ou recursos definidos em um único servidor.</p><a class="vps-inline-link" href="{{ route('help.index') }}">Consultar a Central de Ajuda <span aria-hidden="true">→</span></a></div>
    <div class="use-case-list"><article><span>01</span><div><h3>Aplicações e APIs</h3><p>Execute serviços web e integrações em um ambiente dedicado ao seu projeto.</p></div></article><article><span>02</span><div><h3>Automações</h3><p>Mantenha processos e ferramentas que precisam rodar continuamente.</p></div></article><article><span>03</span><div><h3>Múltiplos projetos</h3><p>Organize diferentes aplicações dentro da configuração contratada.</p></div></article></div>
</div></section>

<section class="vps-journey"><div class="container"><header><p class="vps-kicker">Da escolha à gestão</p><h2>Um fluxo direto,<br>sem atalhos escondidos.</h2></header><ol><li><span>01</span><strong>Compare</strong><p>Analise os recursos disponíveis em cada plano.</p></li><li><span>02</span><strong>Adicione</strong><p>Envie a configuração escolhida para o carrinho.</p></li><li><span>03</span><strong>Finalize</strong><p>Conclua pelo checkout conectado à sua conta.</p></li><li><span>04</span><strong>Acompanhe</strong><p>Consulte o serviço pela área do cliente.</p></li></ol></div></section>

@php($vpsFaq = [
    ['O que é um servidor VPS?', 'É um ambiente virtual com uma configuração própria de recursos. Ele é indicado para projetos que precisam de mais autonomia do que uma hospedagem compartilhada.'],
    ['Quais recursos acompanham cada plano?', 'RAM, armazenamento, processamento e preço são apresentados nos planos cadastrados. Outras condições devem ser confirmadas quando não estiverem publicadas.'],
    ['A proteção DDoS está incluída?', 'A página informa “Incluída” somente quando esse recurso está marcado no produto. Nos demais casos, confirme a condição antes da compra.'],
    ['Como faço a contratação?', 'Escolha um plano, adicione-o ao carrinho e siga o fluxo de checkout da sua conta.'],
    ['Onde acompanho o servidor?', 'O pedido e os recursos de gestão disponibilizados ficam associados à área do cliente.'],
])
<section class="vps-faq"><div class="container faq-layout"><header><p class="vps-kicker">Perguntas frequentes</p><h2>O essencial antes de escolher.</h2></header><div class="faq-list">@foreach($vpsFaq as [$question,$answer])<details><summary>{{ $question }}<span></span></summary><p>{{ $answer }}</p></details>@endforeach</div></div></section>

<section class="vps-final"><div class="container"><div><p class="vps-kicker">Próximo passo</p><h2>Encontre a configuração para o seu projeto.</h2></div><a class="btn btn-primary btn-lg" href="#planos-vps">Comparar planos <span aria-hidden="true">↑</span></a></div></section>
@endsection
