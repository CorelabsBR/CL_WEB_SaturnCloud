@php
    $supportTickets = collect(session('support_tickets', []))->sortBy('id')->values();
    $shouldOpenSupport = session('support_widget_open') || $errors->has('message');
@endphp

<div class="support-widget {{ $shouldOpenSupport ? 'is-open' : '' }}" data-support-widget>
    <button class="support-fab" type="button" aria-label="Abrir chat de suporte" data-support-toggle>
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M21 11.5a8.4 8.4 0 0 1-8.7 8.3 9.2 9.2 0 0 1-3.8-.8L3 20.5l1.6-4.7a7.9 7.9 0 0 1-1.2-4.3 8.4 8.4 0 0 1 8.7-8.3 8.4 8.4 0 0 1 8.9 8.3Z" />
            <path d="M8 10h8M8 14h5" />
        </svg>
        @if($supportTickets->where('status', 'aberto')->count() > 0)
            <span>{{ $supportTickets->where('status', 'aberto')->count() }}</span>
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

        <div class="support-chat-body">
            <div class="support-message support-message-team">
                <strong>Suporte</strong>
                <p>Olá! Descreva sua dúvida ou problema e nossa equipe acompanha por aqui.</p>
            </div>

            @foreach($supportTickets as $ticket)
                <div class="support-message support-message-user">
                    <strong>Você</strong>
                    <p>{{ data_get($ticket, 'message') }}</p>
                    <small>#{{ data_get($ticket, 'id') }} · {{ data_get($ticket, 'created_at') }}</small>
                </div>
            @endforeach

            @if(session('success'))
                <div class="support-message support-message-team">
                    <strong>Suporte</strong>
                    <p>{{ session('success') }}</p>
                </div>
            @endif
        </div>

        @auth
            <form class="support-chat-form" method="POST" action="{{ route('ticket.store') }}">
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

        toggles.forEach((toggle) => {
            toggle.addEventListener('click', (event) => {
                event.preventDefault();
                widget.classList.toggle('is-open');
            });
        });
    })();
</script>
