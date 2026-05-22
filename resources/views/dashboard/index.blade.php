@extends('layouts.app')

@section('title', 'Área do Cliente')

@section('content')

@php
    $user = auth()->user();
    $userName = $user?->nome ?? 'Cliente';
    $firstName = explode(' ', trim($userName))[0] ?: 'Cliente';
    $userInitial = strtoupper(substr($userName, 0, 1));

    $services = collect($services ?? []);
    $invoices = collect($invoices ?? []);
    $activities = collect($activities ?? []);

    $activeServices = data_get($stats ?? [], 'active_services', $services->where('status', 'ativo')->count());
    $pendingInvoices = data_get($stats ?? [], 'pending_invoices', $invoices->where('status', 'pendente')->count());
    $pendingAmount = data_get($stats ?? [], 'pending_amount', $invoices->where('status', 'pendente')->sum('amount'));
    $paidAmount = data_get($stats ?? [], 'paid_amount', $invoices->where('status', 'pago')->sum('amount'));
    $lastPayment = data_get($stats ?? [], 'last_payment') ?: $invoices->firstWhere('status', 'pago');
    $nextInvoice = data_get($stats ?? [], 'next_invoice');
    $profileCompletion = data_get($profile ?? [], 'completion', 0);
    $missingProfile = collect(data_get($profile ?? [], 'missing', []));
    $accountCreatedAt = data_get($stats ?? [], 'account_created_at', $user?->criado_em?->format('d/m/Y') ?? '--/--/----');
    $accountAgeDays = (int) floor((float) data_get($stats ?? [], 'account_age_days', 0));
@endphp

<main class="client-page">
    <section class="client-hero" aria-labelledby="hero-welcome">
        <div class="container">
            <div class="client-hero-grid">

                <div class="client-hero-left">
                    <h1 id="hero-welcome">
                        Bem-vindo, {{ $firstName }}
                    </h1>

                    <p>
                        Monitore recursos, gerencie servidores e acompanhe faturas em um painel limpo para operação diária.
                    </p>

                    <div class="client-actions">
                        <a href="/games" class="btn btn-primary">
                            Contratar serviço
                        </a>
                    </div>
                </div>

                <div class="client-hero-right">
                    <div class="hero-status-card">
                        <div class="hero-status-head">
                            <span>Status da Conta</span>
                            <div class="status-online" aria-label="Status: Ativa">
                                <span class="status-indicator"></span>
                                Ativa
                            </div>
                        </div>

                        <div class="hero-status-grid">
                            <div>
                                <strong>{{ $activeServices }}</strong>
                                <span>Serviços ativos</span>
                            </div>
                            <div>
                                <strong>{{ $pendingInvoices }}</strong>
                                <span>Faturas pendentes</span>
                            </div>
                            <div>
                                <strong>
                                    {{ $lastPayment ? 'R$ ' . number_format((float) data_get($lastPayment, 'amount', 0), 2, ',', '.') : 'R$ 0,00' }}
                                </strong>
                                <span>Último pagamento</span>
                            </div>
                            <div>
                                <strong>99.9%</strong>
                                <span>Uptime global</span>
                            </div>
                            <div>
                                <strong>{{ $accountCreatedAt }}</strong>
                                <span>Conta criada</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="client-content">
        <div class="container">
            @if (session('success'))
                <div class="flash flash-success" role="status">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="flash flash-error" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <div class="dashboard-grid">

                <aside class="dashboard-sidebar">
                    <div class="sidebar-card">
                        <div class="sidebar-user">
                            <div class="sidebar-avatar" aria-hidden="true">
                                {{ $userInitial }}
                            </div>
                            <div>
                                <strong>{{ $userName }}</strong>
                                <span>{{ $user?->email ?? 'cliente@vortexhost.com.br' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="sidebar-card">
                        <span class="sidebar-title">Navegação</span>
                        <nav class="sidebar-nav" aria-label="Menu do Painel">
                            <a href="#dashboard" class="active" aria-current="page" data-dashboard-link>
                                Dashboard
                            </a>
                            <a href="#servicos" data-dashboard-link>
                                Meus Serviços
                            </a>
                            <a href="#faturas" data-dashboard-link>
                                Faturas
                            </a>
                            <a href="#perfil" data-dashboard-link>
                                Perfil
                            </a>
                        </nav>
                    </div>
                </aside>

                <div class="dashboard-main" data-dashboard-tabs>

                    <div class="dashboard-view is-active" id="dashboard" data-dashboard-view="dashboard">

                    <div class="dashboard-cards">

                        <div class="dash-card">
                            <div class="dash-card-top">
                                <span class="dash-card-title">Serviços ativos</span>
                                <span class="dash-card-icon" role="img" aria-label="Ícone de raio">⚡</span>
                            </div>
                            <strong>{{ $activeServices }}</strong>
                            <small>{{ $activeServices === 1 ? '1 ambiente online agora' : $activeServices . ' ambientes online agora' }}</small>
                        </div>

                        <div class="dash-card">
                            <div class="dash-card-top">
                                <span class="dash-card-title">Faturas pendentes</span>
                                <span class="dash-card-icon" role="img" aria-label="Ícone de cartão de crédito">💳</span>
                            </div>
                            <strong>{{ $pendingInvoices }}</strong>
                            <small>{{ $pendingInvoices > 0 ? 'R$ ' . number_format((float) $pendingAmount, 2, ',', '.') . ' em aberto' : 'Tudo em dia no financeiro' }}</small>
                        </div>

                        <div class="dash-card">
                            <div class="dash-card-top">
                                <span class="dash-card-title">Conta criada</span>
                                <span class="dash-card-icon" role="img" aria-label="Ícone de calendário">📅</span>
                            </div>
                            <strong>
                                {{ $accountCreatedAt }}
                            </strong>
                            <small>{{ $accountAgeDays > 0 ? $accountAgeDays . ' dias com a VortexHost' : 'Cliente VortexHost' }}</small>
                        </div>

                    </div>

                    <div class="dashboard-panels">

                        <div class="dashboard-panel">
                            <div class="panel-head">
                                <div>
                                    <h2>Atividade recente</h2>
                                    <p>Últimos eventos da sua conta.</p>
                                </div>
                            </div>

                            <div class="activity-list">
                                @forelse ($activities as $activity)
                                    <div class="activity-item">
                                        <div class="activity-dot" aria-hidden="true"></div>
                                        <div>
                                            <strong>{{ data_get($activity, 'title', 'Evento da conta') }}</strong>
                                            <span>{{ data_get($activity, 'description', 'Atualização registrada no painel.') }}</span>
                                        </div>
                                        <small>{{ data_get($activity, 'time', 'Agora') }}</small>
                                    </div>
                                @empty
                                    <div class="activity-item">
                                        <div class="activity-dot" aria-hidden="true"></div>
                                        <div>
                                            <strong>Conta criada</strong>
                                            <span>Sua conta foi criada com sucesso.</span>
                                        </div>
                                        <small>Agora</small>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                    </div>

                    </div>

                    <div class="dashboard-panels">

                        <div class="dashboard-panel dashboard-view" id="servicos" data-dashboard-view="servicos" hidden>
                            <div class="panel-head">
                                <div>
                                    <h2>Meus serviços</h2>
                                    <p>Servidores de jogos, VPS e hospedagens vinculados à sua conta.</p>
                                </div>
                                <a href="/games" class="btn btn-sm btn-primary">
                                    Novo serviço
                                </a>
                            </div>

                            <div class="services-table-shell" data-service-table>
                                <div class="services-table-toolbar">
                                    <span data-service-summary>
                                        Mostrando {{ $services->count() ? 1 : 0 }} para {{ $services->count() }} de {{ $services->count() }} registros
                                    </span>
                                    <label class="services-search">
                                        <span>Buscar serviço</span>
                                        <input type="search" placeholder="Buscar" data-service-search>
                                    </label>
                                </div>

                                <div class="services-table-wrap">
                                    <table class="services-table">
                                        <thead>
                                            <tr>
                                                <th>Produto/Serviço</th>
                                                <th>Preços</th>
                                                <th>Próximo Vencimento</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($services as $service)
                                                @php
                                                    $status = data_get($service, 'status', 'ativo');
                                                    $statusClass = match ($status) {
                                                        'pago' => 'status-pago',
                                                        'pendente' => 'status-pendente',
                                                        'suspenso' => 'status-suspenso',
                                                        'offline' => 'status-offline',
                                                        default => 'status-ativo',
                                                    };

                                                    $panel = data_get($service, 'panel');
                                                    $invoice = data_get($service, 'invoice', [
                                                        'label' => 'Fatura pendente',
                                                        'class' => 'status-pendente',
                                                    ]);
                                                @endphp

                                                <tr data-service-row>
                                                    <td>
                                                        <div class="table-service-name">{{ data_get($service, 'name', 'Servidor Vortex') }}</div>
                                                        <div class="table-service-meta">
                                                            <span>{{ data_get($service, 'display_id', 'SRV-000000') }}</span>
                                                            <span>{{ data_get($service, 'plan', 'Plano sob demanda') }}</span>
                                                            <span>{{ data_get($service, 'ip', 'Aguardando provisionamento') }}</span>
                                                        </div>
                                                        @if ($panel)
                                                            <a
                                                                href="{{ data_get($panel, 'url') }}"
                                                                class="table-panel-link"
                                                                target="_blank"
                                                                rel="noopener noreferrer"
                                                            >
                                                                {{ data_get($panel, 'label', 'Abrir painel') }}
                                                            </a>
                                                        @endif
                                                    </td>
                                                    <td>{{ data_get($service, 'price', 'Sob consulta') }}</td>
                                                    <td>{{ data_get($service, 'expires_at', 'Sem renovação definida') }}</td>
                                                    <td>
                                                        <div class="table-status-stack">
                                                            <span class="{{ $statusClass }}">{{ ucfirst($status) }}</span>
                                                            <span class="{{ data_get($invoice, 'class', 'status-pendente') }}">
                                                                {{ data_get($invoice, 'label', 'Fatura pendente') }}
                                                            </span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="table-empty">Nenhum registro encontrado</td>
                                                </tr>
                                            @endforelse
                                            @if ($services->isNotEmpty())
                                                <tr data-service-empty-row hidden>
                                                    <td colspan="4" class="table-empty">Nenhum registro encontrado</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>

                                <div class="services-table-footer">
                                    <div class="table-pagination" aria-label="Paginação dos serviços">
                                        <button type="button" disabled>Anterior</button>
                                        <button type="button" disabled>Próximo</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="dashboard-panel dashboard-view" id="faturas" data-dashboard-view="faturas" hidden>
                            <div class="panel-head">
                                <div>
                                    <h2>Faturas</h2>
                                    <p>Status financeiro dos pedidos e renovações de VPS.</p>
                                </div>
                                <a href="/checkout" class="btn btn-sm btn-primary">
                                    Ver checkout
                                </a>
                            </div>

                            <div class="service-list">
                                @forelse ($invoices as $invoice)
                                    @php
                                        $invoiceStatus = data_get($invoice, 'status', 'pendente');
                                        $isRenewalInvoice = data_get($invoice, 'billing_type') === 'renewal';
                                        $invoiceStatusClass = match ($invoiceStatus) {
                                            'pago' => 'status-pago',
                                            'suspenso', 'cancelado', 'vencido' => 'status-suspenso',
                                            default => 'status-pendente',
                                        };
                                    @endphp

                                    <article class="service-row">
                                        <div class="service-main">
                                            <div class="service-title">
                                                <span>Fatura {{ data_get($invoice, 'display_id', 'VTX-000000') }}</span>
                                                <span class="{{ $invoiceStatusClass }}">{{ ucfirst($invoiceStatus) }}</span>
                                            </div>
                                            <div class="service-meta">
                                                <span>{{ data_get($invoice, 'description', 'Serviços VortexHost') }}</span>
                                                <span>{{ $isRenewalInvoice ? 'Renovação de mensalidade' : 'Compra inicial' }}</span>
                                                <span>Emitida em {{ data_get($invoice, 'created_at', 'Sem data') }}</span>
                                            </div>
                                        </div>

                                        <div class="service-location">
                                            <strong>Valor</strong>
                                            R$ {{ number_format((float) data_get($invoice, 'amount', 0), 2, ',', '.') }}
                                        </div>

                                        <div class="service-usage">
                                            <strong>
                                                @if($isRenewalInvoice)
                                                    {{ $invoiceStatus === 'pendente' ? 'Expira em' : 'Expirou em' }}
                                                @else
                                                    Compra em
                                                @endif
                                            </strong>
                                            {{ data_get($invoice, 'due_at', 'Sem vencimento') }}
                                            @if ($isRenewalInvoice && $invoiceStatus === 'pendente' && data_get($invoice, 'due_at_iso'))
                                                <span class="invoice-timer" data-invoice-timer="{{ data_get($invoice, 'due_at_iso') }}">
                                                    calculando...
                                                </span>
                                            @endif
                                        </div>

                                        <div class="service-actions" aria-label="Ações da fatura">
                                            @if ($invoiceStatus === 'pendente')
                                                <form method="POST" action="{{ route('checkout.order.pay', data_get($invoice, 'id')) }}">
                                                    @csrf
                                                    <button type="submit">Pagar</button>
                                                </form>
                                            @endif
                                            <a href="#faturas" data-dashboard-link>Detalhes</a>
                                        </div>
                                    </article>
                                @empty
                                    <div class="empty-state">
                                        <div class="empty-icon" role="img" aria-label="Ícone de cartão">💳</div>
                                        <h3>Nenhuma fatura pendente</h3>
                                        <p>Quando houver compra ou renovação de VPS, a fatura aparecerá aqui.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="dashboard-panel dashboard-view" id="perfil" data-dashboard-view="perfil" hidden>
                            <div class="panel-head">
                                <div>
                                    <h2>Perfil</h2>
                                    <p>Dados principais da conta do cliente.</p>
                                </div>
                            </div>

                            <div class="service-list">
                                <article class="service-row">
                                    <div class="service-main">
                                        <div class="service-title">
                                            <span>{{ $userName }}</span>
                                            <span class="status-ativo">Ativo</span>
                                        </div>
                                        <div class="service-meta">
                                            <span>{{ $user?->email ?? 'E-mail não informado' }}</span>
                                            <span>Cliente desde {{ $accountCreatedAt }}</span>
                                        </div>
                                    </div>

                                    <div class="service-location">
                                        <strong>Documento</strong>
                                        {{ data_get($profile ?? [], 'document', 'Não informado') }}
                                    </div>

                                    <div class="service-usage">
                                        <strong>Cadastro</strong>
                                        {{ $profileCompletion }}% completo
                                    </div>

                                    <div class="service-actions" aria-label="Ações de perfil">
                                        <a href="{{ route('perfil.edit') }}">Editar</a>
                                    </div>
                                </article>

                                <article class="service-row">
                                    <div class="service-main">
                                        <div class="service-title">
                                            <span>Dados de contato</span>
                                            <span class="{{ data_get($profile ?? [], 'email_verified') ? 'status-pago' : 'status-pendente' }}">
                                                {{ data_get($profile ?? [], 'email_verified') ? 'Verificado' : 'Pendente' }}
                                            </span>
                                        </div>
                                        <div class="service-meta">
                                            <span>{{ $user?->email ?? 'E-mail não informado' }}</span>
                                            <span>{{ data_get($profile ?? [], 'phone', 'Não informado') }}</span>
                                        </div>
                                    </div>

                                    <div class="service-location">
                                        <strong>Endereço</strong>
                                        {{ data_get($profile ?? [], 'address', 'Endereço não informado') }}
                                    </div>

                                    <div class="service-usage">
                                        <strong>Faltando</strong>
                                        {{ $missingProfile->isEmpty() ? 'Nada pendente' : $missingProfile->take(3)->join(', ') }}
                                    </div>

                                    <div class="service-actions" aria-label="Completar perfil">
                                        <a href="{{ route('perfil.edit') }}">Atualizar</a>
                                    </div>
                                </article>
                            </div>
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </section>
</main>

@endsection

@push('scripts')
<script>
    (() => {
        const tabs = document.querySelector('[data-dashboard-tabs]');

        if (!tabs) {
            return;
        }

        const links = document.querySelectorAll('[data-dashboard-link]');
        const views = tabs.querySelectorAll('[data-dashboard-view]');

        if (!links.length || !views.length) {
            return;
        }

        const showView = (viewName, updateUrl = true) => {
            const target = tabs.querySelector(`[data-dashboard-view="${viewName}"]`)
                || tabs.querySelector('[data-dashboard-view="dashboard"]');

            views.forEach((view) => {
                const isActive = view === target;
                view.hidden = !isActive;
                view.classList.toggle('is-active', isActive);
            });

            links.forEach((link) => {
                const isActive = link.getAttribute('href') === `#${target.dataset.dashboardView}`;
                link.classList.toggle('active', isActive);

                if (isActive) {
                    link.setAttribute('aria-current', 'page');
                } else {
                    link.removeAttribute('aria-current');
                }
            });

            if (updateUrl) {
                history.replaceState(null, '', `#${target.dataset.dashboardView}`);
            }
        };

        links.forEach((link) => {
            link.addEventListener('click', (event) => {
                event.preventDefault();
                showView(link.getAttribute('href').replace('#', ''));
            });
        });

        showView(window.location.hash.replace('#', '') || 'dashboard', false);

        document.querySelectorAll('[data-service-table]').forEach((table) => {
            const search = table.querySelector('[data-service-search]');
            const summary = table.querySelector('[data-service-summary]');
            const rows = Array.from(table.querySelectorAll('[data-service-row]'));
            const emptyRow = table.querySelector('[data-service-empty-row]');

            if (!search || !summary || !rows.length) {
                return;
            }

            const updateRows = () => {
                const query = search.value.trim().toLowerCase();
                let visible = 0;

                rows.forEach((row) => {
                    const matches = row.textContent.toLowerCase().includes(query);

                    row.hidden = !matches;
                    visible += matches ? 1 : 0;
                });

                summary.textContent = `Mostrando ${visible ? 1 : 0} para ${visible} de ${rows.length} registros`;

                if (emptyRow) {
                    emptyRow.hidden = visible > 0;
                }
            };

            search.addEventListener('input', updateRows);
            updateRows();
        });

        const timers = document.querySelectorAll('[data-invoice-timer]');

        const updateTimers = () => {
            timers.forEach((timer) => {
                const dueAt = new Date(timer.dataset.invoiceTimer);
                const remaining = Math.max(0, dueAt.getTime() - Date.now());
                const days = Math.floor(remaining / 86400000);
                const hours = Math.floor((remaining % 86400000) / 3600000);
                const minutes = Math.floor((remaining % 3600000) / 60000);
                const seconds = Math.floor((remaining % 60000) / 1000);

                if (remaining <= 0) {
                    timer.textContent = 'expirada';
                    return;
                }

                timer.textContent = days > 0
                    ? `${days}d ${hours}h ${minutes}m`
                    : `${hours}h ${minutes}m ${seconds}s`;
            });
        };

        updateTimers();
        setInterval(updateTimers, 1000);
    })();
</script>
@endpush
