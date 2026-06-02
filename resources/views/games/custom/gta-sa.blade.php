<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $jogo->nome }} - VortexHost</title>

    <link rel="icon" type="image/png" href="{{ asset('img/logo_vtxhost.png') }}">
    <link rel="stylesheet" href="{{ asset('css/gtasa.css') }}?v={{ filemtime(public_path('css/gtasa.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/game-standard.css') }}?v={{ filemtime(public_path('css/game-standard.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/suporte.css') }}?v={{ filemtime(public_path('css/suporte.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}?v={{ filemtime(public_path('css/header.css')) }}">
</head>
<body>

<section class="sa-section">
    <section class="sa-hero" style="--game-hero-bg: url('{{ $jogo->heroImageUrl() }}'); --game-font-family: {{ $jogo->fontFamilyCss() }};">
        @include('partials.header')

        <div class="sa-hero-info">
            <h1 class="sa-title">{{ $jogo->nome }}</h1>
            <p class="sa-sub">
                Servidor no estilo San Andreas: rua, calor, gangue, rádio chiando e performance sem cair no tiroteio.
            </p>
        </div>
    </section>

    <div class="container">
        <div class="sa-plans-grid">
            @forelse ($plans as $plan)
                <article class="sa-card">
                    <div class="sa-card-header">
                        <div class="sa-icon-box">
                            @if ($jogo->iconUrl())
                                <img src="{{ $jogo->iconUrl() }}" alt="{{ $jogo->nome }}" class="sa-icon-img">
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
                        <li><span>Preço</span><strong>{{ $plan->precoFormatada() }} /mês</strong></li>
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

@include('partials.footer')
@include('partials.support-widget')
</body>
</html>
