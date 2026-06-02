<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $jogo->nome }} - VortexHost</title>

    <link rel="icon" type="image/png" href="{{ asset('img/logo_vtxhost.png') }}">
    <link rel="stylesheet" href="{{ asset('css/minecraft_bed.css') }}?v={{ filemtime(public_path('css/minecraft_bed.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/game-standard.css') }}?v={{ filemtime(public_path('css/game-standard.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/suporte.css') }}?v={{ filemtime(public_path('css/suporte.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}?v={{ filemtime(public_path('css/header.css')) }}">
</head>

<body>

<main class="minecraftjava-section">
    
    <section class="minecraftjava-header" style="--game-hero-bg: url('{{ $jogo->heroImageUrl() }}'); --game-font-family: {{ $jogo->fontFamilyCss() }};">
        @include('partials.header')
        <div class="minecraftjava-header-info">
            <h1 class="minecraftjava-title">
                {{ $jogo->nome }}
            </h1>

            <p class="minecraftjava-sub">
                Escolha seu plano, equipe sua base e coloque seu servidor no ar.
                Performance bruta para vanilla, plugins, mods e survival raiz.
            </p>
        </div>
    </section>

    <div class="container">
        <section class="minecraftjava-plans-grid">
            @forelse ($plans as $plan)
                @php
                    $planId = $plan->plan ?? $plan->id ?? null;
                @endphp

                <article class="minecraftjava-card">

                    <div class="minecraftjava-icon-fallback">
                        @if ($jogo->iconUrl())
                            <img
                                src="{{ $jogo->iconUrl() }}"
                                alt="{{ $jogo->nome }}"
                                class="minecraftjava-icon-img"
                            >
                        @else
                            ⛏️
                        @endif
                    </div>

                    <p class="minecraftjava-plan-tag">Plano de mineração</p>

                    <h3 class="minecraftjava-plan-name">
                        {{ $plan->name }}
                    </h3>

                    <ul class="minecraftjava-plan-specs">
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
                            <span class="spec-value" style="color: var(--mc-green);">
                                 {{ $plan->precoFormatada() }} <small>/mês</small>
                            </span>
                        </li>
                    </ul>

                    <hr class="minecraftjava-divider"> 

                    <div class="minecraftjava-card-footer">
                        @if ($planId)
                            <form method="POST" action="{{ route('cart.addPlan', $planId) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    Craftar servidor →
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
                <div class="minecraftjava-empty">
                    <p class="minecraftjava-empty-icon">🧱</p>
                    <h3>Sem planos no inventário</h3>
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
