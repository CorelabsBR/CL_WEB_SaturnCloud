<header class="site-header">
    <div class="container">
        <nav class="nav-inner">

            <a class="brand" href="{{ url('/') }}">
                <img src="{{ asset('img/logo_vtxhost.png') }}" alt="logo_vtxhost" id="logo_img">
            </a>

            @php
                $current = request()->path();
                $cartCount = auth()->check()
                    ? \App\Models\CartItem::where('user_id', auth()->id())->sum('quantidade')
                    : 0;

                function navActive($paths = []) {
                    foreach ($paths as $path) {
                        if (request()->is($path)) {
                            return 'active';
                        }
                    }

                    return '';
                }
            @endphp

            <ul class="nav-links">
                <li>
                    <a
                        href="{{ url('/') }}"
                        class="{{ request()->is('/') ? 'active' : '' }}"
                    >
                        Início
                    </a>
                </li>

                <li>
                    <a
                        href="{{ url('/games') }}"
                        class="{{ navActive(['games', 'games/*']) }}"
                    >
                        Hospedagem de Jogos
                    </a>
                </li>

                <li>
                    <a
                        href="{{ url('/vps') }}"
                        class="{{ navActive(['vps', 'vps/*']) }}"
                    >
                        VPS
                    </a>
                </li>

                <li>
                    <a
                        href="{{ url('/web') }}"
                        class="{{ navActive(['web', 'web/*']) }}"
                    >
                        Hospedagem Web
                    </a>
                </li>
            </ul>

            <div class="nav-actions">
                @auth
                    <a class="btn btn-primary btn-sm" href="{{ url('/painel') }}">
                        Painel
                    </a>
                @else
                    <a class="btn btn-ghost btn-sm" href="{{ url('/login') }}">
                        Entrar
                    </a>

                    <a class="btn btn-primary btn-sm" href="{{ url('/registro') }}">
                        Criar conta
                    </a>
                @endauth

                <a
                    class="nav-cart {{ navActive(['carrinho', 'carrinho/*']) }}"
                    href="{{ url('/carrinho') }}"
                    aria-label="Ir para o carrinho{{ $cartCount > 0 ? ' com ' . $cartCount . ' ' . ($cartCount === 1 ? 'item' : 'itens') : '' }}"
                    title="Carrinho"
                >
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6.2 6h15l-1.7 8.2a2 2 0 0 1-2 1.6H9a2 2 0 0 1-2-1.6L5.2 3.8H2.8" />
                        <circle cx="9.5" cy="20" r="1.4" />
                        <circle cx="17.5" cy="20" r="1.4" />
                    </svg>
                    <span class="nav-cart-label">Carrinho</span>
                    @if($cartCount > 0)
                        <span class="nav-cart-count" aria-hidden="true">
                            {{ $cartCount > 99 ? '99+' : $cartCount }}
                        </span>
                    @endif
                </a>
            </div>

            <button class="hamburger" id="hamburger" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>

        </nav>

        <nav class="mobile-nav" id="mobile-nav">
            <a
                href="{{ url('/') }}"
                class="{{ request()->is('/') ? 'active' : '/' }}"
            >
                Início
            </a>

            <a
                href="{{ url('/games') }}"
                class="{{ navActive(['games', 'games/*']) }}"
            >
                Hospedagem de Jogos
            </a>

            <a
                href="{{ url('/vps') }}"
                class="{{ navActive(['vps', 'vps/*']) }}"
            >
                VPS
            </a>

            <a
                href="{{ url('/web') }}"
                class="{{ navActive(['web', 'web/*']) }}"
            >
                Hospedagem Web
            </a>

            @auth
                <a
                    href="{{ url('/painel') }}"
                    class="{{ navActive(['painel']) }}"
                >
                    Painel
                </a>
            @else
                <a href="{{ url('/login') }}">
                    Entrar
                </a>

                <a href="{{ url('/registro') }}">
                    Criar conta
                </a>
            @endauth

            <a
                href="{{ url('/carrinho') }}"
                class="mobile-cart {{ navActive(['carrinho', 'carrinho/*']) }}"
            >
                Carrinho
                @if($cartCount > 0)
                    <span class="mobile-cart-count">
                        {{ $cartCount > 99 ? '99+' : $cartCount }}
                    </span>
                @endif
            </a>
        </nav>
    </div>
</header>

<script src="{{ asset('js/header.js') }}"></script>
