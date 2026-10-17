@extends('layouts.app')
@section('title', 'Vortex | VPS Brasil e hospedagem de sites')
@section('meta_description', 'VPS Brasil e hospedagem de sites com SSD NVMe, pagamento via Pix e gestão pela área do cliente. Conheça os planos da Vortex.')
@section('content')
<section class="vtx-hero" aria-labelledby="home-title"><div class="container vtx-hero-grid">
    <div class="vtx-hero-copy">
        <p class="eyebrow">Infraestrutura CoreLabs no Brasil</p>
        <h1 id="home-title">Seu projeto online.<br><span>Sem gargalos.</span></h1>
        <p>Hospedagem de sites e servidores VPS para quem precisa publicar, escalar e manter a operação sob controle.</p>
        <div class="vtx-hero-actions"><a class="btn btn-primary btn-lg" href="{{ route('vps.index') }}">Ver planos de VPS</a><a class="btn btn-ghost btn-lg" href="{{ route('web') }}">Hospedar meu site</a></div>
        <ul class="vtx-trust-row"><li>SSD NVMe*</li><li>Suporte humano</li><li>Pix</li><li>SSL grátis*</li></ul>
        <small>* Recursos conforme especificação de cada plano.</small>
    </div>
    <aside class="vtx-terminal" aria-label="Demonstração decorativa de provisionamento">
        <div class="vtx-terminal-top"><i></i><i></i><i></i><span>deploy.vortex</span></div>
        <div class="vtx-terminal-body" aria-hidden="true"><p><b>$</b> vortex deploy --region br</p><p><em>→</em> reservando recursos...</p><p><em>→</em> aplicando rede privada...</p><p><em>→</em> preparando armazenamento...</p><p class="is-success">✓ ambiente pronto para uso</p><p><b>$</b> <span class="terminal-cursor"></span></p></div>
    </aside>
</div></section>

<section class="vtx-benefit-bar" aria-label="Benefícios"><div class="container"><span>NVMe conforme o plano</span><span>Proteção anti-DDoS*</span><span>Backup conforme o plano</span><span>SSL grátis*</span><span>Pagamento via Pix</span></div></section>

<section class="vtx-section" id="vps-home"><div class="container">
    <div class="vtx-section-head"><div><p class="eyebrow">VPS Brasil</p><h2>Potência e controle para sua aplicação.</h2></div><p>Ambientes isolados para APIs, bancos de dados, automações e múltiplos sites.</p></div>
    <div class="vtx-plan-grid">
        @forelse($vpsProducts as $product)<x-plan-card :product="$product" type="vps" />@empty
        <article class="vtx-plan-empty"><p class="eyebrow">Configuração personalizada</p><h3>VPS sob consulta</h3><p>Ainda não há planos VPS públicos cadastrados. Converse com a equipe para dimensionar seu ambiente sem assumir especificações ou preços inexistentes.</p><a class="btn btn-primary" href="{{ auth()->check() ? route('ticket.index') : route('login') }}">Solicitar proposta</a></article>
        @endforelse
    </div>
    <p class="vtx-policy-note">Consulte as condições de cancelamento e elegibilidade na <a href="{{ route('reembolso') }}">Política de Reembolso</a>.</p>
</div></section>

<section class="vtx-section vtx-section-alt" id="hospedagem-home"><div class="container">
    <div class="vtx-section-head"><div><p class="eyebrow">Hospedagem de sites</p><h2>Do primeiro site à operação profissional.</h2></div><p>Planos anuais com recursos claros, painel de gerenciamento e contratação direta.</p></div>
    <div class="vtx-plan-grid">@forelse($webProducts as $product)<x-plan-card :product="$product" type="web" />@empty<p>Nenhum plano disponível no momento.</p>@endforelse</div>
</div></section>

<section class="vtx-domain"><div class="container vtx-domain-box"><div><p class="eyebrow">Domínio</p><h2>Encontre o endereço do seu próximo projeto.</h2><p>A consulta é direcionada ao Registro.br; a Vortex não comercializa domínios diretamente neste momento.</p></div><form action="https://registro.br/tecnologia/ferramentas/whois/" method="get" target="_blank"><label class="sr-only" for="domain-name">Domínio desejado</label><input id="domain-name" name="search" placeholder="suaempresa.com.br" required><button class="btn btn-primary" type="submit">Consultar domínio</button></form></div></section>

<section class="vtx-section"><div class="container"><div class="vtx-section-head"><div><p class="eyebrow">Por que Vortex</p><h2>Infraestrutura sem caixa-preta.</h2></div><p>Você sabe o que está contratando, quanto custa e onde acompanha cada etapa.</p></div><div class="vtx-feature-grid">
    <article><span>01</span><h3>Recursos transparentes</h3><p>Limites e preços publicados em cada plano.</p></article><article><span>02</span><h3>Painel centralizado</h3><p>Pedidos, serviços e suporte em uma única conta.</p></article><article><span>03</span><h3>Pagamento brasileiro</h3><p>Checkout integrado ao Mercado Pago com Pix e cartão.</p></article><article><span>04</span><h3>Suporte com histórico</h3><p>Chamados registrados e vinculados ao cliente.</p></article><article><span>05</span><h3>Provisionamento integrado</h3><p>Fluxo técnico conectado ao pedido aprovado.</p></article><article><span>06</span><h3>Ecossistema CoreLabs</h3><p>Tecnologia orientada a operações digitais.</p></article>
</div></div></section>

<section class="vtx-compare"><div class="container"><div class="vtx-section-head"><div><p class="eyebrow">Compare</p><h2>Uma experiência feita para ser clara.</h2></div></div><div class="vtx-table-wrap"><table><thead><tr><th>Experiência</th><th>Vortex</th><th>Hospedagem comum</th></tr></thead><tbody><tr><td>Especificações antes da compra</td><td>Claras no plano</td><td>Podem ficar dispersas</td></tr><tr><td>Pedidos e serviços</td><td>Painel central</td><td>Múltiplos canais</td></tr><tr><td>Suporte</td><td>Histórico por chamado</td><td>Varia por fornecedor</td></tr><tr><td>Pagamento</td><td>Pix e cartão</td><td>Varia por fornecedor</td></tr></tbody></table></div></div></section>

<section class="vtx-section"><div class="container"><div class="vtx-section-head"><div><p class="eyebrow">Como funciona</p><h2>Da escolha ao serviço em três passos.</h2></div></div><div class="vtx-steps"><article><b>01</b><h3>Escolha</h3><p>Compare recursos e selecione o plano adequado.</p></article><article><b>02</b><h3>Pague</h3><p>Conclua com Pix ou cartão pelo Mercado Pago.</p></article><article><b>03</b><h3>Acompanhe</h3><p>Veja o pedido e o provisionamento na área do cliente.</p></article></div></div></section>

<section class="vtx-tech"><div class="container vtx-tech-grid"><div><p class="eyebrow">Camada técnica</p><h2>Preparada para workloads modernos.</h2><p>Os itens abaixo dependem do produto e devem ser confirmados na especificação do plano contratado.</p></div><dl><div><dt>Virtualização</dt><dd>KVM*</dd></div><div><dt>Armazenamento</dt><dd>SSD / NVMe*</dd></div><div><dt>Rede</dt><dd>Proteção DDoS*</dd></div><div><dt>Gestão</dt><dd>Painel do cliente</dd></div></dl></div></section>

{{-- [SUBSTITUIR POR DADO REAL] Inserir depoimentos verificados antes de publicar prova social. --}}
<section class="vtx-section vtx-placeholder-section"><div class="container"><p class="eyebrow">Experiências reais</p><h2>Depoimentos em validação.</h2><p>Esta área será publicada quando houver autorização e conteúdo verificável dos clientes.</p></div></section>

@php($faqs = [
['A Vortex oferece VPS no Brasil?','A página de VPS apresenta os planos cadastrados e as configurações sob consulta. A localização deve ser confirmada na especificação comercial.'],
['Qual é a periodicidade da hospedagem?','Os planos atuais de hospedagem web são cobrados anualmente.'],
['Posso pagar com Pix?','Sim. O checkout utiliza Mercado Pago e disponibiliza Pix conforme a resposta do provedor.'],
['A hospedagem inclui SSL?','O SSL depende das condições publicadas no plano. Confirme os recursos antes da contratação.'],
['Vocês fazem migração gratuita?','Solicite uma análise ao suporte. A disponibilidade depende da origem, acesso e complexidade da migração.'],
['Como acesso meu serviço?','Pedidos, faturas, serviços e suporte ficam disponíveis na área do cliente.'],
['Existe política de reembolso?','Sim. Consulte a página de Política de Reembolso para conhecer prazos e condições.'],
['A VPS possui acesso administrativo?','As condições de acesso são informadas na oferta de cada VPS ou proposta comercial.'],
['Posso hospedar WordPress?','Os planos web são voltados a sites e aplicações PHP, incluindo WordPress quando compatível com os recursos do plano.'],
['Como funciona o suporte?','O atendimento é feito por chamados autenticados, preservando o histórico da conversa.'],
['Há proteção anti-DDoS?','Ela é informada por produto. Não considere o recurso incluído quando não estiver descrito no plano.'],
])
<section class="vtx-faq" id="faq"><div class="container"><div class="vtx-section-head"><div><p class="eyebrow">FAQ</p><h2>Dúvidas frequentes</h2></div></div><div class="vtx-faq-list">@foreach($faqs as [$question,$answer])<details><summary>{{ $question }}</summary><p>{{ $answer }}</p></details>@endforeach</div></div></section>

<section class="vtx-final"><div class="container"><div><p class="eyebrow">Pronto para migrar?</p><h2>Traga seu projeto para uma base mais clara.</h2><p>Migração sujeita à análise técnica. Fale com a equipe antes de contratar.</p></div><a class="btn btn-primary btn-lg" href="{{ auth()->check() ? route('ticket.index') : route('registro') }}">Solicitar migração</a></div></section>

<script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@type'=>'FAQPage','mainEntity'=>collect($faqs)->map(fn($faq)=>['@type'=>'Question','name'=>$faq[0],'acceptedAnswer'=>['@type'=>'Answer','text'=>$faq[1]]])->all()], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
@php($catalogProducts = $webProducts->map(fn($product) => [$product, 'ano'])->concat($vpsProducts->map(fn($product) => [$product, 'mês'])))
@if($catalogProducts->isNotEmpty())
<script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@type'=>'ItemList','itemListElement'=>$catalogProducts->values()->map(fn($entry,$index)=>['@type'=>'ListItem','position'=>$index+1,'item'=>['@type'=>'Product','name'=>$entry[0]->nome,'description'=>$entry[0]->descricao,'brand'=>['@type'=>'Brand','name'=>'Vortex'],'offers'=>['@type'=>'Offer','priceCurrency'=>'BRL','price'=>(string)$entry[0]->preco,'availability'=>'https://schema.org/InStock','url'=>$entry[1]==='ano' ? route('web') : route('vps.index')]]])->all()], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
@endif
@endsection
