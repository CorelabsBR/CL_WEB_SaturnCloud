<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GTA San Andreas - VortexHost</title>

    <link rel="icon" type="image/png" href="{{ asset('img/logo_vtxhost.png') }}">
    <link rel="stylesheet" href="{{ asset('css/gtasa.css') }}?v={{ filemtime(public_path('css/gtasa.css')) }}">
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
                <img src="{{ asset('img/logo_vtxhost.png') }}" alt="logo_vtxhost" id="logo_img">
                <span class="brand-mark">VORTEX<span>HOST</span></span>
            </a>

            <ul class="nav-links">
                <li><a href="{{ url('/') }}">Início</a></li>
                <li><a href="{{ url('/games') }}">Hospedagem de Jogos</a></li>
                <li><a href="{{ url('/vps') }}">VPS</a></li>
                <li><a href="{{ url('/web') }}">Hospedagem Web</a></li>
            </ul>

            <div class="nav-actions">
                <span class="nav-area-note">Grove Street Ready</span>
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
            <a href="{{ url('/') }}">Início</a>
            <a href="{{ url('/games') }}">Hospedagem de Jogos</a>
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

<script src="{{ asset('js/header.js') }}"></script>

<section class="sa-section">
    <div class="sa-poster-layer"></div>
    <div class="sa-scanlines"></div>

    <div class="container">
        <div class="sa-hero">
            <span class="sa-eyebrow">VortexHost Roleplay Division</span>
            <h1 class="sa-title">{{ $jogo->nome }}</h1>
            <p class="sa-sub">
                Servidor no estilo San Andreas: rua, calor, gangue, rádio chiando e performance sem cair no tiroteio.
            </p>
            <div class="sa-tags">
                <span>Grove Street</span>
                <span>RP / Freeroam</span>
                <span>Baixa latência</span>
            </div>
        </div>

        <div class="sa-plans-grid">
            @forelse ($plans as $plan)
                <article class="sa-card">
                    <div class="sa-card-header">
                        <div class="sa-icon-box">
                            @if ($jogo->icon)
                                <img src="{{ asset('img/games/icons/' . $jogo->icon) }}" alt="{{ $jogo->nome }}" class="sa-icon-img">
                            @else
                                <span>SA</span>
                            @endif
                        </div>
                        <span class="sa-stamp">RESPECT +</span>
                    </div>

                    <p class="sa-plan-tag">Plano</p>
                    <h3 class="sa-plan-name">{{ $plan->name }}</h3>

                    <ul class="sa-plan-specs">
                        <li><span>RAM</span><strong>{{ $plan->ramFormatada() }}</strong></li>
                        <li><span>Disco</span><strong>{{ $plan->discoFormatado() }}</strong></li>
                        <li><span>CPU</span><strong>{{ $plan->cpuFormatada() }}</strong></li>
                    </ul>

                    <div class="sa-card-footer">
                        <form method="POST" action="{{ route('cart.addPlan', $plan->plan) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-md">
                                Bora fechar →
                            </button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="sa-empty">
                    <span>SA</span>
                    <h3>Sem planos ainda</h3>
                    <p>Configura os planos no banco, camarada.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="brand">
                    <img src="{{ asset('img/logo_vtxhost.png') }}" alt="logo_vtxhost" id="logo_img">
                    <span class="brand-mark">VORTEX<span>HOST</span></span>
                </div>
                <p>Infraestrutura bruta para servidores de jogos, VPS e hospedagem web.</p>
                <a href="mailto:contato@vortexhost.com.br">contato@vortexhost.com.br</a>
                <span class="footer-status">SISTEMAS ONLINE</span>
            </div>

            <div class="footer-col"><h4>Serviços</h4><ul class="footer-links"><li><a href="{{ url('/web') }}">HospedaWeb</a></li><li><a href="{{ url('/vps') }}">VPS Premium</a></li><li><a href="{{ url('/games') }}">Servidores de Jogos</a></li></ul></div>
            <div class="footer-col"><h4>Suporte</h4><ul class="footer-links"><li><a href="{{ url('/cliente') }}">Área do Cliente</a></li><li><a href="{{ url('/login') }}">Login</a></li><li><a href="{{ url('/registro') }}">Criar Conta</a></li><li><a href="mailto:contato@vortexhost.com.br">Abrir Ticket</a></li></ul></div>
            <div class="footer-col"><h4>Legal</h4><ul class="footer-links"><li><a href="#">Termos de Serviço</a></li><li><a href="#">Política de Privacidade</a></li><li><a href="#">Política de Reembolso</a></li></ul></div>
            <div class="footer-col"><h4>Pagamentos</h4><div class="footer-payments"><span>PIX</span><span>Cartão</span><span>Boleto</span><span>PayPal</span></div></div>
        </div>
        <div class="footer-bottom"><span>VortexHost © {{ date('Y') }}</span><span>San Andreas Hosting Department</span></div>
    </div>
</footer>
@include('partials.support-widget')
</body>
</html>
