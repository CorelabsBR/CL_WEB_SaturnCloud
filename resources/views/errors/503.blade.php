<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#050505">
    <meta name="robots" content="noindex">
    <title>Estação espacial em manutenção | Saturn Cloud</title>
    <link rel="icon" href="{{ asset('img/logo-128.png') }}">
    <link rel="stylesheet" href="{{ asset('css/error503.css') }}">
</head>
<body class="error503">
    <header class="error503__header">
        <a class="error503__brand" href="{{ url('/') }}" aria-label="Saturn Cloud — início">
            <img src="{{ asset('img/saturno_branco_quadrado.svg') }}" width="60" height="60" alt="">
            <span>Saturn <strong>Cloud</strong></span>
        </a>
    </header>
    <main class="error503__main">
        <section class="error503__layout" aria-labelledby="error503-title">
            <div class="error503__copy">
                <p class="error503__code">Erro 503 · Serviço temporariamente indisponível</p>
                <h1 id="error503-title">Estação espacial<br>em manutenção.</h1>
                <p class="error503__description">Estamos ajustando os sistemas da estação. O acesso está temporariamente indisponível. Tente novamente em instantes.</p>
                <a class="error503__retry" href="{{ url()->full() }}">Tentar novamente</a>
            </div>
            <img
                class="error503__station"
                src="{{ asset('img/space-station-maintenance-503-transparent.png') }}"
                alt="Estação espacial realista com um painel solar quebrado e dobrado, com um astronauta realizando manutenção."
                width="1254"
                height="1254"
                fetchpriority="high"
                decoding="async"
            >
        </section>
    </main>
</body>
</html>
