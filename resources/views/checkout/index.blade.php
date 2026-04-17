<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout - Marketplace Bariloche</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] antialiased">

    @include('partials.header')

    <main class="max-w-7xl mx-auto px-6 py-10 lg:py-20">

        <h1 class="text-4xl font-black dark:text-white uppercase mb-10 tracking-tighter">
            Checkout
        </h1>

        @php $total = 0; @endphp

        <!-- mostrar errores -->
        @if ($errors->any())
            <div class="bg-red-500 text-white p-4 rounded-xl mb-6">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-500 text-white p-4 rounded-xl mb-6">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('checkout.process') }}" method="POST">
            @csrf

            <div class="grid lg:grid-cols-3 gap-10">

                <!-- DATOS DEL COMPRADOR -->
                <div class="lg:col-span-2 space-y-8">

                    <div class="bg-white dark:bg-[#161615] p-6 rounded-2xl shadow-sm">

                        <h2 class="text-xl font-bold mb-6 dark:text-white">
                            Datos de contacto
                        </h2>

                        <div class="grid md:grid-cols-2 gap-6">

                            <div>
                                <label class="text-sm font-semibold">Nombre completo</label>
                                <input type="text" name="name" value="{{ old('name') }}" required
                                    class="w-full mt-2 p-3 rounded-xl border border-gray-200 dark:border-[#2a2a2a] dark:bg-[#0f0f0f]">
                            </div>

                            <div>
                                <label class="text-sm font-semibold">Email</label>
                                <input type="email" name="email" value="{{ old('email') }}" required
                                    class="w-full mt-2 p-3 rounded-xl border border-gray-200 dark:border-[#2a2a2a] dark:bg-[#0f0f0f]">
                            </div>

                            <div class="md:col-span-2">
                                <label class="text-sm font-semibold">Dirección</label>
                                <input type="text" name="address" value="{{ old('address') }}" required
                                    class="w-full mt-2 p-3 rounded-xl border border-gray-200 dark:border-[#2a2a2a] dark:bg-[#0f0f0f]">
                            </div>

                            <div>
                                <label class="text-sm font-semibold">Teléfono</label>
                                <input type="text" name="phone" value="{{ old('phone') }}" required
                                    class="w-full mt-2 p-3 rounded-xl border border-gray-200 dark:border-[#2a2a2a] dark:bg-[#0f0f0f]">
                            </div>

                            <div class="md:col-span-2">
                                <label class="text-sm font-semibold">Zona de envío</label>
                                <select name="shipping_zone" required
                                    class="w-full mt-2 p-3 rounded-xl border border-gray-200 dark:border-[#2a2a2a] dark:bg-[#0f0f0f]">
                                    <option value="">Seleccioná tu zona</option>
                                    @foreach ($shippingZones as $zoneKey => $zone)
                                        <option value="{{ $zoneKey }}" {{ old('shipping_zone') === $zoneKey ? 'selected' : '' }}>
                                            {{ $zone['label'] }} - ${{ number_format($zone['price'], 0, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('shipping_zone')
                                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>

                    </div>

                    <div class="bg-white dark:bg-[#161615] p-6 rounded-2xl shadow-sm">
                        <h2 class="text-xl font-bold mb-4 dark:text-white">Tarifas de envío por zona</h2>
                        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-[#2a2a2a]">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-gray-50 dark:bg-[#111111]">
                                    <tr>
                                        <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200">Zona</th>
                                        <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200">Tarifa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($shippingZones as $zone)
                                        <tr class="border-t border-gray-100 dark:border-[#2a2a2a]">
                                            <td class="px-4 py-3 text-gray-700 dark:text-gray-200">{{ $zone['label'] }}</td>
                                            <td class="px-4 py-3 font-semibold text-gray-900 dark:text-gray-100">${{ number_format($zone['price'], 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-[#161615] p-6 rounded-2xl shadow-sm">

                        <h2 class="text-xl font-bold mb-6 dark:text-white">
                            Método de pago
                        </h2>

                        <div class="space-y-4">

                            <label id="label-mercadopago"
                                class="flex items-start gap-4 p-4 rounded-2xl border border-gray-200 dark:border-[#2a2a2a] cursor-pointer transition-colors">
                                <input type="radio" name="payment_method" id="pm-mercadopago" value="mercadopago" class="mt-1"
                                    {{ old('payment_method', 'mercadopago') === 'mercadopago' ? 'checked' : '' }}>
                                <span>
                                    <span class="block font-semibold dark:text-white">Mercado Pago</span>
                                    <span class="block text-sm text-gray-500 dark:text-gray-400">Pagás online en el momento y la orden queda confirmada cuando Mercado Pago aprueba el pago.</span>
                                </span>
                            </label>

                            <div id="manual-payment-card"
                                class="rounded-2xl border border-gray-200 dark:border-[#2a2a2a] overflow-hidden transition-colors">
                                <button type="button" id="manual-payment-toggle"
                                    class="w-full flex items-center gap-4 p-4 text-left hover:bg-gray-50 dark:hover:bg-[#1a1a1a] transition-colors">
                                    <span id="manual-indicator"
                                        class="flex-shrink-0 w-4 h-4 rounded-full border-2 border-gray-300 dark:border-gray-600 transition-colors mt-0.5"></span>
                                    <span>
                                        <span class="block font-semibold dark:text-white">Pago al recibir</span>
                                        <span class="block text-sm text-gray-500 dark:text-gray-400">Reservamos tu pedido y coordinamos el pago cuando te lo entreguemos.</span>
                                    </span>
                                </button>

                                <div id="manual-payment-options"
                                    class="{{ in_array(old('payment_method'), ['efectivo', 'transferencia']) ? '' : 'hidden' }} border-t border-gray-100 dark:border-[#2a2a2a] px-4 pb-4 pt-3 space-y-3">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">¿Cómo preferís pagar?</p>

                                    <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-100 dark:border-[#2a2a2a] cursor-pointer transition-colors">
                                        <input type="radio" name="payment_method" value="efectivo" class="mt-1"
                                            {{ old('payment_method') === 'efectivo' ? 'checked' : '' }}>
                                        <span>
                                            <span class="block font-semibold text-sm dark:text-white">Efectivo</span>
                                            <span class="block text-xs text-gray-500 dark:text-gray-400">Abonás en efectivo al momento de la entrega.</span>
                                        </span>
                                    </label>

                                    <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-100 dark:border-[#2a2a2a] cursor-pointer transition-colors">
                                        <input type="radio" name="payment_method" value="transferencia" class="mt-1"
                                            {{ old('payment_method') === 'transferencia' ? 'checked' : '' }}>
                                        <span>
                                            <span class="block font-semibold text-sm dark:text-white">Transferencia bancaria</span>
                                            <span class="block text-xs text-gray-500 dark:text-gray-400">Te mostramos los datos para transferir desde cualquier app bancaria o billetera.</span>
                                        </span>
                                    </label>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>


                <!-- RESUMEN DEL PEDIDO -->
                <div class="bg-white dark:bg-[#161615] rounded-2xl shadow-sm p-6 h-fit">

                    <h2 class="text-xl font-bold mb-6 dark:text-white">
                        Tu pedido
                    </h2>

                    @if (session('cart'))

                        @php
                            $selectedShippingZone = old('shipping_zone');
                            $selectedShippingCost = $selectedShippingZone && isset($shippingZones[$selectedShippingZone])
                                ? (float) $shippingZones[$selectedShippingZone]['price']
                                : null;
                        @endphp

                        @foreach (session('cart') as $id => $details)
                            @php
                                $subtotal = $details['price'] * $details['quantity'];
                                $total += $subtotal;
                            @endphp

                            <div class="flex justify-between text-sm mb-3">
                                <span>
                                    {{ $details['name'] }} x{{ $details['quantity'] }}
                                </span>
                                <span>
                                    ${{ number_format($subtotal, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach

                        <div class="flex justify-between mt-6 pt-4 border-t text-lg font-bold dark:text-white">
                            <span>Subtotal productos</span>
                            <span>${{ number_format($total, 0, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between mt-3 text-sm font-semibold text-gray-700 dark:text-gray-300">
                            <span>Envío</span>
                            <span>
                                @if (!is_null($selectedShippingCost))
                                    ${{ number_format($selectedShippingCost, 0, ',', '.') }}
                                @else
                                    Seleccioná zona
                                @endif
                            </span>
                        </div>

                        <div class="flex justify-between mt-3 pt-3 border-t text-lg font-bold dark:text-white">
                            <span>Total</span>
                            <span>
                                @if (!is_null($selectedShippingCost))
                                    ${{ number_format($total + $selectedShippingCost, 0, ',', '.') }}
                                @else
                                    ${{ number_format($total, 0, ',', '.') }}
                                @endif
                            </span>
                        </div>

                        <button type="submit"
                            class="w-full mt-8 bg-black hover:bg-gradient-to-r hover:from-purple-600 hover:to-purple-700 transition-colors text-white py-4 rounded-xl font-bold uppercase text-sm">
                            Confirmar pedido
                        </button>

                    @endif

                </div>

            </div>

        </form>

    </main>

    <script>
            (() => {
                const toggle       = document.getElementById('manual-payment-toggle');
                const options      = document.getElementById('manual-payment-options');
                const indicator    = document.getElementById('manual-indicator');
                const card         = document.getElementById('manual-payment-card');
                const mpInput      = document.getElementById('pm-mercadopago');
                if (!toggle || !options || !indicator || !card) {
                    return;
                }

                const manualInputs = options.querySelectorAll('input[type="radio"]');

                function syncIndicator() {
                    const anyChecked = Array.from(manualInputs).some(i => i.checked);
                    if (anyChecked) {
                        indicator.classList.replace('border-gray-300', 'border-indigo-600');
                        indicator.classList.add('bg-indigo-600');
                        card.classList.add('border-indigo-500');
                        card.classList.remove('border-gray-200');
                    } else {
                        indicator.classList.replace('border-indigo-600', 'border-gray-300');
                        indicator.classList.remove('bg-indigo-600');
                        card.classList.remove('border-indigo-500');
                        card.classList.add('border-gray-200');
                    }
                }

                // Abrir/cerrar sub-opciones
                toggle.addEventListener('click', () => {
                    const isHidden = options.classList.toggle('hidden');
                    // Si se abrió y MP estaba seleccionado, deseleccionarlo
                    if (!isHidden && mpInput && mpInput.checked) {
                        mpInput.checked = false;
                    }
                });

                // Al seleccionar sub-opción: actualizar indicador visual
                manualInputs.forEach(input => {
                    input.addEventListener('change', () => {
                        syncIndicator();
                        if (mpInput) mpInput.checked = false;
                    });
                });

                // Al seleccionar MP: cerrar sub-opciones y limpiar selección manual
                if (mpInput) {
                    mpInput.addEventListener('change', () => {
                        manualInputs.forEach(i => { i.checked = false; });
                        options.classList.add('hidden');
                        syncIndicator();
                    });
                }

                // Estado inicial (por old() en caso de error de validación)
                syncIndicator();
            })();
    </script>

</body>

</html>
