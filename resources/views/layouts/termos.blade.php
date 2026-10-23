<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#050505">
    <meta name="description" content="@yield('meta_description', 'Políticas e termos aplicáveis aos serviços da Saturn Cloud.')">
    <title>@yield('title', 'Saturn Cloud')</title>

    <link rel="icon" type="image/png" sizes="128x128" href="{{ asset('img/saturno.svg') }}">

    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/serviceterms.css') }}">
    <link rel="stylesheet" href="{{ asset('css/privacidade.css') }}">
    <link rel="stylesheet" href="{{ asset('css/reembolso.css') }}">
    <link rel="stylesheet" href="{{ asset('css/notifications.css') }}?v={{ filemtime(public_path('css/notifications.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}?v={{ filemtime(public_path('css/header.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/system.css') }}?v={{ filemtime(public_path('css/system.css')) }}">


    @stack('styles')
    <link rel="preload" href="{{ asset('fonts/manrope-regular.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/cinematic.css') }}?v={{ filemtime(public_path('css/cinematic.css')) }}">
    @vite('resources/js/app.js')
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


<script src="{{ asset('js/notifications.js') }}?v={{ filemtime(public_path('js/notifications.js')) }}"></script>

@stack('scripts')

</body>
</html>
