<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'VortexHost')</title>

    <link rel="icon" type="image/png" href="{{ asset('img/logo_vtxhost.png') }}">

    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}?v={{ filemtime(public_path('css/header.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/serviceterms.css') }}">
    <link rel="stylesheet" href="{{ asset('css/privacidade.css') }}">
    <link rel="stylesheet" href="{{ asset('css/reembolso.css') }}">
    <link rel="stylesheet" href="{{ asset('css/suporte.css') }}?v={{ filemtime(public_path('css/suporte.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/notifications.css') }}?v={{ filemtime(public_path('css/notifications.css')) }}">


    @stack('styles')
</head>

<body class="@yield('body_class')">

@if (!View::hasSection('hide_header'))
    @include('partials.header')
@endif

<main class="@yield('main_class')">
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
