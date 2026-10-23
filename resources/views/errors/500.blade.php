<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#050505">
    <meta name="robots" content="noindex">
    <title>Erro no servidor | Saturn Cloud</title>
    <link rel="icon" href="{{ asset('img/logo-128.png') }}">
    <link rel="stylesheet" href="{{ asset('css/error500.css') }}?v={{ filemtime(public_path('css/error500.css')) }}">
</head>
<body class="error500">
    <div class="error500__top">
        <nav class="error500__navigation" aria-label="Navegação">
            <a class="error500__brand" href="{{ url('/') }}" aria-label="Saturn Cloud — início">
                <img src="{{ asset('img/saturno_branco_quadrado.svg') }}" width="60" height="60" alt="">
                <span>Saturn <strong>Cloud</strong></span>
            </a>
        </nav>
    </div>
    <main class="error500__main">
        <section class="error500__scene" aria-labelledby="error500-title">
            <div class="error500__layout">
                <div class="error500__copy">
                    <p class="error500__code">Erro 500 · Falha no servidor</p>
                    <h1 id="error500-title">
                        <span class="error500__title-line">Houston, temos</span>
                        <span class="error500__title-line">um problema.</span>
                    </h1>
                    <p class="error500__description">
                        O servidor não conseguiu concluir sua solicitação.
                        Tente novamente em instantes ou volte ao início.
                    </p>
                    <div class="error500__actions">
                        <a class="error500__home" href="{{ url()->current() }}">Tentar novamente</a>
                        <a class="error500__help" href="{{ url('/') }}">Voltar ao início</a>
                    </div>
                </div>
                <div class="error500__illustration">
                    <img
                        class="error500__engineer"
                        src="{{ asset('img/space-engineer-server-500.png') }}"
                        alt="Engenheiro espacial usando uma ferramenta para consertar um servidor aberto."
                        width="1024"
                        height="1536"
                        fetchpriority="high"
                        decoding="async"
                    >
                </div>
            </div>
        </section>
    </main>
</body>
</html>
