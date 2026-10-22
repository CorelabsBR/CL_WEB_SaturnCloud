@extends('layouts.app')
@section('title', 'Planos de hospedagem web | Saturn Cloud')
@section('meta_description', 'Compare planos de hospedagem web da Saturn Cloud, incluindo memória, armazenamento, bancos de dados, domínios e preço anual.')
@section('content')


<section class="hosting-section">
    <div class="container">

        <div class="hosting-header">
            <p class="eyebrow">Hospedagem profissional</p>
            <h1 class="section-title">Seu site rápido, seguro e sempre sob controle.</h1>
            <p class="section-sub">Planos para sites institucionais, WordPress, lojas e aplicações PHP, com painel de gerenciamento e suporte centralizado.</p>
        </div>

        <div class="hosting-assurances" aria-label="Benefícios da hospedagem">
            <span>SSL conforme o plano</span><span>Painel de gerenciamento</span><span>Suporte por chamados</span><span>Contratação online</span>
        </div>

        <div class="plans-grid">

            @forelse ($produtos as $produto)
                <div class="plan-card {{ $produto->featured ? 'plan-highlighted' : '' }}">

                    <div class="plan-card-header">
                        @if($produto->imagem)
                            <img class="plan-icon plan-image" src="{{ asset($produto->imagem) }}" alt="{{ $produto->nome }}">
                        @else
                            <div class="plan-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18"/></svg>
                            </div>
                        @endif
                        <span class="plan-label">
                            {{ $produto->featured ? 'Destaque' : 'Hospedagem Web' }}
                        </span>
                    </div>

                    <h3 class="plan-name">{{ $produto->nome }}</h3>

                    <p class="plan-description">
                        {{ $produto->descricao ?? 'Descrição comercial ainda não informada.' }}
                    </p>

                    <ul class="plan-features">
                        <li><strong>RAM</strong> {{ $produto->ram ?? '-' }}</li>
                        <li><strong>Armazenamento</strong> {{ $produto->armazenamento ?? '-' }}</li>
                        <li><strong>Processamento</strong> {{ $produto->processamento ?? 'Não informado' }}</li>
                        <li><strong>Bancos de dados</strong> {{ $produto->bancos_dados ?? '-' }}</li>
                        <li><strong>Domínios</strong> {{ $produto->dominios ?? '-' }}</li>
                    </ul>

                    <hr class="plan-divider">

                    <div class="plan-footer">
                        <div class="plan-pricing">
                            <span class="plan-pricing-label">investimento anual</span>
                            <div class="plan-pricing-value">
                                <span class="amount">R$ {{ number_format($produto->preco, 2, ',', '.') }}</span>
                                <span class="period">/ano</span>
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
                    <p>Fale com a equipe para encontrar uma solução para o seu site.</p>
                </div>

            @endforelse

        </div>

    </div>
</section>

<section class="hosting-platform">
    <div class="container hosting-platform-grid">
        <div><p class="eyebrow">Operação simplificada</p><h2>Menos tempo administrando hospedagem. Mais tempo no seu negócio.</h2></div>
        <div class="hosting-platform-list">
            <article><strong>01</strong><span><b>Gerenciamento central</b>Domínios, serviços e pedidos acessíveis pela sua conta.</span></article>
            <article><strong>02</strong><span><b>Escala sem confusão</b>Compare recursos e mude de solução conforme o projeto evolui.</span></article>
            <article><strong>03</strong><span><b>Suporte com histórico</b>Atendimento organizado por chamados dentro da plataforma.</span></article>
        </div>
    </div>
</section>


@endsection
