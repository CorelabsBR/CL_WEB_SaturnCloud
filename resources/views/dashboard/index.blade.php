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
    $lastPayment = data_get($stats ?? [], 'last_payment');
    $nextInvoice = data_get($stats ?? [], 'next_invoice');
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
                                    {{ $lastPayment ? 'R$ ' . number_format((float) data_get($lastPayment, 'amount', 0), 2, ',', '.') : 'Sem pagamento' }}
                                </strong>
                                <span>{{ $lastPayment ? 'Último gasto' : 'Último pagamento' }}</span>
                            </div>
                            <div>
                                <strong>99.9%</strong>
                                <span>Uptime global</span>
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

            <div class="dashboard-shell">

                <div class="dashboard-topbar">
                    <div class="dashboard-tabs-card">
                        <nav class="dashboard-tab-nav" aria-label="Menu do Painel">
                            <a href="#dashboard" class="active" aria-current="page" data-dashboard-link>
                                <span class="dashboard-tab-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M4 13h6v7H4zm10-9h6v16h-6zM4 4h6v6H4z" />
                                    </svg>
                                </span>
                                <span>
                                    <strong>Dashboard</strong>
                                    <small>Resumo da conta</small>
                                </span>
                            </a>
                            <a href="#servicos" data-dashboard-link>
                                <span class="dashboard-tab-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M4 4h16v5H4zm0 7h16v9H4zm3 3v3h3v-3z" />
                                    </svg>
                                </span>
                                <span>
                                    <strong>Serviços</strong>
                                    <small>Servidores e planos</small>
                                </span>
                            </a>
                            <a href="#faturas" data-dashboard-link>
                                <span class="dashboard-tab-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M6 2h12v20l-3-2-3 2-3-2-3 2zm3 6h6V6H9zm0 4h6v-2H9zm0 4h4v-2H9z" />
                                    </svg>
                                </span>
                                <span>
                                    <strong>Faturas</strong>
                                    <small>Pagamentos</small>
                                </span>
                            </a>
                        </nav>
                    </div>
                </div>

                <div class="dashboard-main" data-dashboard-tabs>

                    <div class="dashboard-view is-active" id="dashboard" data-dashboard-view="dashboard">

                    <div class="dashboard-cards">

                        <div class="dash-card">
                            <div class="dash-card-top">
                                <span class="dash-card-title">Serviços ativos</span>
                                <span class="dash-card-icon dash-card-service-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M4 4h16v5H4zm0 7h16v9H4zm3 3v3h3v-3z" />
                                    </svg>
                                </span>
                            </div>
                            <strong>{{ $activeServices }}</strong>
                            <small>{{ $activeServices === 1 ? '1 ambiente online agora' : $activeServices . ' ambientes online agora' }}</small>
                        </div>

                        <div class="dash-card">
                            <div class="dash-card-top">
                                <span class="dash-card-title">Faturas pendentes</span>
                                <span class="dash-card-icon dash-card-invoice-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M3 5h18v14H3zm2 3v2h14V8zm0 5v3h5v-3z" />
                                    </svg>
                                </span>
                            </div>
                            <strong>{{ $pendingInvoices }}</strong>
                            <small>{{ $pendingInvoices > 0 ? 'R$ ' . number_format((float) $pendingAmount, 2, ',', '.') . ' em aberto' : 'Tudo em dia no financeiro' }}</small>
                        </div>

                        <div class="dash-card dash-card-account">
                            <div class="dash-card-top">
                                <span class="dash-card-title">Membro desde</span>
                                <span class="dash-card-icon dash-card-date-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M7 2h2v3h6V2h2v3h3v17H4V5h3zm11 8H6v10h12zM6 7v1h12V7z" />
                                    </svg>
                                </span>
                            </div>
                            <strong>
                                {{ $accountCreatedAt }}
                            </strong>
                            <small>{{ $accountAgeDays > 0 ? $accountAgeDays . ' dias de conta ativa' : 'Conta ativada recentemente' }}</small>
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
                                    <span data-service-summary class="services-summary" hidden></span>
                                    <label class="services-search">
                                        <span>Buscar serviço</span>
                                        <input type="search" placeholder="Buscar" data-service-search>
                                    </label>
                                </div>

                                <div class="services-table-wrap">
                                    <table class="services-table">
                                        <thead>
                                            <tr>
                                                <th>Serviço</th>
                                                <th>Preço</th>
                                                <th>Vencimento</th>
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
                                                        'cancelado' => 'status-suspenso',
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
                                                            <span>{{ data_get($service, 'plan', 'Plano sob demanda') }}</span>
                                                            @if (data_get($service, 'ip') && data_get($service, 'ip') !== 'Aguardando provisionamento')
                                                                <span>{{ data_get($service, 'ip') }}</span>
                                                            @endif
                                                        </div>

                                                        <div class="table-service-actions">
                                                            @if ($panel)
                                                                <a
                                                                    href="{{ data_get($panel, 'url') }}"
                                                                    class="table-panel-link"
                                                                    target="_blank"
                                                                    rel="noopener noreferrer"
                                                                >
                                                                    Painel
                                                                </a>
                                                            @endif
                                                            @if (data_get($service, 'configuration_url'))
                                                                <a
                                                                    href="{{ data_get($service, 'configuration_url') }}"
                                                                    class="table-panel-link"
                                                                >
                                                                    Configurar
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>{{ data_get($service, 'price', 'Sob consulta') }}</td>
                                                    <td>{{ data_get($service, 'expires_at', 'Sem renovação definida') }}</td>
                                                    <td>
                                                        <div class="table-status-stack">
                                                            <span class="{{ $statusClass }}">{{ ucfirst($status) }}</span>
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
                                        <button type="button" data-service-prev>Anterior</button>
                                        <button type="button" data-service-next>Próximo</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="dashboard-panel dashboard-view" id="faturas" data-dashboard-view="faturas" hidden>
                            <div class="panel-head">
                                <div>
                                    <h2>Faturas</h2>
                                    <p>Status financeiro dos pedidos e renovações mensais.</p>
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
                                        <p>Quando houver compra ou renovação mensal, a fatura aparecerá aqui.</p>
                                    </div>
                                @endforelse
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

        const showView = (viewName, updateUrl = true, processInvoices = true) => {
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

            if (processInvoices && target.dataset.dashboardView === 'faturas') {
                processRenewals(true);
            }
        };

        document.addEventListener('click', (event) => {
            const link = event.target.closest('[data-dashboard-link]');

            if (!link) {
                return;
            }

            event.preventDefault();
            showView(link.getAttribute('href').replace('#', ''));
        });

        const initServiceTables = () => {
            document.querySelectorAll('[data-service-table]').forEach((table) => {
                if (table.dataset.tableReady === '1') {
                    return;
                }

                table.dataset.tableReady = '1';

                const search = table.querySelector('[data-service-search]');
                const summary = table.querySelector('[data-service-summary]');
                const rows = Array.from(table.querySelectorAll('[data-service-row]'));
                const emptyRow = table.querySelector('[data-service-empty-row]');
                const previous = table.querySelector('[data-service-prev]');
                const next = table.querySelector('[data-service-next]');
                const perPage = 3;
                let currentPage = 1;

                if (!search || !previous || !next || !rows.length) {
                    return;
                }

                const updateRows = () => {
                    const query = search.value.trim().toLowerCase();
                    const matchingRows = rows.filter((row) => row.textContent.toLowerCase().includes(query));
                    const total = matchingRows.length;
                    const totalPages = Math.max(1, Math.ceil(total / perPage));

                    currentPage = Math.min(currentPage, totalPages);

                    const start = total ? (currentPage - 1) * perPage : 0;
                    const end = Math.min(start + perPage, total);
                    const visibleRows = new Set(matchingRows.slice(start, end));

                    rows.forEach((row) => {
                        row.hidden = !visibleRows.has(row);
                    });

                    if (summary) {
                        summary.textContent = total
                            ? `Mostrando ${start + 1} a ${end} de ${total} registros`
                            : `Mostrando 0 a 0 de ${rows.length} registros`;
                    }

                    previous.disabled = currentPage <= 1 || total === 0;
                    next.disabled = currentPage >= totalPages || total === 0;

                    if (emptyRow) {
                        emptyRow.hidden = total > 0;
                    }
                };

                search.addEventListener('input', () => {
                    currentPage = 1;
                    updateRows();
                });

                previous.addEventListener('click', () => {
                    currentPage = Math.max(1, currentPage - 1);
                    updateRows();
                });

                next.addEventListener('click', () => {
                    currentPage += 1;
                    updateRows();
                });

                updateRows();
            });
        };

        initServiceTables();

        let timers = Array.from(document.querySelectorAll('[data-invoice-timer]'));
        let renewalCheckInProgress = false;
        let fragmentRefreshInProgress = false;

        const collectTimers = () => {
            timers = Array.from(document.querySelectorAll('[data-invoice-timer]'));
        };

        const refreshDashboardFragments = async () => {
            if (fragmentRefreshInProgress) {
                return;
            }

            fragmentRefreshInProgress = true;

            try {
                const response = await fetch(window.location.pathname, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    cache: 'no-store',
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    return;
                }

                const html = await response.text();
                const nextDocument = new DOMParser().parseFromString(html, 'text/html');
                const selectors = [
                    '.hero-status-grid',
                    '.dashboard-cards',
                    '[data-service-table]',
                    '#faturas .service-list',
                ];

                selectors.forEach((selector) => {
                    const current = document.querySelector(selector);
                    const next = nextDocument.querySelector(selector);

                    if (current && next) {
                        current.innerHTML = next.innerHTML;
                        current.removeAttribute('data-table-ready');
                    }
                });

                initServiceTables();
                collectTimers();
                updateTimers();
                showView(window.location.hash.replace('#', '') || 'dashboard', false, false);
            } catch (error) {
                console.warn('Falha ao atualizar painel em background.', error);
            } finally {
                fragmentRefreshInProgress = false;
            }
        };

        const currentViewName = () => {
            return window.location.hash.replace('#', '') || 'dashboard';
        };

        const processRenewals = async (refreshWhenUnchanged = false) => {
            if (renewalCheckInProgress) {
                return;
            }

            renewalCheckInProgress = true;

            try {
                const response = await fetch('{{ route('dashboard.renewals.process') }}', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    cache: 'no-store',
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    return;
                }

                const result = await response.json();

                if (result.changed || refreshWhenUnchanged) {
                    refreshDashboardFragments();
                }
            } catch (error) {
                console.warn('Falha ao processar renovações em background.', error);
            } finally {
                renewalCheckInProgress = false;
            }
        };

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
                    processRenewals();
                    return;
                }

                timer.textContent = days > 0
                    ? `${days}d ${hours}h ${minutes}m`
                    : `${hours}h ${minutes}m ${seconds}s`;
            });
        };

        showView(window.location.hash.replace('#', '') || 'dashboard', false);
        updateTimers();
        setInterval(updateTimers, 1000);
        setInterval(() => processRenewals(currentViewName() === 'faturas'), 10000);
    })();
</script>
@endpush
