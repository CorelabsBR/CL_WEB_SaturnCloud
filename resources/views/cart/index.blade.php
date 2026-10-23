@extends('layouts.app')
@section('title', 'Carrinho | Saturn Cloud')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/cart.css') }}">
@endpush
@section('content')
<section class="site-cart container" aria-labelledby="cart-title">
    <h1 id="cart-title">Seu carrinho</h1>
    <p>Escolha seus planos aqui. A contratação e o pagamento serão feitos no portal do cliente.</p>
    @if($errors->any())<p role="alert">Informe uma quantidade entre 1 e 99.</p>@endif
    @forelse($items as $item)
        <article class="site-cart__item">
            <div><h2>{{ $item->product->nome }}</h2><p>R$ {{ number_format($item->product->preco, 2, ',', '.') }} / {{ $item->product->type === 'web' ? 'ano' : 'mês' }}</p></div>
            <form method="POST" action="{{ route('cart.update', $item->product->id) }}">
                @csrf @method('PATCH')
                <label for="quantity-{{ $loop->index }}">Quantidade</label>
                <input id="quantity-{{ $loop->index }}" name="quantidade" type="number" min="1" max="99" value="{{ $item->quantity }}" required>
                <button class="btn btn-ghost" type="submit">Atualizar</button>
            </form>
            <form method="POST" action="{{ route('cart.remove', $item->product->id) }}">
                @csrf @method('DELETE')
                <button class="btn btn-ghost" type="submit" aria-label="Remover {{ $item->product->nome }}">Remover</button>
            </form>
        </article>
    @empty
        <div class="site-cart__empty"><h2>Seu carrinho está vazio</h2><p>Explore os planos disponíveis para escolher os recursos do seu projeto.</p></div>
    @endforelse
    <div class="site-cart__actions">
        <a class="btn btn-ghost" href="{{ route('web') }}">Ver hospedagem</a>
        <a class="btn btn-ghost" href="{{ route('vps.index') }}">Ver servidores VPS</a>
        @if($items->isNotEmpty())
            @if($portalCartUrl)
                <form method="POST" action="{{ $portalCartUrl }}">
                    <input type="hidden" name="version" value="1">
                    @foreach($items->values() as $item)
                        <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->product->id }}">
                        <input type="hidden" name="items[{{ $loop->index }}][quantity]" value="{{ $item->quantity }}">
                    @endforeach
                    <button class="btn btn-primary" type="submit">Continuar no portal</button>
                </form>
            @else
                <p class="site-cart__notice" role="status">A finalização pelo portal estará disponível em breve. Nenhum pedido ou pagamento foi criado.</p>
            @endif
            <form method="POST" action="{{ route('cart.clear') }}">@csrf @method('DELETE')<button class="btn btn-ghost" type="submit">Limpar carrinho</button></form>
        @endif
    </div>
</section>
@endsection
