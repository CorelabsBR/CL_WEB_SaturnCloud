<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $jogo->nome }} - VortexHost</title>

    <link rel="icon" type="image/png" href="{{ asset('img/logo_vtxhost.png') }}">
    <link rel="stylesheet" href="{{ asset('css/gtav.css') }}?v={{ filemtime(public_path('css/gtav.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/game-standard.css') }}?v={{ filemtime(public_path('css/game-standard.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/suporte.css') }}?v={{ filemtime(public_path('css/suporte.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}?v={{ filemtime(public_path('css/header.css')) }}">
</head>
<body>

<section class="gta5-section">
    <section class="gta5-hero" style="--game-hero-bg: url('{{ $jogo->heroImageUrl() }}'); --game-font-family: {{ $jogo->fontFamilyCss() }};">
        @include('partials.header')

        <div class="gta5-hero-info">
            <h1 class="gta5-title">{{ $jogo->nome }}</h1>
            <p class="gta5-sub">
                Hospedagem no clima de Los Santos: rápida, estável e pronta pra caos pesado de servidor.
            </p>
        </div>
    </section>

    <div class="container">
        <div class="gta5-plans-grid">
            @forelse ($plans as $plan)
                <article class="gta5-card">
                    <div class="gta5-card-top">
                        <div class="gta5-icon-box">
                            @if ($jogo->iconUrl())
                                <img src="{{ $jogo->iconUrl() }}" alt="{{ $jogo->nome }}" class="gta5-icon-img">
                            @else
                                <span>V</span>
                            @endif
                        </div>
                        <span class="gta5-wanted">{{ trim(str_repeat('★ ', $loop->iteration)) }}</span>
                    </div>

                    <p class="gta5-plan-tag">Plano</p>
                    <h3 class="gta5-plan-name">{{ $plan->name }}</h3>

                    <ul class="gta5-plan-specs">
                        <li><span>RAM</span><strong>{{ $plan->ramFormatada() }}</strong></li>
                        <li><span>Disco</span><strong>{{ $plan->discoFormatado() }}</strong></li>
                        <li><span>CPU</span><strong>{{ $plan->cpuFormatada() }}</strong></li>
                        <li><span>Preço</span><strong>{{ $plan->precoFormatada() }} /mês</strong></li>
                    </ul>

                    <div class="gta5-card-footer">
                        <form method="POST" action="{{ route('cart.addPlan', $plan->plan) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-md">
                                Selecionar plano →
                            </button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="gta5-empty">
                    <span>V</span>
                    <h3>Sem planos ainda</h3>
                    <p>Configura os planos no banco, camarada.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

@include('partials.footer')
@include('partials.support-widget')
</body>
</html>
