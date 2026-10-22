@props(['product', 'type' => 'web'])
<article class="vtx-plan-card {{ $product->featured ? 'is-featured' : '' }}">
    @if($product->featured)<span class="vtx-plan-ribbon">Em destaque</span>@endif
    <p class="vtx-plan-kicker">{{ $type === 'vps' ? 'Servidor VPS' : 'Hospedagem de sites' }}</p>
    <h3>{{ $product->nome }}</h3>
    <p class="vtx-plan-copy">{{ $product->descricao ?: ($type === 'vps' ? 'Servidor virtual para projetos profissionais.' : 'Hospedagem confiável para seu projeto online.') }}</p>
    <div class="vtx-plan-price"><span>R$</span><strong>{{ number_format($product->preco, 2, ',', '.') }}</strong><small>/{{ $type === 'vps' ? 'mês' : 'ano' }}</small></div>
    <ul class="vtx-plan-specs">
        <li><span>Memória</span><code>{{ $product->ram ?: 'Consulte' }}</code></li>
        <li><span>Armazenamento</span><code>{{ $product->armazenamento ?: 'Consulte' }}</code></li>
        @if($type === 'web')
            <li><span>Bancos de dados</span><code>{{ $product->bancos_dados ?: 'Consulte' }}</code></li>
            <li><span>Domínios</span><code>{{ $product->dominios ?: 'Consulte' }}</code></li>
        @else
            <li><span>Processamento</span><code>{{ $product->processamento ?: 'Consulte' }}</code></li>
            <li><span>Proteção DDoS</span><code>{{ $product->ddos_protection ? 'Incluída' : 'Conforme plano' }}</code></li>
        @endif
    </ul>
    <form method="POST" action="{{ route('cart.addProduct', $product->id) }}">@csrf<button class="btn btn-primary" type="submit">Contratar</button></form>
</article>
