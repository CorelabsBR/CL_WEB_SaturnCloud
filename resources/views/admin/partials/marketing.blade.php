<section class="admin-block admin-tab-panel" id="marketing" data-admin-panel="marketing" hidden>
    <div class="admin-block-head">
        <div><h2>Marketing</h2><p>Campanhas, conteúdo, aquisição e integrações dentro da operação administrativa da Saturn Cloud.</p></div>
        <a class="btn btn-ghost btn-sm" href="{{ route('marketing.openapi') }}">Documentação da API</a>
    </div>

    <div class="admin-marketing-metrics">
        <article><span>Campanhas</span><strong>{{ $marketingMetrics['campaigns'] }}</strong></article>
        <article><span>Aprovações pendentes</span><strong>{{ $marketingMetrics['pending'] }}</strong></article>
        <article><span>Clientes reais</span><strong>{{ $marketingMetrics['customers'] }}</strong></article>
        <article><span>Pedidos pagos</span><strong>{{ $marketingMetrics['paid_orders'] }}</strong></article>
        <article><span>Receita confirmada</span><strong>{{ $money($marketingMetrics['paid_revenue']) }}</strong></article>
    </div>

    <nav class="admin-marketing-nav" aria-label="Módulos de marketing">
        <a href="#marketing-campanhas">Campanhas</a><a href="#marketing-editorial">Publicações</a><a href="#marketing-editorial">Calendário</a>
        <a href="#marketing-seo">SEO</a><a href="#marketing-analytics">Analytics</a><a href="#marketing-growth">Leads</a>
        <a href="#marketing-growth">Conversões</a><a href="#marketing-growth">Indicações</a><a href="#cupons" data-admin-jump="cupons">Cupons</a>
        <a href="#marketing-assets">Assets</a><a href="#marketing-integracoes">Integrações</a><a href="#marketing-automacoes">Automações</a>
        <a href="#marketing-configuracoes">Configurações</a>
    </nav>

    <section class="admin-marketing-module" id="marketing-campanhas">
        <div class="admin-product-panel-head"><div><strong>Campanhas</strong><span>Criação e aprovação; nenhuma mídia paga é iniciada automaticamente.</span></div></div>
        <div class="admin-marketing-columns">
            <form method="POST" action="{{ route('marketing.campaigns.store') }}" class="admin-form-stack admin-form-section">@csrf
                <strong>Nova campanha</strong>
                <label>Nome<input name="name" required maxlength="255"></label><label>Objetivo<input name="objective" required maxlength="255"></label>
                <label>Produto<select name="product_id"><option value="">Institucional / sem produto</option>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->nome }}</option>@endforeach</select></label>
                <label>Público-alvo<textarea name="audience" required maxlength="500"></textarea></label>
                <label>Canal<select name="channel" required><option value="organic">Orgânico</option><option value="email">E-mail consentido</option><option value="instagram">Instagram</option><option value="facebook">Facebook</option><option value="linkedin">LinkedIn</option><option value="discord">Discord</option><option value="telegram">Telegram</option><option value="other">Outro</option></select></label>
                <label>Mensagem<textarea name="message" required maxlength="5000"></textarea></label>
                <div class="admin-form-grid"><label>Início<input type="datetime-local" name="starts_at"></label><label>Término<input type="datetime-local" name="ends_at"></label><label>Orçamento máximo<input type="number" name="budget_limit" min="0" step="0.01"></label></div>
                <label>URL rastreável<input type="url" name="tracking_url" placeholder="https://"></label><input type="hidden" name="status" value="draft"><button class="btn btn-primary" type="submit">Salvar rascunho</button>
            </form>
            <div class="admin-marketing-records">@forelse($marketingCampaigns as $campaign)<article><div><span class="admin-marketing-state">{{ str_replace('_',' ',$campaign->status) }}</span><h3>{{ $campaign->name }}</h3><p>{{ $campaign->objective }} · {{ $campaign->channel }}@if($campaign->product) · {{ $campaign->product->nome }}@endif</p></div>@if(!$campaign->approved_at)<form method="POST" action="{{ route('marketing.campaigns.approve',$campaign) }}">@csrf<button class="btn btn-ghost btn-sm">Aprovar</button></form>@else<small>Aprovada em {{ $campaign->approved_at->format('d/m/Y H:i') }}</small>@endif</article>@empty<div class="admin-empty">Nenhuma campanha cadastrada.</div>@endforelse</div>
        </div>
    </section>

    <section class="admin-marketing-module" id="marketing-editorial">
        <div class="admin-product-panel-head"><div><strong>Publicações e calendário</strong><span>Rascunhos versionados; aprovação não equivale a publicação.</span></div></div>
        <div class="admin-marketing-columns">
            <form method="POST" action="{{ route('marketing.contents.store') }}" class="admin-form-stack admin-form-section">@csrf
                <strong>Novo conteúdo</strong><label>Título<input name="title" required maxlength="255"></label>
                <div class="admin-form-grid"><label>Tipo<select name="type"><option value="article">Artigo</option><option value="tutorial">Tutorial</option><option value="news">Notícia</option><option value="announcement">Anúncio</option></select></label><label>Categoria<input name="category" maxlength="100"></label><label>Slug opcional<input name="slug" maxlength="255"></label></div>
                <label>Markdown<textarea name="body_markdown" rows="12" required maxlength="100000"></textarea></label><label>Resumo<textarea name="excerpt" maxlength="500"></textarea></label>
                <div class="admin-form-grid"><label>Título SEO<input name="seo_title" maxlength="60"></label><label>Descrição SEO<input name="seo_description" maxlength="160"></label></div>
                <input type="hidden" name="status" value="draft"><button class="btn btn-primary">Salvar rascunho</button>
            </form>
            <div class="admin-marketing-records">@forelse($marketingContents as $content)<article><div><span class="admin-marketing-state">{{ str_replace('_',' ',$content->status) }}</span><h3>{{ $content->title }}</h3><p>{{ $content->type }} · {{ $content->revisions_count }} revisão(ões)</p></div>@if(!$content->approved_by)<form method="POST" action="{{ route('marketing.contents.approve',$content) }}">@csrf<button class="btn btn-ghost btn-sm">Aprovar</button></form>@else<small>Aprovado; publicação permanece manual</small>@endif</article>@empty<div class="admin-empty">Nenhum conteúdo cadastrado.</div>@endforelse</div>
        </div>
    </section>

    <section class="admin-marketing-module" id="marketing-seo"><div class="admin-product-panel-head"><div><strong>SEO</strong><span>Sitemap dinâmico, robots, canonical e metadados estão integrados ao site.</span></div><a href="{{ route('sitemap') }}" target="_blank" rel="noopener" class="btn btn-ghost btn-sm">Abrir sitemap</a></div></section>
    <section class="admin-marketing-module" id="marketing-analytics"><div class="admin-product-panel-head"><div><strong>Analytics e conversões</strong><span>Eventos first-party exigem consentimento. Pedidos e receita abaixo usam somente estados confirmados.</span></div></div><div class="admin-marketing-metrics compact"><article><span>Eventos · 30 dias</span><strong>{{ $marketingMetrics['events'] }}</strong></article><article><span>Conteúdos publicados</span><strong>{{ $marketingMetrics['published'] }}</strong></article><article><span>Pedidos pagos</span><strong>{{ $marketingMetrics['paid_orders'] }}</strong></article></div></section>
    <section class="admin-marketing-module" id="marketing-growth"><div class="admin-product-panel-head"><div><strong>Leads, conversões e indicações</strong><span>Atribuição e programa de indicação ainda não estão habilitados; nenhum benefício é concedido.</span></div></div><div class="admin-empty">Módulos aguardando regras comerciais e implementação antifraude.</div></section>
    <section class="admin-marketing-module" id="marketing-assets"><div class="admin-product-panel-head"><div><strong>Biblioteca de assets</strong><span>Nenhum gerador de imagens está conectado. Use somente materiais oficiais autorizados.</span></div></div><div class="admin-empty">Biblioteca controlada ainda não implementada.</div></section>
    <section class="admin-marketing-module" id="marketing-integracoes"><div class="admin-product-panel-head"><div><strong>Integrações</strong><span>Estados persistidos; ausência de credenciais nunca aparece como conexão ativa.</span></div></div><div class="admin-marketing-integrations">@foreach($marketingIntegrations as $integration)<article><strong>{{ str_replace('_',' ',ucfirst($integration->provider)) }}</strong><span>{{ $integration->enabled ? 'Configurada' : 'Não configurada' }}</span><small>Última sincronização: {{ $integration->last_synced_at?->format('d/m/Y H:i') ?? 'nunca' }}</small></article>@endforeach</div></section>
    <section class="admin-marketing-module" id="marketing-automacoes"><div class="admin-product-panel-head"><div><strong>Automações</strong><span>Worker interno processa itens aprovados e agendados sem depender do navegador.</span></div></div><p class="admin-marketing-note"><code>marketing:process-schedule</code> · execução a cada minuto · bloqueio contra sobreposição ativo.</p></section>
    <section class="admin-marketing-module" id="marketing-configuracoes"><div class="admin-product-panel-head"><div><strong>Configurações</strong><span>Permissões usam a sessão e o papel root existentes. Segredos futuros serão criptografados no backend.</span></div></div></section>
</section>
