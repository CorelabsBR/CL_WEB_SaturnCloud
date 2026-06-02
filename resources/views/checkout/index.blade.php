@extends('layouts.app')

@section('title', 'Checkout - VortexHost')

@section('content')
<section class="checkout-page">
    <div class="checkout-container">
        <div class="checkout-hero">
            <div>
                <h1>Checkout</h1>
                <p>Revise seu pedido e finalize o pagamento para ativarmos seus serviços.</p>
            </div>

            <a href="{{ route('cart.index') }}" class="btn btn-ghost btn-sm">
                Voltar ao carrinho
            </a>
        </div>

        @if(session('error'))
            <div class="checkout-alert" role="alert">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="checkout-alert checkout-alert-success" role="status">{{ session('success') }}</div>
        @endif

        <div class="checkout-layout">
            <div class="checkout-box">
                <div class="checkout-box-head">
                    <h2>Itens do pedido</h2>
                    <span>{{ $items->count() }} {{ $items->count() === 1 ? 'item' : 'itens' }}</span>
                </div>

                <div class="checkout-items">
                    @foreach($items as $item)
                        @php
                            $category = Illuminate\Support\Str::lower((string) $item->product?->categoria?->nome);
                            $isWebProduct = str_contains($category, 'web') || str_contains($category, 'hosped') || (int) $item->product?->categoria_id === 1;
                        @endphp
                        <article class="checkout-item">
                            <div class="checkout-item-main">
                                <strong>{{ $item->product->nome }}</strong>
                                <span>{{ $item->quantidade }}x R$ {{ number_format($item->product->preco, 2, ',', '.') }}</span>

                                @if($isWebProduct)
                                    <label class="checkout-username">
                                        <span>Usuário do painel</span>
                                        <input
                                            form="checkout-payment-form"
                                            type="text"
                                            name="requested_usernames[{{ $item->id }}]"
                                            value="{{ old('requested_usernames.' . $item->id) }}"
                                            placeholder="ex: minhaempresa"
                                            autocomplete="off"
                                            inputmode="latin"
                                            pattern="[a-z][a-z0-9]{2,31}"
                                            maxlength="32"
                                            required
                                        >
                                    </label>
                                    @error('requested_usernames.' . $item->id)
                                        <small class="checkout-field-error">{{ $message }}</small>
                                    @enderror
                                @endif
                            </div>

                            <div class="checkout-subtotal">
                                <small>Subtotal</small>
                                <strong>R$ {{ number_format($item->product->preco * $item->quantidade, 2, ',', '.') }}</strong>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

            <aside class="checkout-summary">
                <div class="summary-card">
                    <h2>Resumo</h2>

                    <div class="summary-row">
                        <span>Subtotal</span>
                        <strong>R$ {{ number_format($subtotal, 2, ',', '.') }}</strong>
                    </div>

                    <div class="checkout-coupon">
                        <span>Cupom</span>

                        @if($coupon)
                            <div class="checkout-coupon-applied">
                                <strong>{{ $coupon->code }}</strong>
                                <form method="POST" action="{{ route('checkout.coupon.remove') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">Remover</button>
                                </form>
                            </div>
                        @else
                            <form method="POST" action="{{ route('checkout.coupon.apply') }}">
                                @csrf
                                <input type="text" name="coupon_code" placeholder="Código do cupom" autocomplete="off">
                                <button type="submit">Aplicar</button>
                            </form>
                        @endif

                        @error('coupon_code')
                            <small class="checkout-field-error">{{ $message }}</small>
                        @enderror
                    </div>

                    @if($discount > 0)
                        <div class="summary-row summary-discount">
                            <span>Desconto</span>
                            <strong>- R$ {{ number_format($discount, 2, ',', '.') }}</strong>
                        </div>
                    @endif

                    <div class="summary-row">
                        <span>Forma de pagamento</span>
                        <strong>{{ $adminExempt ? 'Cortesia admin' : ($finalTotal <= 0 ? 'Cupom' : 'Mercado Pago') }}</strong>
                    </div>

                    <div class="summary-total">
                        <span>Total</span>
                        <strong>R$ {{ number_format($finalTotal, 2, ',', '.') }}</strong>
                    </div>

                    <form id="checkout-payment-form" method="POST" action="{{ route('checkout.pay') }}">
                        @csrf

                        <button type="submit" class="btn btn-primary btn-full">
                            {{ $adminExempt ? 'Finalizar cortesia administrativa' : ($finalTotal <= 0 ? 'Finalizar pedido' : 'Pagar com Mercado Pago') }}
                        </button>
                    </form>

                    <p>
                        {{ $adminExempt
                            ? 'Seu usuário root aprova este pedido sem cobrança.'
                            : ($finalTotal <= 0 ? 'Seu pedido será aprovado usando o cupom aplicado.' : 'Você será redirecionado para concluir o pagamento com segurança.')
                        }}
                    </p>
                </div>
            </aside>
        </div>
    </div>
</section>
@endsection
