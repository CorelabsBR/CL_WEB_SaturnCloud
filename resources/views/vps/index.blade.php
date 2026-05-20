@extends('layouts.app')

@section('title', 'VPS em Breve')

@section('content')
<main class="coming-page">
    <section class="coming-hero">
        <div class="container">
            <h1>em breve.</h1>

            <p class="coming-sub">
                Estamos preparando a nova infraestrutura VPS da VortexHost.
                Mais performance, controle total e virtualização profissional.
            </p>

            <div class="coming-status">
                <div class="status-dot"></div>
                <span>Deploy em preparação</span>
            </div>

            <div class="coming-features">
                <div class="feature-card">
                    <h3>KVM Virtualization</h3>
                    <p>
                        Ambientes isolados com acesso root completo.
                    </p>
                </div>

                <div class="feature-card">
                    <h3>Anti-DDoS</h3>
                    <p>
                        Proteção de rede e mitigação inteligente.
                    </p>
                </div>

                <div class="feature-card">
                    <h3>NVMe Enterprise</h3>
                    <p>
                        Storage extremamente rápido para aplicações pesadas.
                    </p>
                </div>

                <div class="feature-card">
                    <h3>Linux & Windows</h3>
                    <p>
                        Templates preparados para múltiplos sistemas.
                    </p>
                </div>
            </div>

            <div class="coming-actions">
                <a href="/" class="btn btn-primary">
                    Voltar ao início
                </a>

                <a href="/contato" class="btn btn-ghost">
                    Entrar em contato
                </a>
            </div>
        </div>

        <div class="coming-glow glow-1"></div>
        <div class="coming-glow glow-2"></div>
    </section>
</main>
@endsection
