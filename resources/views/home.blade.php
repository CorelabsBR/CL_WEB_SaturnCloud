@extends('layouts.app')

@section('title', 'Vortex Host')

@section('content')

@stack('styles')

<!-- HERO -->
<section class="hero">
    <div class="container">
        <h1 class="hero-title">Hospedagem de<br>Outro Nível</h1>

        <p class="hero-subtitle">
            Jogos, sites e VPS com hardware de ponta, anti-DDoS incluso e presença
            no Brasil e Canadá.
        </p>

        <div class="hero-cta">
            <a class="btn btn-primary btn-lg" href="{{ route('games.index') }}">COMEÇAR AGORA</a>
            <a class="btn btn-ghost btn-lg" href="{{ route('vps.index') }}">Ver VPS →</a>
        </div>

        <div class="hero-badges" aria-label="Destaques da VortexHost">
            <span class="hero-badge"><span class="dot"></span> Anti-DDoS incluso</span>
            <span class="hero-badge"><span class="dot"></span> Suporte por ticket</span>
            <span class="hero-badge"><span class="dot"></span> Brasil e Canadá</span>
        </div>
    </div>
</section>

<!-- MÉTRICAS -->
<section class="home-stats-section" aria-label="Números da VortexHost">
    <div class="container">
        <div class="home-stats-grid">
            <div class="home-stat">
                <strong>99.9%</strong>
                <span>Uptime monitorado</span>
            </div>
            <div class="home-stat">
                <strong>NVMe</strong>
                <span>Armazenamento rápido</span>
            </div>
            <div class="home-stat">
                <strong>24/7</strong>
                <span>Atendimento via painel</span>
            </div>
            <div class="home-stat">
                <strong>DDoS</strong>
                <span>Proteção incluída</span>
            </div>
        </div>
    </div>
</section>

<!-- SERVIÇOS -->
<section class="services-section">
    <div class="container">

        <div class="section-head center">
            <h2 class="section-title">Escolha o serviço ideal</h2>
            <p class="section-sub">Infraestrutura de alta performance para cada necessidade.</p>
        </div>

        <div class="services-grid">

            <div class="svc-card">
                <div class="svc-icon">🌐</div>
                <p class="svc-tag">Hospedagem Web</p>
                <h3 class="svc-title">Sites & Aplicações</h3>
                <p class="svc-desc">Hospedagem otimizada para WordPress, lojas virtuais e sistemas web com uptime garantido.</p>
                <ul class="svc-feats">
                    <li>SSL gratuito incluído</li>
                    <li>Painel Hestia intuitivo</li>
                    <li>Suporte 24/7</li>
                </ul>
                <div class="svc-divider"></div>
                <div class="svc-footer">
                    <p class="svc-price">a partir de <span>R$32</span>/mês</p>
                    <a href="{{ route('web') }}" class="btn btn-ghost btn-sm">Ver Planos →</a>
                </div>
            </div>

            <div class="svc-card">
                <div class="svc-icon">⚡</div>
                <p class="svc-tag">VPS Premium</p>
                <h3 class="svc-title">Servidores Virtuais</h3>
                <p class="svc-desc">VPS com NVMe, anti-DDoS avançado e acesso root completo. Brasil e Canadá.</p>
                <ul class="svc-feats">
                    <li>NVMe de alta velocidade</li>
                    <li>Anti-DDoS incluso</li>
                    <li>Acesso root completo</li>
                </ul>
                <div class="svc-divider"></div>
                <div class="svc-footer">
                    <p class="svc-price">a partir de <span>R$50</span>/mês</p>
                    <a href="{{ route('vps.index') }}" class="btn btn-ghost btn-sm">Ver Planos →</a>
                </div>
            </div>

            <div class="svc-card">
                <div class="svc-icon">🎮</div>
                <p class="svc-tag">Servidores de Jogos</p>
                <h3 class="svc-title">Jogos Online</h3>
                <p class="svc-desc">Servidores prontos para Minecraft, GTA, Terraria e outros jogos com instalação simples.</p>
                <ul class="svc-feats">
                    <li>Painel para gerenciar o servidor</li>
                    <li>Planos para vários jogos</li>
                    <li>Upgrade quando precisar</li>
                </ul>
                <div class="svc-divider"></div>
                <div class="svc-footer">
                    <p class="svc-price">a partir de <span>R$9</span>/mês</p>
                    <a href="{{ route('games.index') }}" class="btn btn-ghost btn-sm">Ver Planos →</a>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- POR QUE ESCOLHER -->
<section class="home-features-section">
    <div class="container">
        <div class="home-split">
            <div>
                <div class="section-head">
                    <h2 class="section-title">Infraestrutura pensada para jogar e crescer</h2>
                    <p class="section-sub">Do primeiro servidor até projetos maiores, a VortexHost entrega uma base estável para manter tudo online.</p>
                </div>

                <div class="home-feature-list">
                    <article class="home-feature">
                        <span>01</span>
                        <div>
                            <h3>Ativação simples</h3>
                            <p>Escolha o plano, adicione ao carrinho e finalize o checkout pelo painel.</p>
                        </div>
                    </article>

                    <article class="home-feature">
                        <span>02</span>
                        <div>
                            <h3>Painel centralizado</h3>
                            <p>Acompanhe serviços, faturas, carrinho e dados da conta em um só lugar.</p>
                        </div>
                    </article>

                    <article class="home-feature">
                        <span>03</span>
                        <div>
                            <h3>Suporte no canto da tela</h3>
                            <p>Abra um ticket pelo chat flutuante sem sair da página em que você está.</p>
                        </div>
                    </article>
                </div>
            </div>

            <div class="home-highlight-card">
                <span>Pronto para começar</span>
                <h3>Monte seu servidor em poucos cliques</h3>
                <p>Compare planos de jogos, VPS e hospedagem web, coloque no carrinho e finalize a contratação com segurança.</p>
                <div class="home-highlight-actions">
                    <a class="btn btn-primary" href="{{ route('games.index') }}">Ver jogos</a>
                    @auth
                        <a class="btn btn-ghost" href="{{ route('cart.index') }}">Ir ao carrinho</a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA FINAL -->
<section class="home-final-cta">
    <div class="container">
        <div class="home-final-box">
            <div>
                <h2>Hospede seu projeto com a VortexHost</h2>
                <p>Escolha entre servidores de jogos, VPS premium ou hospedagem web e gerencie tudo no painel.</p>
            </div>
            <a class="btn btn-primary btn-lg" href="{{ route('games.index') }}">Conhecer planos</a>
        </div>
    </div>
</section>

@endsection
