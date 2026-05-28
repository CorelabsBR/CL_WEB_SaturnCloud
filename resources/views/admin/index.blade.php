@extends('layouts.app')

@section('title', 'Admin - VortexHost')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
@endpush

@section('content')
@php
    $money = fn ($value) => 'R$ ' . number_format((float) $value, 2, ',', '.');
    $statusClass = [
        'aberto' => 'is-open',
        'respondido' => 'is-answered',
        'fechado' => 'is-closed',
        'pendente' => 'is-open',
        'pago' => 'is-answered',
        'cancelado' => 'is-closed',
    ];

    $productFolders = $games->map(function ($game) use ($products) {
        return [
            'id' => 'game-' . $game->id,
            'name' => $game->nome,
            'icon' => $game->icon ? asset('img/games/' . $game->icon) : null,
            'products' => $products->where('jogo_id', $game->id),
        ];
    });

    $unlinkedProducts = $products->whereNull('jogo_id');

    if ($unlinkedProducts->isNotEmpty()) {
        $productFolders->push([
            'id' => 'no-game',
            'name' => 'Sem jogo vinculado',
            'icon' => null,
            'products' => $unlinkedProducts,
        ]);
    }

    $productCategoryFolders = $categories->map(function ($category) use ($products) {
        return [
            'id' => 'category-' . $category->id,
            'name' => $category->nome,
            'products' => $products->where('categoria_id', $category->id),
        ];
    })->filter(fn ($folder) => $folder['products']->isNotEmpty())->values();

    $uncategorizedProducts = $products->whereNull('categoria_id');

    if ($uncategorizedProducts->isNotEmpty()) {
        $productCategoryFolders->push([
            'id' => 'category-none',
            'name' => 'Sem categoria',
            'products' => $uncategorizedProducts,
        ]);
    }
@endphp

<section class="admin-page">
    <div class="container">
        <div class="admin-head">
            <div>
                <span class="admin-kicker">VortexHost Admin</span>
                <h1>Painel administrativo</h1>
                <p>Gerencie produtos, imagens, pedidos e chamados de suporte em um só lugar.</p>
            </div>
        </div>

        @if(session('admin_success'))
            <div class="admin-alert admin-alert-success">{{ session('admin_success') }}</div>
        @endif

        @if($errors->any())
            <div class="admin-alert admin-alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="admin-stats">
            <article class="admin-stat-card">
                <span class="admin-stat-lbl">Clientes</span>
                <strong class="admin-stat-val">{{ $stats['users'] }}</strong>
            </article>
            <article class="admin-stat-card">
                <span class="admin-stat-lbl">Produtos</span>
                <strong class="admin-stat-val">{{ $stats['products'] }}</strong>
            </article>
            <article class="admin-stat-card">
                <span class="admin-stat-lbl">Chamados ativos</span>
                <strong class="admin-stat-val">{{ $stats['openTickets'] }}</strong>
            </article>
            <article class="admin-stat-card">
                <span class="admin-stat-lbl">Pedidos</span>
                <strong class="admin-stat-val">{{ $stats['orders'] }}</strong>
            </article>
            <article class="admin-stat-card">
                <span class="admin-stat-lbl">Cupons</span>
                <strong class="admin-stat-val">{{ $stats['coupons'] }}</strong>
            </article>
        </div>

        <div class="admin-tabs" role="tablist" aria-label="Seções do painel admin">
            <button type="button" class="is-active" data-admin-tab="suporte" role="tab" aria-selected="true">
                Suporte
            </button>
            <button type="button" data-admin-tab="produtos" role="tab" aria-selected="false">
                Produtos
            </button>
            <button type="button" data-admin-tab="cupons" role="tab" aria-selected="false">
                Cupons
            </button>
            <button type="button" data-admin-tab="jogos" role="tab" aria-selected="false">
                Fotos dos jogos
            </button>
            <button type="button" data-admin-tab="pedidos" role="tab" aria-selected="false">
                Pedidos
            </button>
        </div>

        <section
            class="admin-block admin-tab-panel is-active"
            id="suporte"
            data-admin-panel="suporte"
            data-admin-support-pulse="{{ route('admin.tickets.pulse') }}"
            data-admin-csrf="{{ csrf_token() }}"
            data-admin-support-signature="{{ implode(':', [
                \App\Models\SupportTicket::count(),
                \App\Models\SupportTicket::max('atualizado_em'),
                \App\Models\SupportMessage::count(),
                \App\Models\SupportMessage::max('criado_em'),
            ]) }}"
        >
            <div class="admin-block-head">
                <div>
                    <h2>Chats do suporte</h2>
                    <p>Veja conversas, responda clientes e feche chamados.</p>
                </div>
            </div>

            @if($tickets->isNotEmpty())
                <div class="admin-support-shell" data-admin-support-shell>
                    <aside class="admin-support-list" aria-label="Conversas de suporte">
                        @foreach($tickets as $ticket)
                            @php
                                $lastMessage = $ticket->messages->last();
                                $clientName = $ticket->user?->nome ?? 'Cliente';
                                $initial = strtoupper(substr($clientName, 0, 1));
                            @endphp

                            <button
                                type="button"
                                class="admin-support-contact {{ $loop->first ? 'is-active' : '' }}"
                                data-admin-chat-target="ticket-{{ $ticket->id }}"
                            >
                                <span class="admin-support-avatar" aria-hidden="true">{{ $initial }}</span>
                                <span class="admin-support-contact-main">
                                    <span class="admin-support-contact-top">
                                        <strong>{{ $clientName }}</strong>
                                        <small>{{ optional($lastMessage?->criado_em)->format('H:i') }}</small>
                                    </span>
                                    <span class="admin-support-contact-preview">
                                        {{ $lastMessage?->body ? \Illuminate\Support\Str::limit($lastMessage->body, 44) : $ticket->subject }}
                                    </span>
                                </span>
                            </button>
                        @endforeach
                    </aside>

                    <div class="admin-support-chat-area">
                        @foreach($tickets as $ticket)
                            <article
                                class="admin-ticket admin-support-chat {{ $loop->first ? 'is-active' : '' }}"
                                data-admin-chat-panel="ticket-{{ $ticket->id }}"
                                @if(! $loop->first) hidden @endif
                            >
                                <div class="admin-support-chat-head">
                                    <div>
                                        <h3>{{ $ticket->user?->nome ?? 'Cliente' }}</h3>
                                        <p>#{{ $ticket->id }} {{ $ticket->subject }} · {{ $ticket->category }}</p>
                                    </div>
                                    <form method="POST" action="{{ route('admin.tickets.destroy', $ticket) }}" data-admin-close-chat>
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm">Fechar chat</button>
                                    </form>
                                </div>

                                <div class="admin-chat-log">
                                    @foreach($ticket->messages as $message)
                                        <div class="admin-chat-message {{ $message->author_type === 'admin' ? 'from-admin' : 'from-client' }}">
                                            <strong>{{ $message->author_type === 'admin' ? ($message->user?->nome ?? 'Admin') : ($ticket->user?->nome ?? 'Cliente') }}</strong>
                                            @if($message->body)
                                                <p>{{ $message->body }}</p>
                                            @endif
                                            @if($message->image_path)
                                                <a href="{{ asset($message->image_path) }}" target="_blank" rel="noopener">
                                                    <img src="{{ asset($message->image_path) }}" alt="Imagem enviada" class="admin-chat-image">
                                                </a>
                                            @endif
                                            <small>{{ optional($message->criado_em)->format('d/m/Y H:i') }}</small>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="admin-ticket-actions">
                                    <form method="POST" action="{{ route('admin.tickets.reply', $ticket) }}" enctype="multipart/form-data" class="admin-chat-composer">
                                        @csrf
                                        <div class="admin-chat-composer-row">
                                            <label class="admin-chat-image-button" title="Adicionar imagem">
                                                <input type="file" name="image" accept="image/*" data-admin-chat-image>
                                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M4 17l4.5-4.5a2 2 0 0 1 2.8 0L16 17" />
                                                    <path d="M14 15l1.5-1.5a2 2 0 0 1 2.8 0L20 15" />
                                                    <path d="M5 5h14v14H5z" />
                                                    <path d="M15 8h.01" />
                                                </svg>
                                            </label>
                                            <textarea name="message" rows="1" placeholder="Escrever mensagem..."></textarea>
                                            <button type="submit" aria-label="Enviar resposta" title="Enviar">
                                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M3 11.5 20 3l-4.8 18-3.6-7.2L3 11.5Zm8.6 2.3L20 3" />
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="admin-chat-attachment-row">
                                            <small data-admin-chat-image-name hidden></small>
                                            <button class="admin-chat-image-clear" type="button" data-admin-chat-image-clear hidden aria-label="Remover imagem anexada" title="Remover imagem">
                                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M18 6 6 18" />
                                                    <path d="m6 6 12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="admin-empty">Nenhum chamado aberto ainda.</div>
            @endif
        </section>

        <section class="admin-block admin-tab-panel" id="produtos" data-admin-panel="produtos" hidden>
            <div class="admin-block-head">
                <div>
                    <h2>Produtos</h2>
                    <p>Adicione, edite e remova planos da loja.</p>
                </div>
            </div>

            <div class="admin-subtabs admin-product-tabs" role="tablist" aria-label="Gerenciamento de produtos">
                <button type="button" class="is-active" data-product-tab="novo" aria-selected="true">
                    Novo produto
                </button>
                @foreach($productCategoryFolders as $folder)
                    <button type="button" data-product-tab="{{ $folder['id'] }}" aria-selected="false">
                        {{ $folder['name'] }}
                        <span>{{ $folder['products']->count() }}</span>
                    </button>
                @endforeach
            </div>

            <div class="admin-product-workspace">
                <div data-product-panel="novo">
                    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="admin-product-form admin-create-form">
                        @csrf
                        @include('admin.partials.product-fields', ['product' => null])
                        <div class="admin-form-actions">
                            <button class="btn btn-primary" type="submit">Adicionar produto</button>
                        </div>
                    </form>
                </div>

                @forelse($productCategoryFolders as $folder)
                    <div data-product-panel="{{ $folder['id'] }}" hidden>
                        <div class="admin-product-panel-head">
                            <div>
                                <strong>{{ $folder['name'] }}</strong>
                                <span>{{ $folder['products']->count() }} produto{{ $folder['products']->count() === 1 ? '' : 's' }} cadastrado{{ $folder['products']->count() === 1 ? '' : 's' }}</span>
                            </div>
                        </div>

                        <div class="table-wrap admin-products-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Produto</th>
                                        <th>Jogo</th>
                                        <th>Preço</th>
                                        <th>Ordem</th>
                                        <th>Destaque</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($folder['products'] as $product)
                                        <tr>
                                            <td>
                                                <div class="admin-product-cell">
                                                    @if($product->imagem)
                                                        <img src="{{ asset($product->imagem) }}" alt="{{ $product->nome }}">
                                                    @else
                                                        <span class="admin-product-placeholder" aria-hidden="true">{{ strtoupper(substr($product->nome, 0, 1)) }}</span>
                                                    @endif
                                                    <div>
                                                        <strong>{{ $product->nome }}</strong>
                                                        <span>{{ $product->descricao ? \Illuminate\Support\Str::limit($product->descricao, 70) : 'Sem descrição' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $product->jogo?->nome ?? '-' }}</td>
                                            <td>{{ $money($product->preco) }}</td>
                                            <td>{{ $product->sort_order ?? '-' }}</td>
                                            <td>{{ $product->featured ? 'Sim' : 'Não' }}</td>
                                            <td class="td-actions">
                                                <details class="row-actions">
                                                    <summary>Editar</summary>
                                                    <div class="edit-dropdown admin-edit-panel">
                                                        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="admin-product-form">
                                                            @csrf
                                                            @method('PATCH')
                                                            @include('admin.partials.product-fields', ['product' => $product])
                                                            <div class="admin-form-actions">
                                                                <button class="btn btn-primary btn-sm" type="submit">Salvar</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </details>

                                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Remover este produto?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-ghost btn-sm" type="submit">Remover</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div data-product-panel="empty" hidden>
                        <div class="admin-empty">Nenhum produto cadastrado ainda.</div>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="admin-block admin-tab-panel" id="cupons" data-admin-panel="cupons" hidden>
            <div class="admin-block-head">
                <div>
                    <h2>Cupons</h2>
                    <p>Crie descontos percentuais ou valores fixos para o checkout.</p>
                </div>
            </div>

            <div class="admin-subtabs" role="tablist" aria-label="Gerenciamento de cupons">
                <button type="button" class="is-active" data-coupon-tab="criar" aria-selected="true">
                    Criar
                </button>
                <button type="button" data-coupon-tab="ativos" aria-selected="false">
                    Ativos
                </button>
            </div>

            <div data-coupon-panel="criar">
                <form method="POST" action="{{ route('admin.coupons.store') }}" class="admin-product-form admin-create-form" data-coupon-form>
                    @csrf
                    <div class="admin-form-section admin-form-section-main">
                        <div class="admin-form-section-head">
                            <strong>Novo cupom</strong>
                            <span>Percentual aceita até 100%. Valor fixo desconta em reais.</span>
                        </div>

                        <div class="admin-form-grid">
                            <label>
                                Código
                                <input type="text" name="code" placeholder="Ex: ReiDavi" required>
                            </label>
                            <label>
                                Tipo
                                <select name="type" required data-coupon-type>
                                    <option value="percent">Percentual</option>
                                    <option value="fixed">Valor fixo</option>
                                </select>
                            </label>
                            <label>
                                <span data-coupon-value-label>Percentual (%)</span>
                                <input type="number" name="value" min="0.01" max="100" step="0.01" placeholder="100" required data-coupon-value>
                            </label>
                        </div>

                        <div class="admin-form-actions">
                            <label class="form-check-row">
                                <input type="checkbox" name="active" value="1" checked>
                                Ativo
                            </label>
                            <label class="form-check-row">
                                <input type="checkbox" name="single_use" value="1">
                                Uso único
                            </label>
                            <button class="btn btn-primary" type="submit">Criar cupom</button>
                        </div>
                    </div>
                </form>
            </div>

            <div data-coupon-panel="ativos" hidden>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Tipo</th>
                                <th>Valor</th>
                                <th>Uso</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($coupons as $coupon)
                                <tr>
                                    <td><strong>{{ $coupon->code }}</strong></td>
                                    <td>{{ $coupon->type === 'percent' ? 'Percentual' : 'Valor fixo' }}</td>
                                    <td>{{ $coupon->type === 'percent' ? number_format((float) $coupon->value, 2, ',', '.') . '%' : $money($coupon->value) }}</td>
                                    <td>
                                        {{ $coupon->usage_limit ? $coupon->used_count . '/' . $coupon->usage_limit : $coupon->used_count . '/ilimitado' }}
                                    </td>
                                    <td>
                                        <span class="admin-status {{ $coupon->isAvailable() ? 'is-answered' : 'is-closed' }}">
                                            {{ $coupon->isAvailable() ? 'Ativo' : ($coupon->active ? 'Esgotado' : 'Inativo') }}
                                        </span>
                                    </td>
                                    <td class="td-actions">
                                        <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" onsubmit="return confirm('Excluir este cupom?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-ghost btn-sm" type="submit">Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">Nenhum cupom cadastrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="admin-block admin-tab-panel" id="jogos" data-admin-panel="jogos" hidden>
            <div class="admin-block-head">
                <div>
                    <h2>Fotos e jogos</h2>
                    <p>Atualize nome, slug, banner, ícone e visibilidade dos jogos.</p>
                </div>
                <div class="admin-block-actions">
                    <button class="btn btn-primary btn-sm" type="button" id="save-all-games-button">Salvar todos</button>
                </div>
            </div>

            <div class="admin-game-grid">
                @foreach($games as $game)
                    <article class="admin-game-card">
                        <div class="admin-game-preview">
                            @if($game->banner)
                                <img src="{{ asset('img/games/' . $game->banner) }}" alt="{{ $game->nome }}">
                            @else
                                <span>Sem banner</span>
                            @endif
                        </div>

                        <form method="POST" action="{{ route('admin.games.update', $game) }}" enctype="multipart/form-data" class="admin-form-stack">
                            @csrf
                            @method('PATCH')
                            <label>
                                Nome
                                <input type="text" name="nome" value="{{ old('nome', $game->nome) }}" required>
                            </label>
                            <label>
                                Slug
                                <input type="text" name="slug" value="{{ old('slug', $game->slug) }}">
                            </label>
                            <label>
                                Banner
                                <input type="file" name="banner" accept="image/*">
                            </label>
                            <label>
                                Ícone
                                <input type="file" name="icon" accept="image/*">
                            </label>
                            <label class="form-check-row">
                                <input type="checkbox" name="ativo" value="1" @checked($game->ativo)>
                                Ativo na loja
                            </label>
                            <button class="btn btn-primary btn-sm" type="submit">Salvar jogo</button>
                        </form>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="admin-block admin-tab-panel" id="pedidos" data-admin-panel="pedidos" hidden>
            <div class="admin-block-head">
                <div>
                    <h2>Pedidos e renovações</h2>
                    <p>Acompanhe compras e renovações de VPS com métodos de pagamento.</p>
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Tipo</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Pagamento</th>
                            <th>Vencimento</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            @php
                                $payment = $order->payments->sortByDesc('id')->first();
                                $method = collect([
                                    $payment?->payment_method_id,
                                    $payment?->payment_type_id,
                                ])->filter()->join(' / ') ?: 'Aguardando';
                                $typeLabel = $order->billing_type === 'renewal' ? 'Renovação' : 'Compra inicial';
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ 'VTX-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT) }}</strong>
                                </td>
                                <td>{{ $order->user?->nome ?? '-' }}</td>
                                <td>
                                    {{ $typeLabel }}
                                    @if($order->billing_type === 'renewal' && $order->renewalService)
                                        <br><small>{{ $order->renewalService->name ?? $order->renewalService->product?->nome }}</small>
                                    @endif
                                </td>
                                <td>{{ $money($order->total) }}</td>
                                <td><span class="admin-status {{ $statusClass[$order->status] ?? '' }}">{{ ucfirst($order->status) }}</span></td>
                                <td>
                                    {{ $method }}
                                    @if($payment)
                                        <br><small>{{ ucfirst($payment->status) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($order->billing_type === 'renewal')
                                        {{ optional($order->due_at)->format('d/m/Y H:i') ?? 'Sem vencimento' }}
                                    @else
                                        Não gera fatura
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">Nenhum pedido encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</section>
@endsection

@push('scripts')
<script>
    (() => {
        const tabs = document.querySelectorAll('[data-admin-tab]');
        const panels = document.querySelectorAll('[data-admin-panel]');
        const supportBlock = document.querySelector('[data-admin-support-pulse]');
        const couponForms = document.querySelectorAll('[data-coupon-form]');
        const couponTabs = document.querySelectorAll('[data-coupon-tab]');
        const couponPanels = document.querySelectorAll('[data-coupon-panel]');
        const productTabs = document.querySelectorAll('[data-product-tab]');
        const productPanels = document.querySelectorAll('[data-product-panel]');

        const syncCouponValueField = (form) => {
            const type = form.querySelector('[data-coupon-type]')?.value;
            const label = form.querySelector('[data-coupon-value-label]');
            const input = form.querySelector('[data-coupon-value]');
            const isPercent = type === 'percent';

            if (label) {
                label.textContent = isPercent ? 'Percentual (%)' : 'Valor (R$)';
            }

            if (input) {
                input.max = isPercent ? '100' : '999999.99';
                input.placeholder = isPercent ? '100' : '25.00';

                if (isPercent && Number(input.value) > 100) {
                    input.value = '100';
                }
            }
        };

        couponForms.forEach((form) => {
            syncCouponValueField(form);
            form.querySelector('[data-coupon-type]')?.addEventListener('change', () => syncCouponValueField(form));
        });

        const activateCouponTab = (target) => {
            couponTabs.forEach((tab) => {
                const isActive = tab.dataset.couponTab === target;
                tab.classList.toggle('is-active', isActive);
                tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            couponPanels.forEach((panel) => {
                panel.hidden = panel.dataset.couponPanel !== target;
            });
        };

        couponTabs.forEach((tab) => {
            tab.addEventListener('click', () => activateCouponTab(tab.dataset.couponTab));
        });

        const activateProductTab = (target) => {
            productTabs.forEach((tab) => {
                const isActive = tab.dataset.productTab === target;
                tab.classList.toggle('is-active', isActive);
                tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            productPanels.forEach((panel) => {
                panel.hidden = panel.dataset.productPanel !== target;
            });
        };

        productTabs.forEach((tab) => {
            tab.addEventListener('click', () => activateProductTab(tab.dataset.productTab));
        });

        const activateTab = (target) => {
            tabs.forEach((tab) => {
                const isActive = tab.dataset.adminTab === target;
                tab.classList.toggle('is-active', isActive);
                tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            panels.forEach((panel) => {
                const isActive = panel.dataset.adminPanel === target;
                panel.classList.toggle('is-active', isActive);
                panel.hidden = !isActive;
            });

            if (target === 'suporte') {
                document.querySelectorAll('.admin-chat-log').forEach((chatLog) => {
                    chatLog.scrollTop = chatLog.scrollHeight;
                });
            }

            history.replaceState(null, '', `#${target}`);
        };

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => activateTab(tab.dataset.adminTab));
        });

        const initialTab = window.location.hash.replace('#', '');

        if (initialTab && document.querySelector(`[data-admin-panel="${initialTab}"]`)) {
            activateTab(initialTab);
        }

        const saveAllGamesButton = document.getElementById('save-all-games-button');
        const gameSection = document.querySelector('[data-admin-panel="jogos"]');
        const gameForms = gameSection?.querySelectorAll('.admin-form-stack');

        if (saveAllGamesButton && gameForms?.length) {
            saveAllGamesButton.addEventListener('click', async () => {
                saveAllGamesButton.disabled = true;
                saveAllGamesButton.textContent = 'Salvando...';

                for (const form of gameForms) {
                    if (!form.checkValidity()) {
                        form.reportValidity();
                        saveAllGamesButton.disabled = false;
                        saveAllGamesButton.textContent = 'Salvar todos';
                        return;
                    }

                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: formData,
                    });

                    if (!response.ok) {
                        saveAllGamesButton.disabled = false;
                        saveAllGamesButton.textContent = 'Salvar todos';
                        window.alert('Falha ao salvar alguns jogos. Verifique a página e tente novamente.');
                        return;
                    }
                }

                window.location.reload();
            });
        }

        if (!supportBlock) {
            return;
        }

        let signature = supportBlock.dataset.adminSupportSignature || '';
        const csrfToken = supportBlock.dataset.adminCsrf || '';
        const compressedUploadLimit = 1800 * 1024;
        const imageExtensionPattern = /\.(avif|bmp|gif|jpe?g|png|svg|webp)$/i;

        document.querySelectorAll('.admin-chat-log').forEach((chatLog) => {
            chatLog.scrollTop = chatLog.scrollHeight;
        });

        const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        })[char]);

        const activateChat = (target) => {
            const contact = supportBlock.querySelector(`[data-admin-chat-target="${target}"]`);

            supportBlock.querySelectorAll('[data-admin-chat-target]').forEach((item) => {
                item.classList.toggle('is-active', item === contact);
            });

            supportBlock.querySelectorAll('[data-admin-chat-panel]').forEach((panel) => {
                const isActive = panel.dataset.adminChatPanel === target;
                panel.classList.toggle('is-active', isActive);
                panel.hidden = !isActive;
            });

            const chatLog = supportBlock.querySelector(`[data-admin-chat-panel="${target}"] .admin-chat-log`);

            if (chatLog) {
                chatLog.scrollTop = chatLog.scrollHeight;
            }
        };

        const ensureSupportShell = () => {
            let shell = supportBlock.querySelector('[data-admin-support-shell]');

            if (shell) {
                return shell;
            }

            supportBlock.querySelector('.admin-empty')?.remove();

            shell = document.createElement('div');
            shell.className = 'admin-support-shell';
            shell.setAttribute('data-admin-support-shell', '');
            shell.innerHTML = `
                <aside class="admin-support-list" aria-label="Conversas de suporte"></aside>
                <div class="admin-support-chat-area"></div>
            `;
            supportBlock.appendChild(shell);

            return shell;
        };

        const ticketTarget = (ticketData) => `ticket-${ticketData.id}`;

        const renderContact = (ticketData, isActive) => `
            <button
                type="button"
                class="admin-support-contact ${isActive ? 'is-active' : ''}"
                data-admin-chat-target="${ticketTarget(ticketData)}"
            >
                <span class="admin-support-avatar" aria-hidden="true">${escapeHtml(ticketData.initial || '?')}</span>
                <span class="admin-support-contact-main">
                    <span class="admin-support-contact-top">
                        <strong>${escapeHtml(ticketData.client_name || 'Cliente')}</strong>
                        <small>${escapeHtml(ticketData.last_time || '')}</small>
                    </span>
                    <span class="admin-support-contact-preview">
                        ${escapeHtml(ticketData.preview || ticketData.subject || 'Sem mensagens')}
                    </span>
                </span>
            </button>
        `;

        const renderChatPanel = (ticketData, isActive) => {
            const messages = (ticketData.messages || []).map((message) => {
                const className = message.author_type === 'admin' ? 'from-admin' : 'from-client';

                return `
                    <div class="admin-chat-message ${className}">
                        <strong>${escapeHtml(message.author_name)}</strong>
                        ${message.body ? `<p>${escapeHtml(message.body)}</p>` : ''}
                        ${message.image_url ? `<a href="${escapeHtml(message.image_url)}" target="_blank" rel="noopener"><img src="${escapeHtml(message.image_url)}" alt="Imagem enviada" class="admin-chat-image"></a>` : ''}
                        <small>${escapeHtml(message.time)}</small>
                    </div>
                `;
            }).join('');

            return `
                <article
                    class="admin-ticket admin-support-chat ${isActive ? 'is-active' : ''}"
                    data-admin-chat-panel="${ticketTarget(ticketData)}"
                    ${isActive ? '' : 'hidden'}
                >
                    <div class="admin-support-chat-head">
                        <div>
                            <h3>${escapeHtml(ticketData.client_name || 'Cliente')}</h3>
                            <p>#${escapeHtml(ticketData.id)} ${escapeHtml(ticketData.subject || '')} · ${escapeHtml(ticketData.category || '')}</p>
                        </div>
                        <form method="POST" action="${escapeHtml(ticketData.destroy_url || '#')}" data-admin-close-chat>
                            <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn btn-ghost btn-sm">Fechar chat</button>
                        </form>
                    </div>

                    <div class="admin-chat-log">${messages}</div>

                    <div class="admin-ticket-actions">
                        <form method="POST" action="${escapeHtml(ticketData.reply_url || '#')}" enctype="multipart/form-data" class="admin-chat-composer">
                            <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}">
                            <div class="admin-chat-composer-row">
                                <label class="admin-chat-image-button" title="Adicionar imagem">
                                    <input type="file" name="image" accept="image/*" data-admin-chat-image>
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M4 17l4.5-4.5a2 2 0 0 1 2.8 0L16 17" />
                                        <path d="M14 15l1.5-1.5a2 2 0 0 1 2.8 0L20 15" />
                                        <path d="M5 5h14v14H5z" />
                                        <path d="M15 8h.01" />
                                    </svg>
                                </label>
                                <textarea name="message" rows="1" placeholder="Escrever mensagem..."></textarea>
                                <button type="submit" aria-label="Enviar resposta" title="Enviar">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M3 11.5 20 3l-4.8 18-3.6-7.2L3 11.5Zm8.6 2.3L20 3" />
                                    </svg>
                                </button>
                            </div>
                            <div class="admin-chat-attachment-row">
                                <small data-admin-chat-image-name hidden></small>
                                <button class="admin-chat-image-clear" type="button" data-admin-chat-image-clear hidden aria-label="Remover imagem anexada" title="Remover imagem">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M18 6 6 18" />
                                        <path d="m6 6 12 12" />
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </article>
            `;
        };

        const renderSupport = (payload) => {
            if (!payload || !payload.tickets) {
                return;
            }

            if (payload.tickets.length === 0) {
                supportBlock.querySelector('[data-admin-support-shell]')?.remove();

                if (!supportBlock.querySelector('.admin-empty')) {
                    const empty = document.createElement('div');
                    empty.className = 'admin-empty';
                    empty.textContent = 'Nenhum chamado aberto ainda.';
                    supportBlock.appendChild(empty);
                }

                return;
            }

            const shell = ensureSupportShell();
            const list = shell.querySelector('.admin-support-list');
            const chatArea = shell.querySelector('.admin-support-chat-area');
            const activePanel = supportBlock.querySelector('[data-admin-chat-panel].is-active');
            const activeTarget = activePanel?.dataset.adminChatPanel;
            const nextActiveTarget = payload.tickets.some((ticketData) => ticketTarget(ticketData) === activeTarget)
                ? activeTarget
                : ticketTarget(payload.tickets[0]);
            const scrollState = new Map();

            supportBlock.querySelectorAll('[data-admin-chat-panel]').forEach((panel) => {
                const chatLog = panel.querySelector('.admin-chat-log');

                if (!chatLog) {
                    return;
                }

                scrollState.set(panel.dataset.adminChatPanel, {
                    top: chatLog.scrollTop,
                    distanceFromBottom: chatLog.scrollHeight - chatLog.scrollTop - chatLog.clientHeight
                });
            });

            list.innerHTML = payload.tickets
                .map((ticketData) => renderContact(ticketData, ticketTarget(ticketData) === nextActiveTarget))
                .join('');

            chatArea.innerHTML = payload.tickets
                .map((ticketData) => renderChatPanel(ticketData, ticketTarget(ticketData) === nextActiveTarget))
                .join('');

            supportBlock.querySelectorAll('[data-admin-chat-panel]').forEach((panel) => {
                const chatLog = panel.querySelector('.admin-chat-log');
                const state = scrollState.get(panel.dataset.adminChatPanel);

                if (!chatLog) {
                    return;
                }

                if (!state || state.distanceFromBottom < 80) {
                    chatLog.scrollTop = chatLog.scrollHeight;
                    return;
                }

                chatLog.scrollTop = state.top;
            });
        };

        const showComposerError = (form, message = '') => {
            let error = form.querySelector('[data-admin-composer-error]');

            if (!error) {
                error = document.createElement('small');
                error.setAttribute('data-admin-composer-error', '');
                form.appendChild(error);
            }

            error.textContent = message;
            error.hidden = !message;
        };

        const compressImageFile = (file) => new Promise((resolve) => {
            if (!file || (file.type && !file.type.startsWith('image/')) || file.type === 'image/gif' || file.type === 'image/svg+xml' || file.size <= compressedUploadLimit) {
                resolve(file);
                return;
            }

            const image = new Image();
            const objectUrl = URL.createObjectURL(file);

            image.onload = () => {
                URL.revokeObjectURL(objectUrl);

                const maxSide = 1600;
                const ratio = Math.min(1, maxSide / Math.max(image.width, image.height));
                const canvas = document.createElement('canvas');

                canvas.width = Math.max(1, Math.round(image.width * ratio));
                canvas.height = Math.max(1, Math.round(image.height * ratio));

                const context = canvas.getContext('2d');

                if (!context) {
                    resolve(file);
                    return;
                }

                context.drawImage(image, 0, 0, canvas.width, canvas.height);
                canvas.toBlob((blob) => {
                    if (!blob) {
                        resolve(file);
                        return;
                    }

                    resolve(new File([blob], file.name.replace(/\.[^.]+$/, '') + '.jpg', {
                        type: 'image/jpeg',
                        lastModified: Date.now()
                    }));
                }, 'image/jpeg', 0.82);
            };

            image.onerror = () => {
                URL.revokeObjectURL(objectUrl);
                resolve(file);
            };

            image.src = objectUrl;
        });

        const clearComposerImage = (form) => {
            const imageInput = form?.querySelector('[data-admin-chat-image]');
            const imageName = form?.querySelector('[data-admin-chat-image-name]');
            const clearImageButton = form?.querySelector('[data-admin-chat-image-clear]');

            if (imageInput) {
                imageInput.value = '';
            }

            if (imageName) {
                imageName.textContent = '';
                imageName.hidden = true;
            }

            if (clearImageButton) {
                clearImageButton.hidden = true;
            }
        };

        const validateComposerImage = (input) => {
            const form = input.closest('form');
            const file = input.files?.[0];

            if (!file) {
                clearComposerImage(form);
                showComposerError(form);
                return;
            }

            if (file.type && !file.type.startsWith('image/') && !imageExtensionPattern.test(file.name)) {
                clearComposerImage(form);
                showComposerError(form, 'Selecione um arquivo de imagem.');
                return;
            }

            if (file.size > 8192 * 1024) {
                clearComposerImage(form);
                showComposerError(form, 'A imagem pode ter no máximo 8 MB.');
                return;
            }

            const name = form?.querySelector('[data-admin-chat-image-name]');
            const clear = form?.querySelector('[data-admin-chat-image-clear]');

            if (name) {
                name.textContent = file.name;
                name.hidden = false;
            }

            if (clear) {
                clear.hidden = false;
            }

            showComposerError(form);
        };

        supportBlock.addEventListener('click', (event) => {
            const clearImageButton = event.target.closest('[data-admin-chat-image-clear]');

            if (clearImageButton && supportBlock.contains(clearImageButton)) {
                const form = clearImageButton.closest('form');

                clearComposerImage(form);
                showComposerError(form);
                return;
            }

            const contact = event.target.closest('[data-admin-chat-target]');

            if (contact && supportBlock.contains(contact)) {
                activateChat(contact.dataset.adminChatTarget);
            }
        });

        supportBlock.addEventListener('keydown', (event) => {
            const textarea = event.target.closest('.admin-chat-composer textarea[name="message"]');

            if (!textarea || event.key !== 'Enter' || event.shiftKey || event.ctrlKey || event.metaKey || event.altKey) {
                return;
            }

            event.preventDefault();
            textarea.closest('form')?.requestSubmit();
        });

        supportBlock.addEventListener('change', (event) => {
            const input = event.target.closest('[data-admin-chat-image]');

            if (!input) {
                return;
            }

            validateComposerImage(input);
        });

        supportBlock.addEventListener('submit', async (event) => {
            const form = event.target;

            if (!form.matches('.admin-chat-composer') && !form.matches('[data-admin-close-chat]')) {
                return;
            }

            event.preventDefault();

            const isComposer = form.matches('.admin-chat-composer');
            const textarea = form.querySelector('textarea[name="message"]');
            const imageInput = form.querySelector('[data-admin-chat-image]');
            const submitButton = form.querySelector('button[type="submit"]');

            if (isComposer) {
                const message = textarea.value.trim();

                if (!message && !imageInput?.files?.length) {
                    showComposerError(form, 'Digite uma mensagem ou adicione uma imagem.');
                    textarea.focus();
                    return;
                }

                showComposerError(form);
            }

            submitButton?.setAttribute('disabled', 'disabled');

            try {
                const formData = new FormData(form);

                if (isComposer && imageInput?.files?.[0]) {
                    const uploadFile = await compressImageFile(imageInput.files[0]);
                    formData.set('image', uploadFile, uploadFile.name);
                }

                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    let errorMessage = 'Não foi possível atualizar agora. Tente novamente em instantes.';

                    if (response.status === 422) {
                        const payload = await response.json().catch(() => null);
                        errorMessage = payload?.message
                            || Object.values(payload?.errors ?? {})?.flat()?.[0]
                            || 'Confira a mensagem e tente novamente.';
                    }

                    if (isComposer) {
                        showComposerError(form, errorMessage);
                    }

                    return;
                }

                const payload = await response.json();
                signature = payload.signature || signature;
                renderSupport(payload);

                if (isComposer && textarea) {
                    textarea.value = '';
                    clearComposerImage(form);
                }
            } catch (error) {
                if (isComposer) {
                    showComposerError(form, 'Falha de conexão. Sua mensagem não foi enviada ainda.');
                }
            } finally {
                submitButton?.removeAttribute('disabled');
            }
        });

        const checkSupport = async () => {
            try {
                const response = await fetch(supportBlock.dataset.adminSupportPulse, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    return;
                }

                const payload = await response.json();

                if (payload.signature && payload.signature !== signature) {
                    signature = payload.signature;
                    renderSupport(payload);
                }
            } catch (error) {
                // Admin page keeps working normally if polling is unavailable.
            }
        };

        setInterval(checkSupport, 2000);
    })();
</script>
@endpush
