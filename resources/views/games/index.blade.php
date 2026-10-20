@extends('layouts.app')

@section('title', 'Servidores de Jogos - Saturn Cloud')
@section('content')

<section class="games-page">
    <div class="container">

        <div class="games-picker-head">
            <div>
                <h1 class="section-title">Escolha o jogo do seu servidor</h1>
                <p class="section-sub">
                    Selecione uma base pronta, veja os planos disponíveis e coloque o servidor no ar com a configuração certa para cada jogo.
                </p>
            </div>
        </div>

        <div class="games-picker-grid">

            @forelse ($jogos as $jogo)
                @php
                    $cardImageUrl = method_exists($jogo, 'cardImageUrl')
                        ? $jogo->cardImageUrl()
                        : ($jogo->banner ? asset('img/games/' . $jogo->banner) : null);
                    $isVisibleForUsers = (bool) data_get($jogo, 'ativo', true);
                @endphp
                <a
                    href="{{ route('games.show', ['jogo' => $jogo->slug]) }}"
                    class="game-choice-card game-choice-{{ $jogo->slug }} {{ $loop->first ? 'is-featured' : '' }}"
                    aria-label="Ver planos para {{ $jogo->nome }}"
                >
                    <div class="game-choice-media">
                        @if ($cardImageUrl)
                            <img
                                class="game-choice-image"
                                src="{{ $cardImageUrl }}"
                                alt="{{ $jogo->nome }}"
                            >
                        @else
                            <div class="game-choice-fallback">
                                <span>{{ mb_substr($jogo->nome, 0, 1) }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="game-choice-content">
                        <div class="game-choice-topline">
                            <span class="game-choice-status {{ $isVisibleForUsers ? '' : 'is-maintenance' }}">
                                {{ $isVisibleForUsers ? 'Pronto para ativar' : 'Em manutenção' }}
                            </span>
                            @if ($loop->first)
                                <span class="game-choice-featured">Destaque</span>
                            @endif
                        </div>

                        <h2>{{ $jogo->nome }}</h2>

                        <p>
                            Planos otimizados com painel, proteção e recursos ajustados para partidas estáveis.
                        </p>

                        <div class="game-choice-footer">
                            <span>Ver planos</span>
                            <span class="game-choice-arrow" aria-hidden="true">→</span>
                        </div>
                    </div>
                </a>
            @empty

                <div class="games-empty-state">
                    <h3>Nenhum jogo encontrado</h3>
                    <p>Popula a tabela jogos_prod.</p>
                </div>

            @endforelse

        </div>

    </div>
</section>
@endsection
