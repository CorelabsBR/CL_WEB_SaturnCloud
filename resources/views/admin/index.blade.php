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
        </div>

        <div class="admin-tabs" role="tablist" aria-label="Seções do painel admin">
            <button type="button" class="is-active" data-admin-tab="suporte" role="tab" aria-selected="true">
                Suporte
            </button>
            <button type="button" data-admin-tab="produtos" role="tab" aria-selected="false">
                Produtos
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
                                <span class="admin-status {{ $statusClass[$ticket->status] ?? '' }}">{{ ucfirst($ticket->status) }}</span>
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
                                        <p>#{{ $ticket->id }} {{ $ticket->subject }} · {{ $ticket->category }} · Prioridade {{ $ticket->priority }}</p>
                                    </div>
                                    <span class="admin-status {{ $statusClass[$ticket->status] ?? '' }}">{{ ucfirst($ticket->status) }}</span>
                                </div>

                                <div class="admin-chat-log">
                                    @foreach($ticket->messages as $message)
                                        <div class="admin-chat-message {{ $message->author_type === 'admin' ? 'from-admin' : 'from-client' }}">
                                            <strong>{{ $message->author_type === 'admin' ? ($message->user?->nome ?? 'Admin') : ($ticket->user?->nome ?? 'Cliente') }}</strong>
                                            <p>{{ $message->body }}</p>
                                            <small>{{ optional($message->criado_em)->format('d/m/Y H:i') }}</small>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="admin-ticket-actions">
                                    <form method="POST" action="{{ route('admin.tickets.reply', $ticket) }}" class="admin-chat-composer">
                                        @csrf
                                        <div class="admin-chat-composer-row">
                                            <textarea name="message" rows="1" placeholder="Mensagem..." required></textarea>
                                            <button type="submit" aria-label="Enviar resposta" title="Enviar">
                                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M3 11.5 20 3l-4.8 18-3.6-7.2L3 11.5Zm8.6 2.3L20 3" />
                                                </svg>
                                            </button>
                                        </div>
                                        <select name="status" aria-label="Status após responder">
                                            <option value="respondido" @selected($ticket->status === 'respondido')>Marcar como respondido</option>
                                            <option value="aberto" @selected($ticket->status === 'aberto')>Manter aberto</option>
                                            <option value="fechado" @selected($ticket->status === 'fechado')>Fechar chamado</option>
                                        </select>
                                    </form>

                                    <form method="POST" action="{{ route('admin.tickets.update', $ticket) }}" class="admin-inline-form">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status">
                                            <option value="aberto" @selected($ticket->status === 'aberto')>Aberto</option>
                                            <option value="respondido" @selected($ticket->status === 'respondido')>Respondido</option>
                                            <option value="fechado" @selected($ticket->status === 'fechado')>Fechado</option>
                                        </select>
                                        <select name="priority">
                                            <option value="baixa" @selected($ticket->priority === 'baixa')>Baixa</option>
                                            <option value="normal" @selected($ticket->priority === 'normal')>Normal</option>
                                            <option value="alta" @selected($ticket->priority === 'alta')>Alta</option>
                                            <option value="urgente" @selected($ticket->priority === 'urgente')>Urgente</option>
                                        </select>
                                        <button class="btn btn-ghost btn-sm" type="submit">Atualizar</button>
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

            <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="admin-product-form admin-create-form">
                @csrf
                @include('admin.partials.product-fields', ['product' => null])
                <div class="admin-form-actions">
                    <button class="btn btn-primary" type="submit">Adicionar produto</button>
                </div>
            </form>

            <div class="admin-product-folders">
                @forelse($productFolders as $folder)
                    <details class="admin-product-folder" @if($loop->first) open @endif>
                        <summary>
                            <span class="admin-folder-main">
                                <span class="admin-folder-icon" aria-hidden="true">
                                    @if($folder['icon'])
                                        <img src="{{ $folder['icon'] }}" alt="">
                                    @else
                                        <span>{{ strtoupper(substr($folder['name'], 0, 1)) }}</span>
                                    @endif
                                </span>
                                <span>
                                    <strong>{{ $folder['name'] }}</strong>
                                    <small>{{ $folder['products']->count() }} produto{{ $folder['products']->count() === 1 ? '' : 's' }}</small>
                                </span>
                            </span>
                            <span class="admin-folder-chevron" aria-hidden="true">v</span>
                        </summary>

                        @if($folder['products']->isNotEmpty())
                            <div class="table-wrap admin-products-table">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Produto</th>
                                            <th>Categoria</th>
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
                                                <td>{{ $product->categoria?->nome ?? '-' }}</td>
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
                        @else
                            <div class="admin-empty">Nenhum produto nesta pasta.</div>
                        @endif
                    </details>
                @empty
                    <div class="admin-empty">Nenhum jogo cadastrado para organizar produtos.</div>
                @endforelse
            </div>
        </section>

        <section class="admin-block admin-tab-panel" id="jogos" data-admin-panel="jogos" hidden>
            <div class="admin-block-head">
                <div>
                    <h2>Fotos e jogos</h2>
                    <p>Atualize nome, slug, banner, ícone e visibilidade dos jogos.</p>
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

        document.querySelectorAll('[data-admin-chat-target]').forEach((contact) => {
            contact.addEventListener('click', () => {
                const target = contact.dataset.adminChatTarget;

                document.querySelectorAll('[data-admin-chat-target]').forEach((item) => {
                    item.classList.toggle('is-active', item === contact);
                });

                document.querySelectorAll('[data-admin-chat-panel]').forEach((panel) => {
                    const isActive = panel.dataset.adminChatPanel === target;
                    panel.classList.toggle('is-active', isActive);
                    panel.hidden = !isActive;
                });

                const chatLog = document.querySelector(`[data-admin-chat-panel="${target}"] .admin-chat-log`);

                if (chatLog) {
                    chatLog.scrollTop = chatLog.scrollHeight;
                }
            });
        });

        const initialTab = window.location.hash.replace('#', '');

        if (initialTab && document.querySelector(`[data-admin-panel="${initialTab}"]`)) {
            activateTab(initialTab);
        }

        if (!supportBlock) {
            return;
        }

        let signature = supportBlock.dataset.adminSupportSignature || '';

        document.querySelectorAll('.admin-chat-log').forEach((chatLog) => {
            chatLog.scrollTop = chatLog.scrollHeight;
        });

        const hasFocusedField = () => {
            const active = document.activeElement;
            return active && ['INPUT', 'TEXTAREA', 'SELECT'].includes(active.tagName);
        };

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

                if (payload.signature && payload.signature !== signature && !hasFocusedField()) {
                    window.location.reload();
                }
            } catch (error) {
                // Admin page keeps working normally if polling is unavailable.
            }
        };

        setInterval(checkSupport, 5000);
    })();
</script>
@endpush
