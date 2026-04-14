<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>¡Gracias por tu compra! - Marketplace Bariloche</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#FDFDFC] antialiased flex flex-col min-h-screen">

    @include('partials.header')

    @php
        $paymentMethod = $checkout['payment_method'] ?? null;
        $paymentMethodLabel = $checkout['payment_method_label'] ?? 'tu método de pago';
        $orderId = $checkout['order_id'] ?? null;
        $total = $checkout['total'] ?? null;
    @endphp

    <main class="flex-grow flex items-center justify-center px-6">
        <div class="max-w-2xl w-full bg-white p-10 rounded-3xl shadow-sm text-center">
            <div
                class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h1 class="text-3xl font-black uppercase tracking-tighter mb-4">¡Pedido Recibido!</h1>
            <p class="text-gray-600 mb-8">
                Gracias por tu compra. Registramos tu pedido correctamente y el método de pago elegido fue
                <strong>{{ $paymentMethodLabel }}</strong>.
            </p>

            @if ($orderId)
                <div class="bg-gray-50 rounded-2xl p-5 mb-6 text-left">
                    <p class="text-sm text-gray-500 uppercase tracking-wide mb-2">Resumen</p>
                    <div class="space-y-2 text-sm text-gray-700">
                        <p><strong>Pedido:</strong> #{{ $orderId }}</p>
                        @if ($total)
                            <p><strong>Total:</strong> ${{ number_format($total, 0, ',', '.') }}</p>
                        @endif
                    </div>
                </div>
            @endif

            @if ($paymentMethod === 'transferencia')
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 mb-8 text-left text-sm text-amber-950">
                    <h2 class="font-bold text-base mb-3">Datos para transferir</h2>
                    @if (!empty($bankTransfer['account_holder']))
                        <p class="mb-2"><strong>Titular:</strong> {{ $bankTransfer['account_holder'] }}</p>
                    @endif
                    @if (!empty($bankTransfer['bank_name']))
                        <p class="mb-2"><strong>Banco:</strong> {{ $bankTransfer['bank_name'] }}</p>
                    @endif
                    @if (!empty($bankTransfer['alias']))
                        <p class="mb-2"><strong>Alias:</strong> {{ $bankTransfer['alias'] }}</p>
                    @endif
                    @if (!empty($bankTransfer['cbu']))
                        <p class="mb-2"><strong>CBU/CVU:</strong> {{ $bankTransfer['cbu'] }}</p>
                    @endif
                    @if (!empty($bankTransfer['notes']))
                        <p class="mb-2">{{ $bankTransfer['notes'] }}</p>
                    @endif
                    @if (empty($bankTransfer['account_holder']) && empty($bankTransfer['bank_name']) && empty($bankTransfer['alias']) && empty($bankTransfer['cbu']) && empty($bankTransfer['notes']))
                        <p>Tu pedido quedó registrado. Configurá los datos bancarios en el sistema para mostrar acá las instrucciones de transferencia.</p>
                    @endif
                </div>
            @elseif ($paymentMethod === 'efectivo')
                <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6 mb-8 text-left text-sm text-blue-950">
                    <h2 class="font-bold text-base mb-3">Pago en efectivo</h2>
                    <p>Tu pedido quedó reservado para abonarlo en efectivo al momento de la entrega.</p>
                    <p class="mt-2">Si necesitás coordinar algo adicional, te vamos a contactar usando los datos que cargaste.</p>
                </div>
            @elseif ($paymentMethod === 'mercadopago')
                <div class="bg-green-50 border border-green-200 rounded-2xl p-6 mb-8 text-left text-sm text-green-950">
                    <h2 class="font-bold text-base mb-3">Pago aprobado</h2>
                    <p>Recibimos la confirmación de Mercado Pago y tu pedido ya quedó confirmado.</p>
                </div>
            @endif

            <a href="{{ route('home') }}"
                class="inline-block w-full bg-black hover:bg-gradient-to-r hover:from-purple-600 hover:to-purple-700 transition-colors text-white py-4 rounded-xl font-bold uppercase text-sm">
                Volver a la tienda
            </a>
        </div>
    </main>

</body>

</html>
