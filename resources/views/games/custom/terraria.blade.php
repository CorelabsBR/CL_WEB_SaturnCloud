<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $jogo->nome }} - VortexHost</title>

    <link rel="icon" type="image/png" href="{{ asset('img/logo_vtxhost.png') }}">
    <link rel="stylesheet" href="{{ asset('css/terraria.css') }}?v={{ filemtime(public_path('css/terraria.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/suporte.css') }}?v={{ filemtime(public_path('css/suporte.css')) }}">
</head>

<body>
@php
    $cartCount = auth()->check()
        ? \App\Models\CartItem::where('user_id', auth()->id())->sum('quantidade')
        : 0;
@endphp

<header class="site-header">
    <div class="container">
        <nav class="nav-inner">
            <a class="brand" href="{{ url('/') }}">
                <img src="{{ asset('img/logo_vtxhost.png') }}" alt="VortexHost" id="logo_img">
            </a>

            <ul class="nav-links">
                <li><a href="{{ url('/') }}">Floresta</a></li>
                <li><a href="{{ url('/games') }}">Servidores</a></li>
                <li><a href="{{ url('/vps') }}">VPS</a></li>
                <li><a href="{{ url('/web') }}">Hospedagem Web</a></li>
            </ul>

            <div class="nav-actions">
                <span class="nav-area-note">Área do aventureiro</span>
                <a class="btn btn-ghost btn-sm" href="{{ url('/login') }}">Entrar</a>
                <a class="btn btn-primary btn-sm" href="{{ url('/registro') }}">Criar conta</a>
                <a class="nav-cart" href="{{ url('/carrinho') }}" aria-label="Ir para o carrinho{{ $cartCount > 0 ? ' com ' . $cartCount . ' ' . ($cartCount === 1 ? 'item' : 'itens') : '' }}" title="Carrinho">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6.2 6h15l-1.7 8.2a2 2 0 0 1-2 1.6H9a2 2 0 0 1-2-1.6L5.2 3.8H2.8" />
                        <circle cx="9.5" cy="20" r="1.4" />
                        <circle cx="17.5" cy="20" r="1.4" />
                    </svg>
                    <span class="nav-cart-label">Carrinho</span>
                    @if($cartCount > 0)
                        <span class="nav-cart-count" aria-hidden="true">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
                    @endif
                </a>
            </div>

            <button class="hamburger" id="hamburger" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
        </nav>

        <nav class="mobile-nav" id="mobile-nav">
            <a href="{{ url('/') }}">Floresta</a>
            <a href="{{ url('/games') }}">Servidores</a>
            <a href="{{ url('/vps') }}">VPS</a>
            <a href="{{ url('/web') }}">Hospedagem Web</a>
            <a href="{{ url('/login') }}">Entrar</a>
            <a href="{{ url('/registro') }}">Criar conta</a>
            <a href="{{ url('/carrinho') }}" class="mobile-cart">
                Carrinho
                @if($cartCount > 0)
                    <span class="mobile-cart-count">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
                @endif
            </a>
        </nav>
    </div>
</header>

<main class="terraria-section">
    <div class="container">

        <section class="terraria-header">
            <div class="terraria-header-info">
                <span class="terraria-eyebrow">Bioma dedicado</span>

                <h1 class="terraria-title">
                    {{ $jogo->nome }}
                </h1>

                <p class="terraria-sub">
                    Escolha seu plano, monte sua vila e abra seu mundo Terraria sem lag.
                    Performance para survival, bosses, mods, eventos e caos noturno.
                </p>
            </div>
        </section>

        <section class="terraria-plans-grid">
            @forelse ($plans as $plan)
                <article class="terraria-card">
                    <div class="terraria-icon-fallback">
                        @if ($jogo->icon)
                            <img
                                src="{{ asset('img/games/icons/' . $jogo->icon) }}"
                                alt="{{ $jogo->nome }}"
                                class="terraria-icon-img"
                            >
                        @else
                            🌳
                        @endif
                    </div>

                    <p class="terraria-plan-tag">Plano de aventura</p>

                    <h3 class="terraria-plan-name">
                        {{ $plan->name }}
                    </h3>

                    <ul class="terraria-plan-specs">
                        <li>
                            <span class="spec-label">RAM</span>
                            <span class="spec-value">{{ $plan->ramFormatada() }}</span>
                        </li>

                        <li>
                            <span class="spec-label">Disco</span>
                            <span class="spec-value">{{ $plan->discoFormatado() }}</span>
                        </li>

                        <li>
                            <span class="spec-label">CPU</span>
                            <span class="spec-value">{{ $plan->cpuFormatada() }}</span>
                        </li>
                    </ul>

                    <hr class="terraria-divider">

                    <div class="terraria-card-footer">
                        <form method="POST" action="{{ route('cart.addPlan', $plan->plan) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm">
                                Invocar servidor →
                            </button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="terraria-empty">
                    <p class="terraria-empty-icon">🧱</p>
                    <h3>Sem planos no baú</h3>
                    <p>Configura os planos no banco, camarada.</p>
                </div>
            @endforelse
        </section>

    </div>
</main>

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">

            <div class="footer-brand">
                <div class="brand">
                    <img src="{{ asset('img/logo_vtxhost.png') }}" alt="VortexHost" id="logo_img">
                </div>

                <p>
                    Infraestrutura para servidores de jogos com desempenho,
                    estabilidade e suporte que não some igual Blood Moon.
                </p>

                <a href="mailto:contato@vortexhost.com.br">
                    contato@vortexhost.com.br
                </a>

                <span class="footer-status">
                    Mundo online
                </span>
            </div>

            <div class="footer-col">
                <h4>Serviços</h4>
                <ul class="footer-links">
                    <li><a href="{{ url('/games') }}">Servidores de Jogos</a></li>
                    <li><a href="{{ url('/vps') }}">VPS Premium</a></li>
                    <li><a href="{{ url('/web') }}">Hospedagem Web</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Suporte</h4>
                <ul class="footer-links">
                    <li><a href="{{ url('/cliente') }}">Área do Cliente</a></li>
                    <li><a href="{{ url('/login') }}">Login</a></li>
                    <li><a href="{{ url('/registro') }}">Criar Conta</a></li>
                    <li><a href="mailto:contato@vortexhost.com.br">Abrir Ticket</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Legal</h4>
                <ul class="footer-links">
                    <li><a href="#">Termos de Serviço</a></li>
                    <li><a href="#">Política de Privacidade</a></li>
                    <li><a href="#">Política de Reembolso</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Taverna</h4>

                <div class="social-icons">
                    <a href="https://instagram.com/Colocar_a_merda_do_link_aqui_gordao" target="_blank" rel="noopener noreferrer" class="social-icon" title="Instagram">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="2" width="20" height="20" rx="5"></rect>
                            <circle cx="12" cy="12" r="4"></circle>
                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                        </svg>
                    </a>

                    <a href="https://discord.gg/Colocar_a_merda_do_link_aqui_gordao" target="_blank" rel="noopener noreferrer" class="social-icon" title="Discord">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20.317 4.37A19.79 19.79 0 0 0 15.432 2.85a.07.07 0 0 0-.079.037c-.211.375-.445.865-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.618-1.25.077.077 0 0 0-.078-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.028C.533 9.046-.319 13.58.099 18.058a.082.082 0 0 0 .031.056 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.873-1.295 1.226-1.994a.076.076 0 0 0-.042-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128c.126-.094.252-.192.372-.291a.074.074 0 0 1 .078-.01c3.928 1.793 8.18 1.793 12.061 0a.074.074 0 0 1 .079.01c.12.099.246.198.373.292a.077.077 0 0 1-.007.128c-.598.343-1.22.644-1.873.891a.077.077 0 0 0-.041.107c.36.698.772 1.363 1.225 1.993a.076.076 0 0 0 .084.029 19.963 19.963 0 0 0 6.002-3.03.077.077 0 0 0 .031-.055c.5-5.177-.838-9.674-3.548-13.66a.061.061 0 0 0-.031-.03zM8.02 15.331c-1.183 0-2.157-1.086-2.157-2.419s.956-2.419 2.157-2.419c1.211 0 2.176 1.095 2.157 2.419 0 1.333-.956 2.419-2.157 2.419zm7.975 0c-1.183 0-2.157-1.086-2.157-2.419s.955-2.419 2.157-2.419c1.211 0 2.176 1.095 2.157 2.419 0 1.333-.946 2.419-2.157 2.419z"/>
                        </svg>
                    </a>
                </div>
            </div>

        </div>

        <div class="footer-bottom">
            <span class="footer-copy">
                VortexHost © {{ date('Y') }} — boss por boss.
            </span>

            <div class="footer-payments">
                <span class="payment-badge">PIX</span>
                <span class="payment-badge">Cartão</span>
                <span class="payment-badge">Boleto</span>
                <span class="payment-badge">PayPal</span>
            </div>
        </div>
    </div>
</footer>

<script src="{{ asset('js/header.js') }}"></script>
@include('partials.support-widget')
</body>
</html>
