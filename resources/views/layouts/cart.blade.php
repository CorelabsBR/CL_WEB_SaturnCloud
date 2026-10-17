<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0b0f14">
    <meta name="description" content="@yield('meta_description', 'Revise os itens e valores do seu pedido VortexHost.')">
    <title>@yield('title', 'VortexHost')</title>

    <link rel="icon" type="image/png" href="{{ asset('img/vortex-logo-mark.png') }}">

    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('css/cart.css') }}">
    <link rel="stylesheet" href="{{ asset('css/suporte.css') }}?v={{ filemtime(public_path('css/suporte.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/notifications.css') }}?v={{ filemtime(public_path('css/notifications.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}?v={{ filemtime(public_path('css/header.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/system.css') }}?v={{ filemtime(public_path('css/system.css')) }}">


    @stack('styles')
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

@include('partials.support-widget')

<script src="{{ asset('js/notifications.js') }}?v={{ filemtime(public_path('js/notifications.js')) }}"></script>

@stack('scripts')

</body>
</html>
