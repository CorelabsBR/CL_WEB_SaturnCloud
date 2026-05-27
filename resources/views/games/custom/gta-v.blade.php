<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GTA V - VortexHost</title>

    <link rel="icon" type="image/png" href="{{ asset('img/logo_vtxhost.png') }}">
    <link rel="stylesheet" href="{{ asset('css/gtav.css') }}?v={{ filemtime(public_path('css/gtav.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}?v={{ filemtime(public_path('css/header.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/suporte.css') }}?v={{ filemtime(public_path('css/suporte.css')) }}">
</head>
<body>

@include('partials.header')

<section class="gta5-section">
    <div class="gta5-skyline"></div>
    <div class="gta5-noise"></div>

    <div class="container">
        <div class="gta5-hero">
            <div class="gta5-badge">Premium Game Hosting</div>
            <h1 class="gta5-title">{{ $jogo->nome }}</h1>
            <p class="gta5-sub">
                Hospedagem no clima de Los Santos: rápida, estável e pronta pra caos pesado de servidor.
            </p>
            <div class="gta5-meta">
                <span>Fivem</span>
                <span>SSD NVMe</span>
                <span>Proteção DDoS</span>
                <span>Deploy instantâneo</span>
            </div>
        </div>

        <div class="gta5-plans-grid">
            @forelse ($plans as $plan)
                <article class="gta5-card">
                    <div class="gta5-card-top">
                        <div class="gta5-icon-box">
                            @if ($jogo->icon)
                                <img src="{{ asset('img/games/icons/' . $jogo->icon) }}" alt="{{ $jogo->nome }}" class="gta5-icon-img">
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
<script src="{{ asset('js/header.js') }}"></script>
</body>
</html>
