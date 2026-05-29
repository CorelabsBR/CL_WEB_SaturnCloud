@php
    $hideSupportWidget = auth()->user()?->tipo_usuario === 'root'
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
                            @if($message->body)
                                <p>{{ $message->body }}</p>
                            @endif
                            @if($message->image_path)
                                <a href="{{ asset($message->image_path) }}" target="_blank" rel="noopener">
                                    <img src="{{ asset($message->image_path) }}" alt="Imagem enviada" class="support-message-image">
                                </a>
                            @endif
                            <small>#{{ $ticket->id }} · {{ optional($message->criado_em)->format('d/m/Y H:i') }}</small>
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>

        @auth
            <form class="support-chat-form" method="POST" action="{{ route('ticket.store') }}" enctype="multipart/form-data" data-support-form>
                @csrf
                <input type="hidden" name="subject" value="Atendimento pelo chat">
                <input type="hidden" name="category" value="Suporte geral">
                <input type="hidden" name="priority" value="normal">

                <div class="support-quick-questions" aria-label="Perguntas rápidas">
                    <button type="button" data-support-question="Como acompanho meu pedido?">Como acompanho meu pedido?</button>
                    <button type="button" data-support-question="Meu servidor ainda não foi criado.">Servidor não criado</button>
                    <button type="button" data-support-question="Preciso de ajuda para configurar meu servidor.">Configurar servidor</button>
                    <button type="button" data-support-question="Quero tirar uma dúvida sobre pagamento.">Pagamento</button>
                </div>

                <textarea name="message" rows="3" placeholder="Digite sua mensagem...">{{ old('message') }}</textarea>
                <div class="support-chat-tools">
                    <label class="support-image-button" title="Adicionar imagem">
                        <input type="file" name="image" accept="image/*" data-support-image>
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M4 17l4.5-4.5a2 2 0 0 1 2.8 0L16 17" />
                            <path d="M14 15l1.5-1.5a2 2 0 0 1 2.8 0L20 15" />
                            <path d="M5 5h14v14H5z" />
                            <path d="M15 8h.01" />
                        </svg>
                    </label>
                    <small data-support-image-name hidden></small>
                    <button class="support-image-clear" type="button" data-support-image-clear hidden aria-label="Remover imagem anexada" title="Remover imagem">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M18 6 6 18" />
                            <path d="m6 6 12 12" />
                        </svg>
                    </button>
                </div>
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
        let formError = form?.querySelector('[data-support-form-error]');

        const textarea = form?.querySelector('textarea[name="message"]');
        const submitButton = form?.querySelector('button[type="submit"]');
        const imageInput = form?.querySelector('[data-support-image]');
        const imageName = form?.querySelector('[data-support-image-name]');
        const imageClear = form?.querySelector('[data-support-image-clear]');
        const quickQuestions = form?.querySelectorAll('[data-support-question]');
        const compressedUploadLimit = 1800 * 1024;
        const imageExtensionPattern = /\.(avif|bmp|gif|jpe?g|png|svg|webp)$/i;

        if (form && !formError) {
            formError = document.createElement('small');
            formError.setAttribute('data-support-form-error', '');
            formError.hidden = true;
            form.insertBefore(formError, submitButton);
        }

        if (textarea) {
            textarea.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter' || event.shiftKey || event.ctrlKey || event.metaKey || event.altKey) {
                    return;
                }

                event.preventDefault();
                form.requestSubmit();
            });
        }

        quickQuestions?.forEach((button) => {
            button.addEventListener('click', () => {
                if (!textarea) {
                    return;
                }

                textarea.value = button.dataset.supportQuestion || button.textContent.trim();
                textarea.focus();
                textarea.setSelectionRange(textarea.value.length, textarea.value.length);
                setFormError();
            });
        });

        const clearImage = () => {
            if (imageInput) {
                imageInput.value = '';
            }

            if (!imageName) {
                return;
            }

            imageName.textContent = '';
            imageName.hidden = true;

            if (imageClear) {
                imageClear.hidden = true;
            }
        };

        imageInput?.addEventListener('change', () => {
            const file = imageInput.files?.[0];

            if (!file) {
                clearImage();
                setFormError();
                return;
            }

            if (file.type && !file.type.startsWith('image/') && !imageExtensionPattern.test(file.name)) {
                clearImage();
                setFormError('Selecione um arquivo de imagem.');
                return;
            }

            if (file.size > 8192 * 1024) {
                clearImage();
                setFormError('A imagem pode ter no máximo 8 MB.');
                return;
            }

            if (imageName) {
                imageName.textContent = file.name;
                imageName.hidden = false;
            }

            if (imageClear) {
                imageClear.hidden = false;
            }

            setFormError();
        });

        imageClear?.addEventListener('click', () => {
            clearImage();
            setFormError();
        });

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
                        ${message.body ? `<p>${escapeHtml(message.body)}</p>` : ''}
                        ${message.image_url ? `<a href="${escapeHtml(message.image_url)}" target="_blank" rel="noopener"><img src="${escapeHtml(message.image_url)}" alt="Imagem enviada" class="support-message-image"></a>` : ''}
                        <small>#${escapeHtml(message.ticket_id)} · ${escapeHtml(message.time)}</small>
                    </div>
                `;
            }).join('');

            if (badge) {
                const count = Number(payload.open_count ?? 0);
                badge.textContent = count;
                badge.hidden = count <= 0;
            }

            if (body && shouldStickToBottom) {
                requestAnimationFrame(() => {
                    body.scrollTo({
                        top: body.scrollHeight,
                        behavior: 'smooth'
                    });
                });
            }
        };

        const setFormError = (message = '') => {
            if (!formError) {
                return;
            }

            formError.textContent = message;
            formError.hidden = !message;
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
            const imageInput = form.querySelector('[data-support-image]');
            const message = textarea.value.trim();

            if (!message && !imageInput?.files?.length) {
                setFormError('Digite uma mensagem ou adicione uma imagem.');
                textarea.focus();
                return;
            }

            setFormError();
            submitButton?.setAttribute('disabled', 'disabled');

            try {
                const formData = new FormData(form);
                const imageFile = imageInput?.files?.[0];

                if (imageFile) {
                    const uploadFile = await compressImageFile(imageFile);
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
                    let errorMessage = 'Não foi possível enviar agora. Tente novamente em instantes.';

                    if (response.status === 419) {
                        errorMessage = 'Sua sessão expirou. Atualize a página quando puder e tente novamente.';
                    } else if (response.status === 422) {
                        const payload = await response.json().catch(() => null);
                        errorMessage = payload?.message
                            || Object.values(payload?.errors ?? {})?.flat()?.[0]
                            || 'Confira a mensagem e tente novamente.';
                    }

                    setFormError(errorMessage);
                    return;
                }

                textarea.value = '';
                clearImage();
                renderMessages(await response.json());
                widget.classList.add('is-open');
            } catch (error) {
                setFormError('Falha de conexão. Sua mensagem não foi enviada ainda.');
            } finally {
                submitButton?.removeAttribute('disabled');
            }
        });

        fetchMessages();
        setInterval(fetchMessages, 4000);
    })();
</script>
@endunless
