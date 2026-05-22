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

                    <div class="profile-dropdown">
                        <button class="profile-btn" id="profile-btn" aria-label="Menu do perfil">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z" />
                            </svg>
                        </button>
                        <div class="profile-menu" id="profile-menu">
                            <a href="{{ url('/painel') }}" class="profile-menu-item">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M3 13h2v8H3zm4-8h2v16H7zm4-2h2v18h-2zm4-2h2v20h-2zm4 4h2v16h-2z" />
                                </svg>
                                Painel
                            </a>
                            @if(auth()->user()?->tipo_usuario === 'root')
                                <a href="{{ route('admin.index') }}" class="profile-menu-item">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M12 2l8 4v6c0 5-3.4 9.4-8 10-4.6-.6-8-5-8-10V6l8-4zm0 4.2L7 8.7V12c0 3.2 2 6.1 5 6.8 3-.7 5-3.6 5-6.8V8.7l-5-2.5z" />
                                    </svg>
                                    Admin
                                </a>
                            @endif
                            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="profile-menu-item logout-btn">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z" />
                                    </svg>
                                    Deslogar
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a class="btn btn-ghost btn-sm" href="{{ url('/login') }}">
                        Entrar
                    </a>

                    <a class="btn btn-primary btn-sm" href="{{ url('/registro') }}">
                        Criar conta
                    </a>
                @endauth
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

                @if(auth()->user()?->tipo_usuario === 'root')
                    <a
                        href="{{ route('admin.index') }}"
                        class="{{ navActive(['admin', 'admin/*']) }}"
                    >
                        Admin
                    </a>
                @endif

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="mobile-logout-btn">
                        Deslogar
                    </button>
                </form>

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
            @else
                <a href="{{ url('/login') }}">
                    Entrar
                </a>

                <a href="{{ url('/registro') }}">
                    Criar conta
                </a>
            @endauth
        </nav>
    </div>
</header>

<script src="{{ asset('js/header.js') }}"></script>
