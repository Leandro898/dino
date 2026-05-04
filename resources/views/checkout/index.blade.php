<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout - Marketplace Bariloche</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon-arg.svg') }}">
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
                                    class="w-full mt-2 p-3 rounded-xl border border-gray-200 dark:border-[#2a2a2a] dark:bg-[#0f0f0f] dark:text-white dark:placeholder-gray-500">
                            </div>

                            <div>
                                <label class="text-sm font-semibold">Email</label>
                                <input type="email" name="email" value="{{ old('email') }}" required
                                    placeholder="tu@email.com"
                                    class="w-full mt-2 p-3 rounded-xl border border-gray-200 dark:border-[#2a2a2a] dark:bg-[#0f0f0f] dark:text-white dark:placeholder-gray-500">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            @if(!$raffleOnlyMercadoPago)
                            <div>
                                <label class="text-sm font-semibold">Calle</label>
                                <input type="text" id="street-name-input" name="street_name"
                                    value="{{ old('street_name') }}" required autocomplete="off"
                                    list="street-suggestions-list"
                                    placeholder="Ej: Albarracín"
                                    class="w-full mt-2 p-3 rounded-xl border border-gray-200 dark:border-[#2a2a2a] dark:bg-[#0f0f0f] dark:text-white dark:placeholder-gray-500">
                                <datalist id="street-suggestions-list"></datalist>
                                @error('street_name')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="text-sm font-semibold">Altura (número)</label>
                                <input type="number" id="street-number-input" name="street_number"
                                    value="{{ old('street_number') }}" required min="1"
                                    placeholder="Ej: 1430"
                                    class="w-full mt-2 p-3 rounded-xl border border-gray-200 dark:border-[#2a2a2a] dark:bg-[#0f0f0f] dark:text-white dark:placeholder-gray-500">
                                @error('street_number')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Dirección completa compuesta (oculta, para guardar en BD) --}}
                            <input type="hidden" id="address-hidden" name="address" value="{{ old('address') }}">
                            <input type="hidden" id="shipping-zone-hidden" name="shipping_zone" value="{{ old('shipping_zone') }}">
                            @endif

                            <div>
                                <label class="text-sm font-semibold">Teléfono</label>
                                <input type="text" name="phone" value="{{ old('phone') }}" required
                                    class="w-full mt-2 p-3 rounded-xl border border-gray-200 dark:border-[#2a2a2a] dark:bg-[#0f0f0f] dark:text-white dark:placeholder-gray-500">
                            </div>

                            <div class="md:col-span-2">
                                <div id="zone-detection-msg" class="hidden mb-1 p-3 rounded-xl text-sm font-medium"></div>
                                @error('shipping_zone')
                                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>

                    </div>

                    <div class="bg-white dark:bg-[#161615] p-6 rounded-2xl shadow-sm">

                        <h2 class="text-xl font-bold mb-6 dark:text-white">
                            Método de pago
                        </h2>

                        @if (!empty($raffleOnlyMercadoPago))
                            <div class="mb-4 rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-800 px-4 py-3 text-sm font-medium">
                                Este pedido incluye un numero de sorteo. Solo está disponible Mercado Pago.
                            </div>
                        @endif

                        <div class="space-y-4">

                            <label id="label-mercadopago"
                                class="flex items-start gap-4 p-4 rounded-2xl border border-gray-200 dark:border-[#2a2a2a] cursor-pointer transition-colors">
                                <input type="radio" name="payment_method" id="pm-mercadopago" value="mercadopago" class="sr-only"
                                    {{ !empty($raffleOnlyMercadoPago) || old('payment_method', 'mercadopago') === 'mercadopago' ? 'checked' : '' }}>
                                <span id="dot-mercadopago" class="flex-shrink-0 mt-1 w-4 h-4 rounded-full border-2 border-gray-300 transition-colors"></span>
                                <span>
                                    <span class="block font-semibold dark:text-white">Mercado Pago</span>
                                    <span class="block text-sm text-gray-500 dark:text-gray-400">Pagás online en el momento y la orden queda confirmada cuando Mercado Pago aprueba el pago.</span>
                                </span>
                            </label>

                            @if (empty($raffleOnlyMercadoPago))
                                <div id="manual-payment-card"
                                    class="rounded-2xl border border-gray-200 dark:border-[#2a2a2a] overflow-hidden transition-colors">
                                    <button type="button" id="manual-payment-toggle"
                                        class="w-full flex items-center gap-4 p-4 text-left hover:bg-gray-50 dark:hover:bg-[#1a1a1a] transition-colors">
                                        <span id="manual-indicator"
                                            class="flex-shrink-0 w-4 h-4 rounded-full border-2 border-gray-300 dark:border-gray-600 transition-colors mt-0.5" style="box-sizing:border-box;"></span>
                                        <span>
                                            <span class="block font-semibold dark:text-white">Pago al recibir</span>
                                            <span class="block text-sm text-gray-500 dark:text-gray-400">Reservamos tu pedido y coordinamos el pago cuando te lo entreguemos.</span>
                                        </span>
                                    </button>

                                    <div id="manual-payment-options"
                                        class="{{ in_array(old('payment_method'), ['efectivo', 'transferencia']) ? '' : 'hidden' }} border-t border-gray-100 dark:border-[#2a2a2a] px-4 pb-4 pt-3 space-y-3">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">¿Cómo preferís pagar?</p>

                                        <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-100 dark:border-[#2a2a2a] cursor-pointer transition-colors">
                                            <input type="radio" name="payment_method" id="pm-efectivo" value="efectivo" class="sr-only"
                                                {{ old('payment_method') === 'efectivo' ? 'checked' : '' }}>
                                            <span id="dot-efectivo" class="flex-shrink-0 mt-0.5 w-4 h-4 rounded-full border-2 border-gray-300 transition-colors"></span>
                                            <span>
                                                <span class="block font-semibold text-sm dark:text-white">Efectivo</span>
                                                <span class="block text-xs text-gray-500 dark:text-gray-400">Abonás en efectivo al momento de la entrega.</span>
                                            </span>
                                        </label>

                                        <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-100 dark:border-[#2a2a2a] cursor-pointer transition-colors">
                                            <input type="radio" name="payment_method" id="pm-transferencia" value="transferencia" class="sr-only"
                                                {{ old('payment_method') === 'transferencia' ? 'checked' : '' }}>
                                            <span id="dot-transferencia" class="flex-shrink-0 mt-0.5 w-4 h-4 rounded-full border-2 border-gray-300 transition-colors"></span>
                                            <span>
                                                <span class="block font-semibold text-sm dark:text-white">Transferencia bancaria</span>
                                                <span class="block text-xs text-gray-500 dark:text-gray-400">Te mostramos los datos para transferir desde cualquier app bancaria o billetera.</span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            @endif

                            @if (!empty($raffleOnlyMercadoPago))
                                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                                    El dia del sorteo, el premio sera entregado por uno de los repartidores de BariTienda al ganador.
                                </div>

                                <div class="rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-900">
                                    <p class="font-semibold mb-1">Regla de transparencia</p>
                                    <p>Si no se venden los 100 numeros, el sorteo se realiza igual y participan solo los numeros vendidos.</p>
                                    <p class="mt-1">Para asegurar ganador: si el primer numero oficial no fue vendido, se toma el siguiente puesto oficial hasta encontrar un numero vendido.</p>
                                </div>
                            @endif

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
                            $selectedShippingCost = !empty($freeShippingForSpecificRaffle)
                                ? 0
                                : ($selectedShippingZone && isset($shippingZones[$selectedShippingZone])
                                    ? (float) $shippingZones[$selectedShippingZone]['price']
                                    : null);
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
                            <span id="summary-subtotal" data-value="{{ $total }}">${{ number_format($total, 0, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between mt-3 text-sm font-semibold text-gray-700 dark:text-gray-300">
                            <span>Envío</span>
                            <span id="summary-shipping">
                                @if (!empty($freeShippingForSpecificRaffle))
                                    Gratis (solo sorteo)
                                @elseif (!is_null($selectedShippingCost))
                                    ${{ number_format($selectedShippingCost, 0, ',', '.') }}
                                @else
                                    Seleccioná zona
                                @endif
                            </span>
                        </div>

                        <div class="flex justify-between mt-3 pt-3 border-t text-lg font-bold dark:text-white">
                            <span>Total</span>
                            <span id="summary-total">
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
                const dotMP        = document.getElementById('dot-mercadopago');
                const dotEfectivo  = document.getElementById('dot-efectivo');
                const dotTransf    = document.getElementById('dot-transferencia');
                if (!toggle || !options || !indicator || !card) return;

                const manualInputs = options.querySelectorAll('input[type="radio"]');

                function setDot(el, active) {
                    if (!el) return;
                    if (active) {
                        el.style.borderColor = '#9333ea';
                        el.style.backgroundColor = '#9333ea';
                    } else {
                        el.style.borderColor = '';
                        el.style.backgroundColor = '';
                    }
                }

                function syncAll() {
                    const mpChecked     = mpInput && mpInput.checked;
                    const efecChecked   = document.getElementById('pm-efectivo')?.checked;
                    const transfChecked = document.getElementById('pm-transferencia')?.checked;
                    const anyManual     = efecChecked || transfChecked;

                    setDot(dotMP, mpChecked);
                    setDot(dotEfectivo, efecChecked);
                    setDot(dotTransf, transfChecked);

                    // indicador del botón "Pago al recibir"
                    if (anyManual) {
                        indicator.style.borderColor = '#9333ea';
                        indicator.style.backgroundColor = '#9333ea';
                        card.classList.add('border-purple-500');
                        card.classList.remove('border-gray-200');
                    } else {
                        indicator.style.borderColor = '';
                        indicator.style.backgroundColor = '';
                        card.classList.remove('border-purple-500');
                        card.classList.add('border-gray-200');
                    }
                }

                // Abrir/cerrar sub-opciones
                toggle.addEventListener('click', () => {
                    const isHidden = options.classList.toggle('hidden');
                    if (!isHidden && mpInput && mpInput.checked) {
                        mpInput.checked = false;
                    }
                    syncAll();
                });

                // Al seleccionar sub-opción
                manualInputs.forEach(input => {
                    input.addEventListener('change', () => {
                        if (mpInput) mpInput.checked = false;
                        syncAll();
                    });
                });

                // Al seleccionar MP
                if (mpInput) {
                    mpInput.addEventListener('change', () => {
                        manualInputs.forEach(i => { i.checked = false; });
                        options.classList.add('hidden');
                        syncAll();
                    });
                }

                // Estado inicial
                syncAll();
            })();
    </script>

    <script>
        // ── Auto-detección de zona de envío ──────────────────────────────────
        (() => {
            const streetInput   = document.getElementById('street-name-input');
            const numberInput   = document.getElementById('street-number-input');
            const zoneHidden    = document.getElementById('shipping-zone-hidden');
            const msgBox        = document.getElementById('zone-detection-msg');
            const addressHidden = document.getElementById('address-hidden');
            const datalist      = document.getElementById('street-suggestions-list');
            const shippingZones = @json($shippingZones);
            const freeShippingForSpecificRaffle = @json(!empty($freeShippingForSpecificRaffle));

            if (!streetInput || !numberInput || !zoneHidden || !msgBox || !addressHidden || !datalist) return;

            function updateAddressHidden() {
                const s = streetInput.value.trim();
                const n = numberInput.value.trim();
                addressHidden.value = s + (n ? ' ' + n : '');
            }

            function showMsg(type, text) {
                msgBox.className = 'mb-3 p-3 rounded-xl text-sm font-medium border';
                msgBox.classList.add(
                    type === 'success' ? 'bg-green-50'  : 'bg-amber-50',
                    type === 'success' ? 'text-green-700' : 'text-amber-700',
                    type === 'success' ? 'border-green-200' : 'border-amber-200'
                );
                msgBox.textContent = text;
                msgBox.classList.remove('hidden');
            }

            function hideMsg() {
                msgBox.classList.add('hidden');
            }

            function updateTotals(price) {
                const subtotalEl = document.getElementById('summary-subtotal');
                const shippingEl = document.getElementById('summary-shipping');
                const totalEl    = document.getElementById('summary-total');
                if (!subtotalEl || !shippingEl || !totalEl) return;

                const subtotal = parseInt(subtotalEl.dataset.value || 0, 10);
                if (freeShippingForSpecificRaffle) {
                    shippingEl.textContent = 'Gratis (solo sorteo)';
                    totalEl.textContent = '$' + subtotal.toLocaleString('es-AR');
                    return;
                }

                if (price !== null) {
                    shippingEl.textContent = '$' + price.toLocaleString('es-AR');
                    totalEl.textContent    = '$' + (subtotal + price).toLocaleString('es-AR');
                } else {
                    shippingEl.textContent = 'Completá tu dirección';
                    totalEl.textContent    = '$' + subtotal.toLocaleString('es-AR');
                }
            }

            let detectTimeout = null;
            let suggestTimeout = null;

            if (freeShippingForSpecificRaffle) {
                updateAddressHidden();
                updateTotals(0);
                hideMsg();
                return;
            }

            async function fetchStreetSuggestions() {
                const term = streetInput.value.trim();

                if (term.length < 2) {
                    datalist.innerHTML = '';
                    return;
                }

                clearTimeout(suggestTimeout);
                suggestTimeout = setTimeout(async () => {
                    try {
                        const url = new URL('{{ route("shipping.street-suggestions") }}', window.location.origin);
                        url.searchParams.set('q', term);

                        const res = await fetch(url.toString(), { headers: { Accept: 'application/json' } });
                        const data = await res.json();
                        const items = Array.isArray(data.suggestions) ? data.suggestions : [];

                        datalist.innerHTML = '';
                        items.forEach((street) => {
                            const option = document.createElement('option');
                            option.value = street;
                            datalist.appendChild(option);
                        });
                    } catch (_) {
                        // silencioso
                    }
                }, 200);
            }

            async function detectZone() {
                const street = streetInput.value.trim();
                const number = numberInput.value.trim();
                updateAddressHidden();
                if (!street) { hideMsg(); return; }

                clearTimeout(detectTimeout);
                detectTimeout = setTimeout(async () => {
                    try {
                        const url = new URL('{{ route("shipping.detect-zone") }}', window.location.origin);
                        url.searchParams.set('street', street);
                        if (number) url.searchParams.set('number', number);

                        const res  = await fetch(url.toString(), { headers: { Accept: 'application/json' } });
                        const data = await res.json();

                        if (data.zone_key) {
                            zoneHidden.value = data.zone_key;
                            showMsg('success', 'Zona detectada: ' + data.zone_label + ' - Envio: $' + data.zone_price.toLocaleString('es-AR'));
                            updateTotals(data.zone_price);
                        } else {
                            zoneHidden.value = '';
                            showMsg('warning', 'No encontramos esa calle y altura. Revisa los datos para calcular el envio.');
                            updateTotals(null);
                        }
                    } catch (_) { /* silencioso */ }
                }, 600);
            }

            const initialZoneKey = zoneHidden.value;
            if (initialZoneKey && shippingZones[initialZoneKey]) {
                updateTotals(shippingZones[initialZoneKey].price);
            }

            streetInput.addEventListener('input', () => {
                fetchStreetSuggestions();
                detectZone();
            });
            numberInput.addEventListener('input', detectZone);
            streetInput.addEventListener('blur',  updateAddressHidden);
            numberInput.addEventListener('blur',  updateAddressHidden);
        })();
    </script>

</body>

</html>
