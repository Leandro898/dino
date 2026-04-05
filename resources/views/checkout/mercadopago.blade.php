<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pago - Marketplace Bariloche</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] antialiased">

    @include('partials.header')

    <main class="max-w-3xl mx-auto px-6 py-10 lg:py-20 text-center">
        <div class="bg-white dark:bg-[#161615] p-10 rounded-3xl shadow-sm border border-gray-100 dark:border-[#2a2a2a]">

            <div class="mb-6 flex justify-center">
                <div class="bg-green-100 dark:bg-green-900/30 p-4 rounded-full">
                    <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>

            <h1 class="text-3xl font-black dark:text-white uppercase mb-2 tracking-tighter">
                ¡Orden Recibida!
            </h1>
            <p class="text-gray-500 dark:text-gray-400 mb-8">
                Solo falta un paso. Hacé clic abajo para pagar de forma segura con Mercado Pago.
            </p>

            <div class="bg-[#f9f9f9] dark:bg-[#0f0f0f] p-4 rounded-2xl mb-8 flex justify-between items-center">
                <span class="font-semibold">Total a pagar:</span>
                <span
                    class="text-2xl font-bold text-purple-700 dark:text-purple-400">${{ number_format($order->total, 0, ',', '.') }}</span>
            </div>

            <div id="wallet_container"></div>

            <div class="mt-8 pt-6 border-t dark:border-[#2a2a2a]">
                <a href="{{ route('home') }}"
                    class="text-sm font-bold uppercase tracking-widest text-gray-400 hover:text-black transition-colors">
                    Cancelar y volver al inicio
                </a>
            </div>
        </div>
    </main>

    <script src="https://sdk.mercadopago.com/js/v2"></script>
    <script>
        const mp = new MercadoPago("{{ config('mercadopago.public_key') }}");
        const bricksBuilder = mp.bricks();

        bricksBuilder.create("wallet", "wallet_container", {
            initialization: {
                preferenceId: "{{ $preferenceId }}",
                redirectMode: "self"
            },
            customization: {
                texts: {
                    valueProp: 'smart_option',
                    action: 'pay',
                },
            },
        });
    </script>
</body>

</html>
