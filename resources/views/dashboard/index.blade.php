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

    $activeServices = $services->filter(fn ($service) => data_get($service, 'status', 'ativo') === 'ativo')->count();
    $pendingInvoices = $invoices->filter(fn ($invoice) => data_get($invoice, 'status') === 'pendente')->count();
    $lastPayment = $invoices->firstWhere('status', 'pago');
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
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="client-content">
        <div class="container">
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
                            <small>{{ $pendingInvoices > 0 ? 'Revise os vencimentos em aberto' : 'Tudo em dia no financeiro' }}</small>
                        </div>

                        <div class="dash-card">
                            <div class="dash-card-top">
                                <span class="dash-card-title">Conta criada</span>
                                <span class="dash-card-icon" role="img" aria-label="Ícone de calendário">📅</span>
                            </div>
                            <strong>
                                {{ $user?->created_at?->format('d/m/Y') ?? '--/--/----' }}
                            </strong>
                            <small>Cliente VortexHost</small>
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

                                    $resources = collect(data_get($service, 'resources', []));
                                @endphp

                                <article class="service-row">
                                    <div class="service-main">
                                        <div class="service-title">
                                            <span>{{ data_get($service, 'name', 'Servidor Vortex') }}</span>
                                            <span class="{{ $statusClass }}">{{ ucfirst($status) }}</span>
                                        </div>
                                        <div class="service-meta">
                                            <span class="service-ip">{{ data_get($service, 'ip', 'Aguardando provisionamento') }}</span>
                                            <span>{{ data_get($service, 'plan', 'Plano sob demanda') }}</span>
                                        </div>
                                    </div>

                                    <div class="service-location">
                                        <strong>Data center</strong>
                                        {{ data_get($service, 'location', 'São Paulo, BR') }}
                                    </div>

                                    <div class="service-usage">
                                        <strong>Consumo atual</strong>
                                        {{ data_get($service, 'usage', 'Sem leitura recente') }}

                                        @if ($resources->isNotEmpty())
                                            <div class="resource-metrics is-compact">
                                                @foreach ($resources as $resource)
                                                    @php
                                                        $value = (int) data_get($resource, 'value', 0);
                                                    @endphp
                                                    <div class="resource-meter {{ $value >= 90 ? 'is-danger' : ($value >= 75 ? 'is-warning' : '') }}">
                                                        <div class="resource-meter-head">
                                                            <span class="resource-meter-label">{{ data_get($resource, 'label', 'Recurso') }}</span>
                                                            <span class="resource-meter-value">{{ $value }}%</span>
                                                        </div>
                                                        <div class="resource-meter-track" aria-hidden="true">
                                                            <span class="resource-meter-fill" style="--value: {{ $value }}%;"></span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <div class="service-actions" aria-label="Ações rápidas para {{ data_get($service, 'name', 'serviço') }}">
                                        <button type="button">Ligar</button>
                                        <button type="button">Desligar</button>
                                        <button type="button">Reiniciar</button>
                                    </div>
                                </article>
                            @empty
                                <div class="empty-state">
                                    <div class="empty-icon" role="img" aria-label="Ícone de monitor">🖥</div>
                                    <h3>Nenhum serviço encontrado</h3>
                                    <p>Contrate uma hospedagem de jogos, VPS ou site para começar a monitorar recursos em tempo real.</p>
                                </div>
                            @endforelse
                        </div>

                        <div class="dashboard-panel dashboard-view" id="faturas" data-dashboard-view="faturas" hidden>
                            <div class="panel-head">
                                <div>
                                    <h2>Faturas</h2>
                                    <p>Status financeiro dos pedidos e renovações da sua conta.</p>
                                </div>
                                <a href="/checkout" class="btn btn-sm btn-primary">
                                    Ver checkout
                                </a>
                            </div>

                            <div class="service-list">
                                @forelse ($invoices as $invoice)
                                    @php
                                        $invoiceStatus = data_get($invoice, 'status', 'pendente');
                                        $invoiceStatusClass = match ($invoiceStatus) {
                                            'pago' => 'status-pago',
                                            'suspenso', 'cancelado', 'vencido' => 'status-suspenso',
                                            default => 'status-pendente',
                                        };
                                    @endphp

                                    <article class="service-row">
                                        <div class="service-main">
                                            <div class="service-title">
                                                <span>Fatura #{{ data_get($invoice, 'id', 'VTX') }}</span>
                                                <span class="{{ $invoiceStatusClass }}">{{ ucfirst($invoiceStatus) }}</span>
                                            </div>
                                            <div class="service-meta">
                                                <span>{{ data_get($invoice, 'description', 'Serviços VortexHost') }}</span>
                                            </div>
                                        </div>

                                        <div class="service-location">
                                            <strong>Valor</strong>
                                            R$ {{ number_format((float) data_get($invoice, 'amount', 0), 2, ',', '.') }}
                                        </div>

                                        <div class="service-usage">
                                            <strong>Vencimento</strong>
                                            {{ data_get($invoice, 'due_at', 'Sem vencimento') }}
                                        </div>

                                        <div class="service-actions" aria-label="Ações da fatura">
                                            <a href="/checkout">Pagar</a>
                                            <a href="/carrinho">Detalhes</a>
                                        </div>
                                    </article>
                                @empty
                                    <div class="empty-state">
                                        <div class="empty-icon" role="img" aria-label="Ícone de cartão">💳</div>
                                        <h3>Nenhuma fatura pendente</h3>
                                        <p>Quando houver cobranças, renovações ou pedidos em aberto, eles aparecerão aqui.</p>
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
                                            <span>Cliente desde {{ $user?->created_at?->format('d/m/Y') ?? '--/--/----' }}</span>
                                        </div>
                                    </div>

                                    <div class="service-location">
                                        <strong>Conta</strong>
                                        VortexHost
                                    </div>

                                    <div class="service-usage">
                                        <strong>Cadastro</strong>
                                        {{ $user?->created_at?->format('d/m/Y') ?? '--/--/----' }}
                                    </div>

                                    <div class="service-actions" aria-label="Ações de perfil">
                                        <a href="{{ route('perfil.edit') }}">Editar</a>
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
    })();
</script>
@endpush
