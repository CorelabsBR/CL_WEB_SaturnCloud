@extends('layouts.app')

@section('title', 'Entrar na área do cliente | Saturn Cloud')
@section('meta_description', 'Acesse sua conta Saturn Cloud para acompanhar pedidos, serviços, pagamentos e chamados de suporte.')
@section('body_class', 'auth-body login-body')
@section('main_class', 'auth-main login-main')
@section('hide_header', true)
@section('hide_footer', true)

@section('content')
<div class="login-shell">
    <aside class="login-aside" aria-labelledby="login-context-title">
        <a href="{{ route('home') }}" class="login-brand" aria-label="Voltar para a página inicial da Saturn Cloud">
            <img src="{{ asset('img/saturn-cloud-mark.svg') }}" alt="" width="42" height="42">
            <span>Saturn Cloud<strong>Host</strong></span>
        </a>

        <div class="login-context">
            <p class="login-eyebrow">Área do cliente</p>
            <h2 id="login-context-title">Sua operação,<br>em um só lugar.</h2>
            <p>Acompanhe o que realmente importa depois da contratação.</p>

            <ul class="login-capabilities">
                <li><span>01</span><div><strong>Pedidos e pagamentos</strong><small>Consulte cobranças e o andamento dos pedidos.</small></div></li>
                <li><span>02</span><div><strong>Serviços contratados</strong><small>Acesse os recursos disponíveis para cada serviço.</small></div></li>
                <li><span>03</span><div><strong>Suporte registrado</strong><small>Mantenha seus chamados e respostas organizados.</small></div></li>
            </ul>
        </div>

        <p class="login-aside-note">Ambiente restrito a clientes e administradores Saturn Cloud.</p>
    </aside>

    <section class="login-form-panel" aria-labelledby="login-title">
        <a href="{{ route('home') }}" class="login-back">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
            Voltar ao site
        </a>

        <div class="login-card">
            <header class="login-card-head">
                <p class="login-eyebrow">Acesso seguro</p>
                <h1 id="login-title">Entrar</h1>
                <p>Use o e-mail cadastrado para acessar sua conta.</p>
            </header>

            @if ($errors->any())
                <div class="login-alert login-alert-error" role="alert" aria-live="polite">
                    <strong>Não foi possível entrar.</strong>
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            @if (session('success'))
                <div class="login-alert login-alert-success" role="status">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="login-form">
                @csrf

                <label class="login-field" for="email">
                    <span>E-mail</span>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" inputmode="email" placeholder="voce@exemplo.com" @error('email') aria-invalid="true" aria-describedby="login-errors" @enderror>
                </label>

                <label class="login-field" for="password">
                    <span>Senha</span>
                    <span class="login-password-control">
                        <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Digite sua senha" @error('password') aria-invalid="true" aria-describedby="login-errors" @enderror>
                        <button type="button" class="login-password-toggle" aria-label="Mostrar senha" aria-pressed="false" data-password-toggle>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                        </button>
                    </span>
                </label>

                <div class="login-form-options">
                    <label class="login-remember"><input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}><span>Lembrar meu acesso</span></label>
                    <a href="{{ route('password.request') }}">Esqueci minha senha</a>
                </div>

                <button class="btn btn-primary login-submit" type="submit">Acessar minha conta <span aria-hidden="true">→</span></button>
            </form>

            <div class="login-divider"><span>ou continue com</span></div>

            <div class="login-social">
                <a href="{{ route('social.redirect', 'google') }}" class="login-social-button">
                    <svg viewBox="0 0 48 48" aria-hidden="true"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24s.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.31-8.16 2.31-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg>
                    Google
                </a>
                <a href="{{ route('social.redirect', 'discord') }}" class="login-social-button">
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.3 4.4A19.8 19.8 0 0 0 15.4 2.9l-.6 1.2a18.3 18.3 0 0 0-5.5 0l-.6-1.2a19.7 19.7 0 0 0-4.9 1.5C.5 9-.3 13.6.1 18.1a20 20 0 0 0 6 3l1.2-2a13 13 0 0 1-1.9-.9l.4-.3c3.9 1.8 8.2 1.8 12.1 0l.4.3c-.6.3-1.2.6-1.9.9l1.2 2a20 20 0 0 0 6-3c.5-5.2-.8-9.7-3.5-13.7ZM8 15.3c-1.2 0-2.2-1.1-2.2-2.4s1-2.4 2.2-2.4 2.2 1.1 2.2 2.4-1 2.4-2.2 2.4Zm8 0c-1.2 0-2.2-1.1-2.2-2.4s1-2.4 2.2-2.4 2.2 1.1 2.2 2.4-1 2.4-2.2 2.4Z"/></svg>
                    Discord
                </a>
            </div>

            <p class="login-register">Ainda não possui conta? <a href="{{ route('registro') }}">Criar conta</a></p>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.querySelector('[data-password-toggle]')?.addEventListener('click', (event) => {
    const button = event.currentTarget;
    const input = document.getElementById('password');
    const showing = input.type === 'text';
    input.type = showing ? 'password' : 'text';
    button.setAttribute('aria-pressed', showing ? 'false' : 'true');
    button.setAttribute('aria-label', showing ? 'Mostrar senha' : 'Ocultar senha');
});
</script>
@endpush
