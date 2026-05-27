<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GTA San Andreas - VortexHost</title>

    <link rel="icon" type="image/png" href="{{ asset('img/logo_vtxhost.png') }}">
    <link rel="stylesheet" href="{{ asset('css/gtasa.css') }}?v={{ filemtime(public_path('css/gtasa.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}?v={{ filemtime(public_path('css/header.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/suporte.css') }}?v={{ filemtime(public_path('css/suporte.css')) }}">
</head>
<body>
@include('partials.header')

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
<script src="{{ asset('js/header.js') }}"></script>
</body>
</html>
