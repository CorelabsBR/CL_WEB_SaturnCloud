@extends('layouts.app')
@section('content')


<section class="hosting-section">
    <div class="container">

        <div class="hosting-header">
            <h1 class="section-title">Hospedagem Web</h1>
            <p class="section-sub">
                Infraestrutura otimizada para sites, WordPress e sistemas PHP.
            </p>
        </div>

        <div class="plans-grid">

            @forelse ($produtos as $produto)
                <div class="plan-card {{ $produto->featured ? 'plan-highlighted' : '' }}">

                    <div class="plan-card-header">
                        @if($produto->imagem)
                            <img class="plan-icon plan-image" src="{{ asset($produto->imagem) }}" alt="{{ $produto->nome }}">
                        @else
                            <div class="plan-icon">🌐</div>
                        @endif
                        <span class="plan-label">
                            {{ $produto->featured ? 'Mais vendido' : 'Hospedagem Web' }}
                        </span>
                    </div>

                    <h3 class="plan-name">{{ $produto->nome }}</h3>

                    <p class="plan-description">
                        {{ $produto->descricao ?? 'Hospedagem rápida, segura e pronta para produção.' }}
                    </p>

                    <ul class="plan-features">
                        <li><strong>RAM</strong> {{ $produto->ram ?? '-' }}</li>
                        <li><strong>Armazenamento</strong> {{ $produto->armazenamento ?? '-' }}</li>
                        <li><strong>Processamento</strong> {{ $produto->processamento ?? 'ilimitado' }}</li>
                        <li><strong>Bancos de dados</strong> {{ $produto->bancos_dados ?? '-' }}</li>
                        <li><strong>Domínios</strong> {{ $produto->dominios ?? '-' }}</li>
                    </ul>

                    <hr class="plan-divider">

                    <div class="plan-footer">
                        <div class="plan-pricing">
                            <span class="plan-pricing-label">a partir de</span>
                            <div class="plan-pricing-value">
                                <span class="amount">R$ {{ number_format($produto->preco, 2, ',', '.') }}</span>
                                <span class="period">/mês</span>
                            </div>
                        </div>
<form method="POST" action="{{ route('cart.addProduct', $produto->id) }}" class="plan-checkout-form">
    @csrf

    <input type="hidden" name="items[0][plan_id]" value="{{ $produto->id }}">
    <input type="hidden" name="items[0][quantity]" value="1">

    <button type="submit" class="btn btn-primary">
        Contratar agora
    </button>
</form>
                    </div>

                </div>
            @empty

                <div class="plans-empty">
                    <h3>Nenhum plano disponível</h3>
                    <p>Cadastre produtos no banco para exibi-los aqui.</p>
                </div>

            @endforelse

        </div>

    </div>
</section>


@endsection
