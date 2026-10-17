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

<div class="client-page">
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
                        <a href="/web" class="btn btn-primary">
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
                                <strong>{{ $user->email_verified_at ? 'Verificado' : 'Pendente' }}</strong>
                                <span>Status do e-mail</span>
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
                                <a href="/web" class="btn btn-sm btn-primary">
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
                                                            @if (data_get($service, 'provision_identifier'))
                                                                <span class="table-service-provision-id">
                                                                    {{ data_get($service, 'provision_identifier') }}
                                                                </span>
                                                            @elseif (data_get($service, 'ip') && data_get($service, 'ip') !== 'Aguardando provisionamento')
                                                                <span>{{ data_get($service, 'ip') }}</span>
                                                            @endif
                                                            @if (data_get($service, 'is_cancellation_scheduled'))
                                                                <span class="table-service-cancelled">Cancela no vencimento</span>
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
                                                            @if (data_get($service, 'can_schedule_cancellation'))
                                                                @php
                                                                    $cancelIdentifier = data_get($service, 'cancellation_identifier');
                                                                @endphp
                                                                <button
                                                                    type="button"
                                                                    class="table-panel-link table-panel-link-danger"
                                                                    data-open-service-cancel
                                                                    data-cancel-url="{{ route('services.cancel', data_get($service, 'id')) }}"
                                                                    data-cancel-identifier="{{ $cancelIdentifier }}"
                                                                    data-cancel-name="{{ data_get($service, 'name', 'Servidor Vortex') }}"
                                                                >
                                                                    Cancelar plano
                                                                </button>
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
                                    <p>Cobranças de renovação disponíveis para pagamento via Pix.</p>
                                </div>
                            </div>

                            <div class="invoice-list">
                                @forelse ($invoices as $invoice)
                                    @php
                                        $invoiceStatus = data_get($invoice, 'status', 'pendente');
                                        $invoiceStatusClass = match ($invoiceStatus) {
                                            'pago' => 'status-pago',
                                            'suspenso', 'cancelado', 'vencido' => 'status-suspenso',
                                            default => 'status-pendente',
                                        };
                                    @endphp

                                    <article class="invoice-slip">
                                        <header class="invoice-slip-head">
                                            <div class="invoice-slip-brand">
                                                <span class="invoice-slip-logo">V</span>
                                                <div>
                                                    <strong>VortexHost</strong>
                                                    <small>Fatura digital de renovação</small>
                                                </div>
                                            </div>
                                            <div class="invoice-slip-status">
                                                <span class="{{ $invoiceStatusClass }}">{{ ucfirst($invoiceStatus) }}</span>
                                                <small>{{ data_get($invoice, 'renewal_label', 'Renovação') }}</small>
                                            </div>
                                        </header>

                                        <div class="invoice-slip-number">
                                            <span>Número da fatura</span>
                                            <strong>{{ data_get($invoice, 'display_id', 'VTX-000000') }}</strong>
                                        </div>

                                        <div class="invoice-slip-fields">
                                            <div>
                                                <span>Beneficiário</span>
                                                <strong>VortexHost</strong>
                                            </div>
                                            <div>
                                                <span>Cliente</span>
                                                <strong>{{ $userName }}</strong>
                                            </div>
                                            <div>
                                                <span>Serviço</span>
                                                <strong>{{ data_get($invoice, 'description', 'Serviços VortexHost') }}</strong>
                                            </div>
                                            <div>
                                                <span>Emissão</span>
                                                <strong>{{ data_get($invoice, 'created_at', 'Sem data') }}</strong>
                                            </div>
                                            <div>
                                                <span>Vencimento</span>
                                                <strong>{{ data_get($invoice, 'due_at', 'Sem vencimento') }}</strong>
                                            </div>
                                            <div class="invoice-slip-amount">
                                                <span>Valor da cobrança</span>
                                                <strong>R$ {{ number_format((float) data_get($invoice, 'amount', 0), 2, ',', '.') }}</strong>
                                            </div>
                                        </div>

                                        <div
                                            class="invoice-pix"
                                            data-pix-charge
                                            data-pix-url="{{ route('checkout.order.pix', data_get($invoice, 'id')) }}"
                                            data-status-url="{{ route('checkout.order.status', data_get($invoice, 'id')) }}"
                                            data-csrf="{{ csrf_token() }}"
                                        >
                                            <div class="invoice-pix-qr">
                                                <div class="invoice-pix-loader" data-pix-loader>
                                                    Gerando QR Code Pix...
                                                </div>
                                                <img data-pix-image alt="QR Code Pix para pagamento da fatura" hidden>
                                            </div>
                                            <div class="invoice-pix-content">
                                                <span class="invoice-pix-label">Pague pelo aplicativo do seu banco</span>
                                                <strong>Escaneie o QR Code ou use o Pix Copia e Cola</strong>
                                                <textarea data-pix-code readonly aria-label="Código Pix Copia e Cola" hidden></textarea>
                                                <div class="invoice-pix-actions">
                                                    <button type="button" data-pix-copy hidden>Copiar código Pix</button>
                                                    <a data-pix-open target="_blank" rel="noopener noreferrer" hidden>
                                                        Abrir cobrança
                                                    </a>
                                                </div>
                                                <small data-pix-status>A cobrança é gerada com segurança pelo Mercado Pago.</small>
                                            </div>
                                        </div>

                                        <footer class="invoice-slip-footer">
                                            <div class="invoice-slip-payment">
                                                @if ($invoiceStatus === 'pendente' && data_get($invoice, 'due_at_iso'))
                                                    <span class="invoice-timer" data-invoice-timer="{{ data_get($invoice, 'due_at_iso') }}">
                                                        calculando...
                                                    </span>
                                                @endif
                                                <small>Pagamento processado com segurança pelo Mercado Pago</small>
                                            </div>
                                        </footer>
                                    </article>
                                @empty
                                    <div class="empty-state">
                                        <div class="empty-icon" role="img" aria-label="Ícone de fatura">🧾</div>
                                        <h3>Nenhuma fatura pendente</h3>
                                        <p>Quando chegar a data de renovação mensal ou anual, a cobrança aparecerá aqui.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </section>
</div>

<div class="cancel-modal" data-cancel-modal hidden>
    <div class="cancel-modal-backdrop" data-cancel-modal-close></div>
    <div
        class="cancel-modal-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="cancel-modal-title"
    >
        <button type="button" class="cancel-modal-close" aria-label="Fechar" data-cancel-modal-close>
            ×
        </button>

        <h2 id="cancel-modal-title">Cancelar plano</h2>
        <p>
            O plano continuará ativo até o vencimento e não será cobrado no próximo mês.
        </p>

        <div class="cancel-modal-service">
            <span>Serviço</span>
            <strong data-cancel-modal-name>Servidor Vortex</strong>
            <code data-cancel-modal-identifier>srv.vortex</code>
        </div>

        <form method="POST" data-cancel-modal-form>
            @csrf
            <label class="cancel-modal-field">
                <span>Digite o identificador acima para confirmar</span>
                <input
                    type="text"
                    name="service_identifier"
                    autocomplete="off"
                    data-cancel-modal-input
                    required
                >
            </label>
            <small class="cancel-modal-error" data-cancel-modal-error hidden>
                O identificador precisa ser igual ao do serviço.
            </small>
            <div class="cancel-modal-actions">
                <button type="button" class="cancel-modal-secondary" data-cancel-modal-close>
                    Voltar
                </button>
                <button type="submit" class="cancel-modal-danger" data-cancel-modal-submit disabled>
                    Confirmar cancelamento
                </button>
            </div>
        </form>
    </div>
</div>

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
                processRenewals();
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

        const cancelModal = document.querySelector('[data-cancel-modal]');
        const cancelForm = cancelModal?.querySelector('[data-cancel-modal-form]');
        const cancelInput = cancelModal?.querySelector('[data-cancel-modal-input]');
        const cancelSubmit = cancelModal?.querySelector('[data-cancel-modal-submit]');
        const cancelError = cancelModal?.querySelector('[data-cancel-modal-error]');
        const cancelName = cancelModal?.querySelector('[data-cancel-modal-name]');
        const cancelIdentifier = cancelModal?.querySelector('[data-cancel-modal-identifier]');
        let expectedCancelIdentifier = '';

        const updateCancelSubmit = () => {
            if (!cancelInput || !cancelSubmit) {
                return;
            }

            const matches = cancelInput.value.trim() === expectedCancelIdentifier;
            cancelSubmit.disabled = !matches;

            if (cancelError) {
                cancelError.hidden = matches || cancelInput.value.trim() === '';
            }
        };

        const closeCancelModal = () => {
            if (!cancelModal) {
                return;
            }

            cancelModal.hidden = true;
            document.body.classList.remove('has-cancel-modal');
            expectedCancelIdentifier = '';

            if (cancelForm) {
                cancelForm.removeAttribute('action');
            }
        };

        const openCancelModal = (trigger) => {
            if (!cancelModal || !cancelForm || !cancelInput) {
                return;
            }

            expectedCancelIdentifier = trigger.dataset.cancelIdentifier || '';
            cancelForm.action = trigger.dataset.cancelUrl || '';

            if (cancelName) {
                cancelName.textContent = trigger.dataset.cancelName || 'Servidor Vortex';
            }

            if (cancelIdentifier) {
                cancelIdentifier.textContent = expectedCancelIdentifier;
            }

            cancelInput.value = '';
            updateCancelSubmit();
            cancelModal.hidden = false;
            document.body.classList.add('has-cancel-modal');
            cancelInput.focus();
        };

        document.addEventListener('click', (event) => {
            const openTrigger = event.target.closest('[data-open-service-cancel]');

            if (openTrigger) {
                event.preventDefault();
                openCancelModal(openTrigger);
                return;
            }

            if (event.target.closest('[data-cancel-modal-close]')) {
                event.preventDefault();
                closeCancelModal();
            }
        });

        cancelInput?.addEventListener('input', updateCancelSubmit);

        cancelForm?.addEventListener('submit', (event) => {
            if (cancelInput?.value.trim() !== expectedCancelIdentifier) {
                event.preventDefault();
                updateCancelSubmit();
                cancelInput?.focus();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && cancelModal && !cancelModal.hidden) {
                closeCancelModal();
            }
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
        let paymentStatusCheckInProgress = false;

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
                    '#faturas .invoice-list',
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
                initPixCharges();
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

        const loadPixCharge = async (container) => {
            if (container.dataset.pixLoading === '1' || container.dataset.pixLoaded === '1') {
                return;
            }

            const loader = container.querySelector('[data-pix-loader]');
            const image = container.querySelector('[data-pix-image]');
            const code = container.querySelector('[data-pix-code]');
            const copy = container.querySelector('[data-pix-copy]');
            const open = container.querySelector('[data-pix-open]');
            const status = container.querySelector('[data-pix-status]');

            container.dataset.pixLoading = '1';
            loader.hidden = false;
            loader.textContent = 'Gerando QR Code Pix...';
            status.textContent = 'A cobrança é gerada com segurança pelo Mercado Pago.';

            try {
                const response = await fetch(container.dataset.pixUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': container.dataset.csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({}),
                });
                const result = await response.json();

                if (!response.ok || !result.qr_code || !result.qr_code_base64) {
                    throw new Error(result.message || 'Não foi possível gerar o QR Code.');
                }

                image.src = result.qr_code_base64.startsWith('data:image')
                    ? result.qr_code_base64
                    : `data:image/png;base64,${result.qr_code_base64}`;
                image.hidden = false;
                loader.hidden = true;
                code.value = result.qr_code;
                code.hidden = false;
                copy.hidden = false;

                if (result.ticket_url) {
                    open.href = result.ticket_url;
                    open.hidden = false;
                }

                status.textContent = 'QR Code pronto. Após o pagamento, a confirmação é automática.';
                container.dataset.pixLoaded = '1';
            } catch (error) {
                loader.textContent = 'QR Code indisponível';
                status.textContent = error.message || 'Não foi possível gerar o QR Code agora.';
            } finally {
                container.dataset.pixLoading = '0';
            }
        };

        const initPixCharges = () => {
            document.querySelectorAll('[data-pix-charge]').forEach((container) => {
                const copy = container.querySelector('[data-pix-copy]');

                if (container.dataset.pixReady !== '1') {
                    container.dataset.pixReady = '1';

                    copy?.addEventListener('click', async () => {
                        const code = container.querySelector('[data-pix-code]');

                        try {
                            await navigator.clipboard.writeText(code.value);
                            copy.textContent = 'Código copiado';
                            setTimeout(() => {
                                copy.textContent = 'Copiar código Pix';
                            }, 1800);
                        } catch (error) {
                            code.focus();
                            code.select();
                            document.execCommand('copy');
                        }
                    });
                }

                loadPixCharge(container);
            });
        };

        const checkPaymentStatuses = async () => {
            if (paymentStatusCheckInProgress || document.hidden) {
                return;
            }

            const charges = Array.from(document.querySelectorAll('[data-pix-charge][data-status-url]'));

            if (!charges.length) {
                return;
            }

            paymentStatusCheckInProgress = true;

            try {
                const results = await Promise.all(charges.map(async (charge) => {
                    const response = await fetch(charge.dataset.statusUrl, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        cache: 'no-store',
                        credentials: 'same-origin',
                    });

                    return response.ok ? response.json() : null;
                }));

                if (results.some((result) => result && result.status !== 'pendente')) {
                    await refreshDashboardFragments();
                }
            } catch (error) {
                console.warn('Falha ao consultar status do pagamento.', error);
            } finally {
                paymentStatusCheckInProgress = false;
            }
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

                    if (timer.dataset.expirationProcessed !== '1') {
                        timer.dataset.expirationProcessed = '1';
                        processRenewals();
                    }

                    return;
                }

                timer.textContent = days > 0
                    ? `${days}d ${hours}h ${minutes}m`
                    : `${hours}h ${minutes}m ${seconds}s`;
            });
        };

        initPixCharges();
        showView(window.location.hash.replace('#', '') || 'dashboard', false);
        updateTimers();
        setInterval(updateTimers, 1000);
        setInterval(checkPaymentStatuses, 5000);
    })();
</script>
@endpush
