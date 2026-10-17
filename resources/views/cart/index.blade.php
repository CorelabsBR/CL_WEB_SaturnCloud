@extends('layouts.cart')

@section('title', 'Carrinho')

@section('content')
<section class="cart-page container py-5">

    <div class="cart-header">
        <div>
            <h1 class="cart-title">Carrinho</h1>
            <p class="cart-subtitle">Revise seus serviços antes de finalizar a compra.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="flash flash-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="flash flash-error">{{ session('error') }}</div>
    @endif

    @if($items->isEmpty())
        <div class="cart-empty">
            <h2>Seu carrinho está vazio.</h2>
            <p>Escolha um plano para continuar.</p>

            <a href="{{ route('web') }}" class="btn btn-primary">
                Ver planos
            </a>
        </div>
    @else
        <div class="cart-layout">

            <div class="cart-list">
                @foreach($items as $item)
                    <article class="cart-item">
                        <div class="cart-item-main">
                            <div class="cart-product-icon">
                                VH
                            </div>

                            <div class="cart-product-info">
                                <h3>{{ $item->product->nome }}</h3>

                                <p class="cart-product-price">
                                    R$ {{ number_format($item->product->preco, 2, ',', '.') }}
                                </p>
                            </div>
                        </div>

                        <div class="cart-item-actions">
                            <form method="POST" action="{{ route('cart.update', $item->id) }}" class="cart-qty-form">
                                @csrf
                                @method('PATCH')

                                <label for="quantidade-{{ $item->id }}">Qtd.</label>

                                <input
                                    id="quantidade-{{ $item->id }}"
                                    type="number"
                                    name="quantidade"
                                    value="{{ $item->quantidade }}"
                                    min="1"
                                    max="99"
                                >

                                <button type="submit" class="btn btn-sm btn-outline">
                                    Atualizar
                                </button>
                            </form>

                            <form method="POST" action="{{ route('cart.remove', $item->id) }}">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn btn-sm btn-danger">
                                    Remover
                                </button>
                            </form>
                        </div>

                        <div class="cart-item-footer">
                            <span>Subtotal</span>

                            <strong>
                                R$ {{ number_format($item->product->preco * $item->quantidade, 2, ',', '.') }}
                            </strong>
                        </div>
                    </article>
                @endforeach

                <div class="cart-list-footer">
                    <a href="{{ route('web') }}" class="btn btn-ghost cart-continue-btn">
                        Voltar a comprar
                    </a>
                </div>
            </div>

            <aside class="cart-summary">
                <h2>Resumo</h2>

                <div class="summary-row">
                    <span>Itens</span>
                    <strong>{{ $items->count() }}</strong>
                </div>

                <div class="summary-row total">
                    <span>{{ $adminExempt ? 'Total administrativo' : 'Total' }}</span>
                    <strong>R$ {{ number_format($total, 2, ',', '.') }}</strong>
                </div>

                @if($adminExempt)
                    <div class="summary-row">
                        <span>Valor original</span>
                        <strong>R$ {{ number_format($subtotal, 2, ',', '.') }}</strong>
                    </div>
                    <p class="cart-admin-note">Root/admin não paga para finalizar compras.</p>
                @endif

                <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-full">
                    {{ $adminExempt ? 'Finalizar como admin' : 'Finalizar compra' }}
                </a>

                <form method="POST" action="{{ route('cart.clear') }}">
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="btn btn-danger btn-full">
                        Limpar carrinho
                    </button>
                </form>
            </aside>

        </div>
    @endif
</section>
@endsection
