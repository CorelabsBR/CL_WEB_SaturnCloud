@extends('layouts.app')

@section('title', 'Suporte')

@section('content')

@php
    $user = auth()->user();
    $tickets = collect($tickets ?? []);
    $openTickets = $tickets->filter(fn ($ticket) => data_get($ticket, 'status', 'aberto') === 'aberto')->count();

    $statusClasses = [
        'aberto' => 'status-open',
        'respondido' => 'status-answered',
        'fechado' => 'status-closed',
    ];

    $priorityClasses = [
        'baixa' => 'priority-low',
        'normal' => 'priority-normal',
        'alta' => 'priority-high',
        'urgente' => 'priority-urgent',
    ];
@endphp

<div class="client-page support-page">
    <div class="ticket-container">
        <section class="support-hero" aria-labelledby="support-title">
            <div>
                <h1 id="support-title">Como podemos ajudar?</h1>
                <p>
                    Abra um chamado para nossa equipe acompanhar sua solicitação com contexto, prioridade e histórico.
                </p>
            </div>

            <div class="support-summary" aria-label="Resumo dos chamados">
                <strong>{{ $openTickets }}</strong>
                <span>{{ $openTickets === 1 ? 'ticket aberto' : 'tickets abertos' }}</span>
            </div>
        </section>

        @if (session('success'))
            <div class="flash flash-success" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="flash flash-error" role="alert">
                Revise os campos destacados e tente novamente.
            </div>
        @endif

        <div class="support-grid">
            <section class="ticket-panel ticket-form-panel" aria-labelledby="new-ticket-title">
                <div class="panel-title">
                    <div>
                        <h2 id="new-ticket-title">Abrir chamado</h2>
                        <p>Descreva o que está acontecendo para acelerarmos o atendimento.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('ticket.store') }}" class="ticket-form">
                    @csrf

                    <div class="form-grid">
                        <label class="field">
                            <span>Assunto</span>
                            <input
                                type="text"
                                name="subject"
                                value="{{ old('subject') }}"
                                placeholder="Ex: Dúvida sobre meu serviço"
                                maxlength="120"
                                required
                            >
                            @error('subject')
                                <small>{{ $message }}</small>
                            @enderror
                        </label>

                        <label class="field">
                            <span>Serviço</span>
                            <select name="category" required>
                                <option value="">Selecione uma opção</option>
                                @foreach (['Hospedagem Web', 'Servidor VPS', 'Servidor de Jogos', 'Pagamento', 'Conta', 'Outro'] as $category)
                                    <option value="{{ $category }}" @selected(old('category') === $category)>
                                        {{ $category }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category')
                                <small>{{ $message }}</small>
                            @enderror
                        </label>
                    </div>

                    <label class="field">
                        <span>Prioridade</span>
                        <select name="priority" required>
                            @foreach (['baixa' => 'Baixa', 'normal' => 'Normal', 'alta' => 'Alta', 'urgente' => 'Urgente'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('priority', 'normal') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('priority')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    <label class="field">
                        <span>Mensagem</span>
                        <textarea
                            name="message"
                            placeholder="Conte o que aconteceu, quando começou e qualquer detalhe que ajude na análise."
                            rows="7"
                            required
                        >{{ old('message') }}</textarea>
                        @error('message')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    <div class="reply-actions">
                        <a href="{{ url('/painel#tickets') }}" class="btn btn-ghost">Ver painel</a>
                        <button type="submit" class="btn btn-primary">Enviar chamado</button>
                    </div>
                </form>
            </section>

            <aside class="support-side">
                <section class="ticket-panel account-card" aria-labelledby="account-title">
                    <h2 id="account-title">Sua conta</h2>
                    <div class="account-row">
                        <span>Nome</span>
                        <strong>{{ $user?->nome ?? 'Cliente' }}</strong>
                    </div>
                    <div class="account-row">
                        <span>E-mail</span>
                        <strong>{{ $user?->email ?? 'Não informado' }}</strong>
                    </div>
                </section>

                <section class="ticket-panel support-guidance" aria-labelledby="guidance-title">
                    <h2 id="guidance-title">Para agilizar</h2>
                    <ul>
                        <li>Informe o serviço afetado.</li>
                        <li>Inclua mensagens de erro quando existirem.</li>
                        <li>Explique o impacto e a urgência.</li>
                    </ul>
                </section>
            </aside>
        </div>

        <section class="ticket-panel tickets-list-panel" aria-labelledby="tickets-title">
            <div class="panel-title">
                <div>
                    <h2 id="tickets-title">Meus chamados</h2>
                    <p>Acompanhe seus chamados e as respostas do suporte.</p>
                </div>
            </div>

            <div class="tickets-list">
                @forelse ($tickets as $ticket)
                    @php
                        $status = data_get($ticket, 'status', 'aberto');
                        $priority = data_get($ticket, 'priority', 'normal');
                    @endphp

                    <article class="ticket-card">
                        <div class="ticket-card-main">
                            <div class="ticket-card-title">
                                <h3>{{ data_get($ticket, 'subject') }}</h3>
                                <span class="ticket-status {{ $statusClasses[$status] ?? 'status-open' }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </div>

                            <p>{{ optional($ticket->messages->first())->body ?? 'Chamado sem mensagens.' }}</p>

                            @if($ticket->messages->where('author_type', 'admin')->isNotEmpty())
                                <div class="ticket-replies">
                                    @foreach($ticket->messages->where('author_type', 'admin') as $reply)
                                        <div class="ticket-reply">
                                            <strong>{{ $reply->user?->nome ?? 'Suporte' }}</strong>
                                            <span>{{ $reply->body }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="ticket-meta">
                                <span>#{{ data_get($ticket, 'id') }}</span>
                                <span>{{ data_get($ticket, 'category') }}</span>
                                <span class="{{ $priorityClasses[$priority] ?? 'priority-normal' }}">
                                    {{ ucfirst($priority) }}
                                </span>
                                <span>{{ optional($ticket->criado_em)->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="empty-state">
                        <div class="empty-icon" aria-hidden="true">?</div>
                        <h3>Nenhum chamado aberto</h3>
                        <p>Quando você abrir um chamado, ele aparecerá aqui para acompanhamento.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>

@endsection
