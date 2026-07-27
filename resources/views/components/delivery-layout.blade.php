<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="theme-color" content="{{ $themeColor ?? '#ffffff' }}">
    <title>{{ $title ?? 'Bari Rider' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @stack('head')

    @if($useVite ?? true)
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    @stack('styles')
</head>
<body class="{{ $bodyClass ?? '' }}" {!! $bodyAttributes ?? '' !!}>
    {{ $slot }}
    
    @stack('scripts')
</body>
</html>
