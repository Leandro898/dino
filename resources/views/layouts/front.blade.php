<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@stack('title'){{ config('app.name') }}</title>

    {{-- Open Graph / WhatsApp --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:title" content="{{ config('app.name') }} — Tienda online en Bariloche">
    <meta property="og:description" content="Comprá cigarrillos, bebidas, snacks y más con entrega rápida en Bariloche.">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ config('app.url') }}/images/og-image.png">
    <meta property="og:locale" content="es_AR">
    @stack('og_tags')

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon-arg.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.ga4')
</head>
<body class="bg-gray-100">

    @include('layouts.front-navigation')

    <main class="max-w-7xl mx-auto p-6">
        {{ $slot }}
    </main>

</body>
</html>