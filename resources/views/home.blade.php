@extends('layouts.app')
@section('title', 'Saturn Cloud | Hospedagem e servidores para o seu próximo projeto')
@section('meta_description', 'Hospedagem de sites e servidores VPS com contratação em reais, pagamento via Pix e gestão pela área do cliente.')
@section('body_class', 'saturn-home')

@section('content')
<section class="cosmic-hero" aria-labelledby="home-title">
    <div class="container cosmic-heading">
        <p class="scene-caption"><span></span> Saturn Cloud · Infraestrutura digital</p>
        <h1 id="home-title"><span>INFRAESTRUTURA</span><span class="sem-limites">SEM LIMITES.</span></h1>
    </div>
    @include('partials.saturn-scene', ['sceneClass' => 'saturn-home-scene', 'priority' => true])
    <div class="container cosmic-bottom">
        <div class="cosmic-intro"><p>Potência, estabilidade e liberdade para construir o que vem depois.</p><div class="cosmic-actions"><a class="btn btn-primary btn-lg" href="{{ route('vps.index') }}">Explorar servidores <span aria-hidden="true">↗</span></a><a class="quiet-link" href="{{ route('pages.show', 'sobre') }}">Conhecer a Saturn</a></div></div>
        <a class="scroll-cue" href="#solucoes"><span aria-hidden="true">↓</span> Existe um universo de possibilidades.</a>
    </div>
</section>

<section class="home-signal" aria-label="Recursos disponíveis conforme o plano"><div class="container"><span>Hospedagem web</span><i></i><span>Servidores VPS</span><i></i><span>Painel do cliente</span><i></i><span>Suporte com histórico</span></div></section>

<section class="home-section home-solutions" id="solucoes"><div class="container">
    <header class="home-section-intro"><div><p class="home-kicker">Escolha seu caminho</p><h2>Infraestrutura no tamanho do seu projeto.</h2></div><p>Comece com a praticidade da hospedagem compartilhada ou assuma o controle de um servidor virtual.</p></header>
    <div class="solution-list">
        <a class="solution-row" href="{{ route('web') }}"><span class="solution-number">01</span><span class="solution-icon" aria-hidden="true"><svg viewBox="0 0 32 32"><rect x="4" y="6" width="24" height="20" rx="2"/><path d="M4 11h24M9 8.5h.01M13 8.5h.01M9 17h6M9 21h10"/></svg></span><span class="solution-copy"><strong>Hospedagem de sites</strong><small>Para sites, lojas e projetos que precisam de uma operação mais simples.</small></span><span class="solution-tags"><i>Planos anuais</i><i>Painel de gestão</i></span><span class="solution-arrow" aria-hidden="true">↗</span></a>
        <a class="solution-row" href="{{ route('vps.index') }}"><span class="solution-number">02</span><span class="solution-icon" aria-hidden="true"><svg viewBox="0 0 32 32"><rect x="5" y="4" width="22" height="8" rx="2"/><rect x="5" y="20" width="22" height="8" rx="2"/><path d="M9 8h.01M13 8h7M9 24h.01M13 24h7M16 12v8"/></svg></span><span class="solution-copy"><strong>Servidores VPS</strong><small>Ambientes virtuais para aplicações, automações e operações com mais autonomia.</small></span><span class="solution-tags"><i>Recursos dedicados*</i><i>Configuração por plano</i></span><span class="solution-arrow" aria-hidden="true">↗</span></a>
    </div><p class="home-footnote">* Confira a especificação de cada oferta antes da contratação.</p>
</div></section>

<section class="home-section home-plans" id="planos"><div class="container">
    <header class="home-section-intro plans-intro"><div><p class="home-kicker">Planos em destaque</p><h2>Escolha com clareza.<br>Evolua quando precisar.</h2></div><div class="plan-tabs" role="group" aria-label="Atalhos de planos"><a href="#vps-plans">VPS</a><a href="#web-plans">Hospedagem web</a></div></header>
    <div class="plan-group" id="vps-plans"><div class="plan-group-head"><h3>Servidores VPS</h3><a href="{{ route('vps.index') }}">Ver todos <span aria-hidden="true">→</span></a></div><div class="vtx-plan-grid">@forelse($vpsProducts as $product)<x-plan-card :product="$product" type="vps" />@empty<article class="vtx-plan-empty"><p class="home-kicker">Sob medida</p><h3>Configuração sob consulta</h3><p>Converse com a equipe para dimensionar um ambiente sem assumir recursos ou valores que ainda não foram publicados.</p><a class="btn btn-primary" href="{{ auth()->check() ? route('ticket.index') : route('login') }}">Falar com a equipe</a></article>@endforelse</div></div>
    <div class="plan-group" id="web-plans"><div class="plan-group-head"><h3>Hospedagem de sites</h3><a href="{{ route('web') }}">Ver todos <span aria-hidden="true">→</span></a></div><div class="vtx-plan-grid">@forelse($webProducts as $product)<x-plan-card :product="$product" type="web" />@empty<article class="vtx-plan-empty"><p class="home-kicker">Em breve</p><h3>Novos planos a caminho</h3><p>Nenhum plano de hospedagem está disponível para contratação neste momento.</p></article>@endforelse</div></div>
    <p class="home-plan-note">Preços, recursos e disponibilidade são exibidos diretamente a partir dos produtos cadastrados. Consulte também a <a href="{{ route('reembolso') }}">Política de Reembolso</a>.</p>
</div></section>

<section class="home-operation"><div class="container home-operation-layout">
    <div class="operation-copy"><p class="home-kicker">Uma operação mais simples</p><h2>Do pedido ao acompanhamento, sem caminhos obscuros.</h2><p>O fluxo comercial e a gestão dos serviços convivem na mesma conta. Você acompanha cada etapa sem depender de conversas espalhadas.</p><a class="home-text-link" href="{{ auth()->check() ? route('dashboard.index') : route('registro') }}">{{ auth()->check() ? 'Acessar meu painel' : 'Criar minha conta' }} <span aria-hidden="true">→</span></a></div>
    <ol class="operation-flow"><li><span>01</span><div><strong>Compare</strong><p>Veja preço e recursos publicados em cada plano.</p></div></li><li><span>02</span><div><strong>Contrate</strong><p>Finalize o pedido pelo checkout da plataforma.</p></div></li><li><span>03</span><div><strong>Acompanhe</strong><p>Consulte pedidos, serviços e suporte na área do cliente.</p></div></li></ol>
</div></section>

<section class="orbit-story" aria-labelledby="orbit-title"><div class="container orbit-story-layout"><p class="scene-caption">A base para o seu próximo passo</p><div><h2 id="orbit-title" data-reveal>Ideias grandes.<br>Uma base à altura.</h2><p>Da primeira publicação à próxima aplicação. Hospedagem, servidores e atendimento conectados para você seguir construindo.</p><a class="quiet-link" href="{{ route('pages.show', 'infraestrutura') }}">Infraestrutura sem caixa-preta <span aria-hidden="true">↗</span></a></div></div><div class="orbital-traces" aria-hidden="true"><i></i><i></i><i></i></div></section>

@php($faqs = [
    ['Qual é a diferença entre hospedagem web e VPS?', 'A hospedagem web oferece um ambiente mais simples para publicar sites. A VPS dá mais autonomia sobre o servidor e é indicada para aplicações e configurações específicas.'],
    ['Como sei quais recursos estão incluídos?', 'Os recursos válidos são os apresentados no plano no momento da contratação. Eles vêm diretamente dos produtos cadastrados na plataforma.'],
    ['Posso pagar via Pix?', 'Sim. O checkout integrado ao Mercado Pago disponibiliza Pix conforme a resposta do provedor.'],
    ['Onde acompanho meu serviço?', 'Pedidos, serviços e chamados de suporte ficam reunidos na área do cliente.'],
    ['Existe política de reembolso?', 'Sim. Os prazos, critérios e condições estão detalhados na página de Política de Reembolso.'],
])
<section class="home-section home-faq"><div class="container faq-layout"><header><p class="home-kicker">Dúvidas frequentes</p><h2>Antes de colocar seu projeto em órbita.</h2><p>Não encontrou o que procura? Consulte a <a href="{{ route('help.index') }}">Central de Ajuda</a>.</p></header><div class="faq-list">@foreach($faqs as [$question, $answer])<details><summary>{{ $question }}<span aria-hidden="true"></span></summary><p>{{ $answer }}</p></details>@endforeach</div></div></section>

<section class="home-final"><div class="container home-final-inner"><div><p class="home-kicker">Seu próximo passo</p><h2>Pronto para tirar seu projeto do papel?</h2></div><div><p>Compare os planos disponíveis e escolha a infraestrutura adequada para agora.</p><a class="btn btn-primary btn-lg" href="#planos">Ver planos <span aria-hidden="true">↗</span></a></div></div></section>
@endsection
