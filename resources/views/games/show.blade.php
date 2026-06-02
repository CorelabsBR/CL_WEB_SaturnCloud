@extends('layouts.showgame')

@section('hide_header', true)

@section('content')

<section class="game-section">

    <div class="container">

        {{-- HEADER --}}
        <div class="game-header" style="--game-hero-bg: url('{{ $jogo->heroImageUrl() }}'); --game-font-family: {{ $jogo->fontFamilyCss() }};">
            @include('partials.header')

            <div class="game-header-info">
                <h1 class="game-title">
                    {{ $jogo->nome }}
                </h1>

                <p class="game-sub">
                    Escolha um plano para seu servidor de {{ $jogo->nome }}.
                    Performance estável e direta, sem frescura.
                </p>
            </div>
        </div>

        {{-- GRID --}}
        <section class="game-plans-grid">
            @forelse ($plans as $plan)
                <div class="game-card">

                    <div class="game-card-icon">
                        @if ($jogo->iconUrl())
                            <img
                                src="{{ $jogo->iconUrl() }}"
                                alt="{{ $jogo->nome }}"
                                class="game-icon-img"
                            >
                        @else
                            <div class="game-icon-fallback">🎮</div>
                        @endif
                    </div>

                    <p class="game-plan-tag">
                        Plano 
                    </p>

                    <h3 class="game-plan-name" title="{{ $plan->name }}">
                        {{ $plan->name }}
                    </h3>

                    <ul class="game-plan-specs">
                        <li>
                            <span class="game-spec-label">RAM</span>
                            <span class="game-spec-value">{{ $plan->ramFormatada() }}</span>
                        </li>
                        <li>
                            <span class="game-spec-label">Disco</span>
                            <span class="game-spec-value">{{ $plan->discoFormatado() }}</span>
                        </li>
                        <li>
                            <span class="game-spec-label">CPU</span>
                            <span class="game-spec-value">{{ $plan->cpuFormatada() }}</span>
                        </li>
                        <li>
                            <span class="game-spec-label">Preço</span>
                            <span class="game-spec-value">
                                {{ $plan->precoFormatada() }} /mês
                            </span>
                        </li>
                    </ul>

                    <hr class="game-divider">

                    <div class="game-card-footer">
                        <form method="POST" action="{{ route('cart.addPlan', $plan->plan) }}">
                            @csrf

                            <button type="submit" class="btn btn-primary btn-sm">
                                Contratar →
                            </button>
                        </form>
                    </div>

                </div>

            @empty
                <div class="game-empty">
                    <p class="game-empty-icon">🎮</p>
                    <h3>Sem planos ainda</h3>
                    <p>Configura os planos no banco, camarada.</p>
                </div>
            @endforelse
        </section>

    </div>

</section>

@endsection
