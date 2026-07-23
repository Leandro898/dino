<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Comanda - #{{ $orderId ?? '' }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            body {
                background-color: #fff;
                color: #000;
                width: 80mm;
                margin: 0;
                padding: 0;
            }
            @page {
                margin: 0;
            }
            .no-print {
                display: none !important;
            }
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            max-width: 80mm;
            margin: 0 auto;
            padding: 10px;
            background-color: #fff;
            color: #000;
        }
    </style>
</head>
<body class="antialiased">
    {{ $slot }}
</body>
</html>
