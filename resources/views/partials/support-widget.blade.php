@php
    $hideSupportWidget = ! auth()->check()
        || auth()->user()?->tipo_usuario === 'root'
        || request()->routeIs('login', 'registro', 'cadastro.completar')
        || request()->is('login', 'registro', 'completar-cadastro');

    $supportTickets = auth()->check()
        ? \App\Models\SupportTicket::with('messages.user')
            ->where('user_id', auth()->id())
            ->latest('atualizado_em')
            ->limit(5)
            ->get()
        : collect();
    $shouldOpenSupport = session('support_widget_open') || $errors->has('message');
@endphp

@unless($hideSupportWidget)
<div
    class="support-widget {{ $shouldOpenSupport ? 'is-open' : '' }}"
    data-support-widget
    data-support-messages-url="{{ route('ticket.messages') }}"
>
    <button class="support-fab" type="button" aria-label="Abrir chat de suporte" data-support-toggle>
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M21 11.5a8.4 8.4 0 0 1-8.7 8.3 9.2 9.2 0 0 1-3.8-.8L3 20.5l1.6-4.7a7.9 7.9 0 0 1-1.2-4.3 8.4 8.4 0 0 1 8.7-8.3 8.4 8.4 0 0 1 8.9 8.3Z" />
            <path d="M8 10h8M8 14h5" />
        </svg>
        @if($supportTickets->whereIn('status', ['aberto', 'respondido'])->count() > 0)
            <span data-support-count>{{ $supportTickets->whereIn('status', ['aberto', 'respondido'])->count() }}</span>
        @else
            <span data-support-count hidden>0</span>
        @endif
    </button>

    <section class="support-chat" aria-label="Chat de suporte">
        <div class="support-chat-head">
            <div>
                <strong>Suporte VortexHost</strong>
                <span>Responderemos assim que possível.</span>
            </div>
            <button type="button" aria-label="Fechar chat de suporte" data-support-toggle>×</button>
        </div>

        <div class="support-chat-body" data-support-body>
            <div class="support-message support-message-team">
                <strong>Suporte</strong>
                <p>Olá! Descreva sua dúvida ou problema e nossa equipe acompanha por aqui.</p>
            </div>

            <div data-support-messages>
                @foreach($supportTickets as $ticket)
                    @foreach($ticket->messages as $message)
                        <div class="support-message {{ $message->author_type === 'admin' ? 'support-message-team' : 'support-message-user' }}">
                            <strong>{{ $message->author_type === 'admin' ? ($message->user?->nome ?? 'Suporte') : 'Você' }}</strong>
                            <p>{{ $message->body }}</p>
                            <small>#{{ $ticket->id }} · {{ optional($message->criado_em)->format('d/m/Y H:i') }}</small>
                        </div>
                    @endforeach
                @endforeach
            </div>

            @if(session('success'))
                <div class="support-message support-message-team">
                    <strong>Suporte</strong>
                    <p>{{ session('success') }}</p>
                </div>
            @endif
        </div>

        @auth
            <form class="support-chat-form" method="POST" action="{{ route('ticket.store') }}" data-support-form>
                @csrf
                <input type="hidden" name="subject" value="Atendimento pelo chat">
                <input type="hidden" name="category" value="Suporte geral">
                <input type="hidden" name="priority" value="normal">

                <textarea name="message" rows="3" placeholder="Digite sua mensagem..." required>{{ old('message') }}</textarea>
                @error('message')
                    <small>{{ $message }}</small>
                @enderror

                <button type="submit" class="btn btn-primary btn-full">Enviar</button>
            </form>
        @else
            <div class="support-chat-form">
                <p class="support-login-note">Entre na sua conta para abrir um atendimento.</p>
                <a href="{{ url('/login') }}" class="btn btn-primary btn-full">Entrar</a>
                <a href="{{ url('/registro') }}" class="support-register-link">Criar conta</a>
            </div>
        @endauth
    </section>
</div>

<script>
    (() => {
        const widget = document.querySelector('[data-support-widget]');

        if (!widget) {
            return;
        }

        const toggles = document.querySelectorAll('[data-support-toggle], [data-support-open]');
        const body = widget.querySelector('[data-support-body]');
        const messagesBox = widget.querySelector('[data-support-messages]');
        const form = widget.querySelector('[data-support-form]');
        const badge = widget.querySelector('[data-support-count]');
        let lastSignature = '';

        toggles.forEach((toggle) => {
            toggle.addEventListener('click', (event) => {
                event.preventDefault();
                widget.classList.toggle('is-open');
            });
        });

        const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        })[char]);

        const renderMessages = (payload) => {
            if (!payload || !messagesBox) {
                return;
            }

            const signature = JSON.stringify(payload.messages ?? []);
            const shouldStickToBottom = !body || body.scrollHeight - body.scrollTop - body.clientHeight < 80;

            if (signature === lastSignature) {
                return;
            }

            lastSignature = signature;
            messagesBox.innerHTML = (payload.messages ?? []).map((message) => {
                const isAdmin = message.author_type === 'admin';

                return `
                    <div class="support-message ${isAdmin ? 'support-message-team' : 'support-message-user'}">
                        <strong>${escapeHtml(message.author_name || (isAdmin ? 'Suporte' : 'Você'))}</strong>
                        <p>${escapeHtml(message.body)}</p>
                        <small>#${escapeHtml(message.ticket_id)} · ${escapeHtml(message.time)}</small>
                    </div>
                `;
            }).join('');

            if (badge) {
                const count = Number(payload.open_count ?? 0);
                badge.textContent = count;
                badge.hidden = count <= 0;
            }

            if (body && (shouldStickToBottom || widget.classList.contains('is-open'))) {
                requestAnimationFrame(() => {
                    body.scrollTo({
                        top: body.scrollHeight,
                        behavior: 'smooth'
                    });
                });
            }
        };

        const fetchMessages = async () => {
            try {
                const response = await fetch(widget.dataset.supportMessagesUrl, {
                    cache: 'no-store',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    renderMessages(await response.json());
                }
            } catch (error) {
                // Keep the static fallback visible if polling fails.
            }
        };

        form?.addEventListener('submit', async (event) => {
            event.preventDefault();

            const textarea = form.querySelector('textarea[name="message"]');

            if (!textarea || textarea.value.trim().length < 10) {
                form.submit();
                return;
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    form.submit();
                    return;
                }

                textarea.value = '';
                renderMessages(await response.json());
                widget.classList.add('is-open');
            } catch (error) {
                form.submit();
            }
        });

        fetchMessages();
        setInterval(fetchMessages, 4000);
    })();
</script>
@endunless
