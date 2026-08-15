<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Bari Tienda | Inicio' }}</title>

    {{-- Open Graph / WhatsApp --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:title" content="{{ config('app.name') }} — Tienda online en Bariloche">
    <meta property="og:description" content="Comprá cigarrillos, bebidas, snacks y más con entrega rápida en Bariloche.">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ config('app.url') }}/images/og-image.png">
    <meta property="og:locale" content="es_AR">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') }}">
    @stack('og_tags')

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon-arg.svg') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#6a31df">
    <link rel="apple-touch-icon" href="https://ui-avatars.com/api/?name=B&size=192&background=5b27ba&color=fff&bold=true">
    <link rel="preconnect" href="https://fonts.bunny.net">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.ga4')
    
    @stack('styles')
</head>

<body class="{{ $bodyClass ?? 'bg-gray-100' }}">

    @include('layouts.front-navigation')

    {{ $slot }}

    @stack('scripts')
</body>

</html>
