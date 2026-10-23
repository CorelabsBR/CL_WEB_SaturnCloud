
    <header class="site-header">

    <div class="container">
        <nav class="site-header__nav-inner">

            <a class="site-header__brand" href="{{ url('/') }}" aria-label="Saturn Cloud — página inicial">
                <img src="{{ asset('img/saturno_branco_quadrado.svg') }}?v={{ filemtime(public_path('img/saturno_branco_quadrado.svg')) }}" alt="" class="site-header__logo" width="70" height="70" decoding="async">
                    <span class="site-header__wordmark"><span class="brandhighlight">Saturn</span> <strong>Cloud</strong></span>
            </a>

            @php
                $current = request()->path();
                $cartCount = array_sum(session('cart', []));

                $navActive = static function (array $paths = []): string {
                    foreach ($paths as $path) {
                        if (request()->is($path)) {
                            return 'active';
                        }
                    }
                    return '';
                };
            @endphp

            <ul class="site-header__nav-links">
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
                        href="{{ url('/web') }}"
                        class="{{ $navActive(['web', 'web/*']) }}"
                    >
                        Hospedagem Web
                    </a>
                </li>
                <li><a href="{{ url('/vps') }}" class="{{ $navActive(['vps', 'vps/*']) }}">Servidores VPS</a></li>
                <li><a href="{{ route('pages.show', 'infraestrutura') }}" class="{{ $navActive(['infraestrutura']) }}">Infraestrutura</a></li>
                <li><a href="{{ route('pages.show', 'sobre') }}" class="{{ $navActive(['sobre']) }}">Sobre</a></li>
                <li>
                    <a href="{{ route('help.index') }}" class="{{ $navActive(['ajuda', 'ajuda/*']) }}">Suporte</a>
                </li>
            </ul>

            <div class="site-header__nav-actions">
                <a class="site-header__nav-cart" href="{{ route('cart.index') }}" aria-label="Carrinho com {{ $cartCount }} itens">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.2 6h15l-1.7 8.2a2 2 0 0 1-2 1.6H9a2 2 0 0 1-2-1.6L5.2 3.8H2.8"/><circle cx="9.5" cy="20" r="1.4"/><circle cx="17.5" cy="20" r="1.4"/></svg>
                    <span class="site-header__nav-cart-label">Carrinho</span>
                    @if($cartCount > 0)<span class="site-header__nav-cart-count">{{ $cartCount }}</span>@endif
                </a>
                @if(config('portal.url'))<a class="btn btn-ghost btn-sm" href="{{ config('portal.url') }}">Portal do cliente</a>@endif
            </div>

            <button class="site-header__hamburger" id="hamburger" type="button" aria-label="Abrir menu" aria-expanded="false" aria-controls="mobile-nav">
                <span></span><span></span><span></span>
            </button>

        </nav>

        <nav class="site-header__mobile-nav" id="mobile-nav" aria-label="Navegação mobile" inert>
            <a
                href="{{ url('/') }}"
                class="{{ request()->is('/') ? 'active' : '/' }}"
            >
                Início
            </a>
            <a
                href="{{ url('/vps') }}"
                class="{{ $navActive(['vps', 'vps/*']) }}"
            >
                VPS
            </a>

            <a
                href="{{ url('/web') }}"
                class="{{ $navActive(['web', 'web/*']) }}"
            >
                Hospedagem Web
            </a>
            <a href="{{ route('pages.show', 'infraestrutura') }}">Infraestrutura</a>
            <a href="{{ route('pages.show', 'sobre') }}">Sobre a Saturn</a>
            <a href="{{ route('help.index') }}" class="{{ $navActive(['ajuda', 'ajuda/*']) }}">Suporte</a>

            <a href="{{ route('cart.index') }}">Carrinho ({{ $cartCount }})</a>
            @if(config('portal.url'))<a href="{{ config('portal.url') }}">Portal do cliente</a>@endif
        </nav>
    </div>
</header>

<script src="{{ asset('js/header.js') }}?v={{ filemtime(public_path('js/header.js')) }}" defer></script>
