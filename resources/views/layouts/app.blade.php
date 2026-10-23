<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#050505">
    <meta name="description" content="@yield('meta_description', 'Hospedagem web e infraestrutura gerenciada com suporte humano e operação no Brasil.')">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Saturn Cloud">
    <meta property="og:title" content="@yield('title', 'Saturn Cloud')">
    <meta property="og:description" content="@yield('meta_description', 'Hospedagem web e infraestrutura digital com informações comerciais claras.')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('img/Minimalist_Saturn_Orbit_Emblem.webp') }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <title>@yield('title', 'Saturn Cloud')</title>

    <link rel="icon" type="image/png" sizes="128x128" href="{{ asset('img/saturno.svg') }}">

    <link rel="stylesheet" href="{{ asset('css/base.css') }}?v={{ filemtime(public_path('css/base.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}?v={{ filemtime(public_path('css/footer.css')) }}">
    @php
        $pageStyles = request()->routeIs('home') ? ['index']
            : (request()->routeIs('vps.index') ? ['vps'] : ['index', 'responsive']);
        if (request()->routeIs('web')) $pageStyles[] = 'web';
    @endphp
    @foreach(array_unique($pageStyles) as $style)
        <link rel="stylesheet" href="{{ asset('css/'.$style.'.css') }}?v={{ filemtime(public_path('css/'.$style.'.css')) }}">
    @endforeach
    <link rel="stylesheet" href="{{ asset('css/notifications.css') }}?v={{ filemtime(public_path('css/notifications.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}?v={{ filemtime(public_path('css/header.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/system.css') }}?v={{ filemtime(public_path('css/system.css')) }}">


    @stack('styles')
    <link rel="preload" href="{{ asset('fonts/manrope-regular.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/cinematic.css') }}?v={{ filemtime(public_path('css/cinematic.css')) }}">
    @vite('resources/js/app.js')
    @php($schemaContext = '@context')
    <script type="application/ld+json">{!! json_encode([$schemaContext => 'https://schema.org', '@type' => 'Organization', 'name' => 'Saturn Cloud', 'url' => url('/'), 'logo' => asset('img/saturno_branco_quadrado.png'), 'email' => 'contato@corelabs.dev.br', 'parentOrganization' => ['@type' => 'Organization', 'name' => 'CoreLabs']], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
</head>

<body class="@yield('body_class')">
<a class="skip-link" href="#main-content">Pular para o conteúdo</a>

@if (!View::hasSection('hide_header'))
    @include('partials.header')
@endif

<main id="main-content" class="@yield('main_class')" tabindex="-1">
    @yield('content')
</main>

@if (!View::hasSection('hide_footer'))
    @include('partials.footer')
@endif

@if (!View::hasSection('hide_header') && !request()->routeIs('cart.*'))
    <a class="mobile-buy-bar" href="{{ route('vps.index') }}">Contratar agora</a>
    @if(config('services.whatsapp.number'))
        <a class="whatsapp-fab" href="https://wa.me/{{ preg_replace('/\D+/', '', config('services.whatsapp.number')) }}" target="_blank" rel="noopener" aria-label="Falar com a Saturn Cloud pelo WhatsApp">WA</a>
    @endif
@endif


<script src="{{ asset('js/notifications.js') }}?v={{ filemtime(public_path('js/notifications.js')) }}"></script>

@stack('scripts')

</body>
</html>
