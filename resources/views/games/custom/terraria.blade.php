<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $jogo->nome }} - VortexHost</title>

    <link rel="icon" type="image/png" href="{{ asset('img/logo_vtxhost.png') }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/suporte.css') }}?v={{ filemtime(public_path('css/suporte.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/terraria.css') }}?v={{ filemtime(public_path('css/terraria.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/game-standard.css') }}?v={{ filemtime(public_path('css/game-standard.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}?v={{ filemtime(public_path('css/header.css')) }}">
</head>

<body>

<main class="terraria-section">
    <section class="terraria-header" style="--game-hero-bg: url('{{ $jogo->heroImageUrl() }}'); --game-font-family: {{ $jogo->fontFamilyCss() }};">
        @include('partials.header')

        <div class="terraria-header-info">
            <h1 class="terraria-title">
                {{ $jogo->nome }}
            </h1>

            <p class="terraria-sub">
                Escolha seu plano, monte sua vila e abra seu mundo Terraria sem lag.
                Performance para survival, bosses, mods, eventos e caos noturno.
            </p>
        </div>
    </section>

    <div class="container">

        <section class="terraria-plans-grid">
            @forelse ($plans as $plan)
                @php
                    $planId = $plan->plan ?? $plan->id ?? null;
                @endphp

                <article class="terraria-card">
                    <div class="terraria-icon-fallback">
                        @if ($jogo->iconUrl())
                            <img
                                src="{{ $jogo->iconUrl() }}"
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

                        <li>
                            <span class="spec-label">Preço</span>
                            <span class="spec-value">{{ $plan->precoFormatada() }} <small>/mês</small></span>
                        </li>
                    </ul>

                    <hr class="terraria-divider">

                    <div class="terraria-card-footer">
                        @if ($planId)
                            <form method="POST" action="{{ route('cart.addPlan', $planId) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    Invocar servidor
                                </button>
                            </form>
                        @else
                            <button type="button" class="btn btn-primary" disabled>
                                Plano indisponível
                            </button>
                        @endif
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

@include('partials.footer')

@include('partials.support-widget')
</body>
</html>
