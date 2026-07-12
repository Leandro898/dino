<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout - Marketplace Bariloche</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon-arg.svg') }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] antialiased">

    @include('partials.header')

    <main class="max-w-7xl mx-auto px-6 py-10 lg:py-20 pb-36 md:pb-10">



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

                <!-- DATOS DEL COMPRADOR + CARRITO -->
                <div class="lg:col-span-2 space-y-8">

                    <!-- RESUMEN DEL CARRITO EDITABLE (NUEVO) -->
                    @php $cart = session()->get('cart', []); @endphp
                    @if (!empty($cart))
                    <div class="bg-white dark:bg-[#161615] p-6 rounded-2xl shadow-sm">
                        <h2 class="text-xl font-bold mb-6 dark:text-white">
                            📦 Tu Pedido
                        </h2>

                        <div class="hidden md:grid grid-cols-6 font-bold text-sm uppercase tracking-widest text-gray-400 pb-4 border-b">
                            <div class="col-span-3">Producto</div>
                            <div>Precio</div>
                            <div>Cantidad</div>
                            <div>Subtotal</div>
                        </div>

                        @foreach ($cart as $id => $details)
                            @php
                                $subtotal = $details['price'] * $details['quantity'];
                                $total += $subtotal;
                            @endphp

                            <div class="grid grid-cols-1 md:grid-cols-6 items-center gap-4 py-6 border-b last:border-0">
                                <!-- PRODUCTO -->
                                <div class="md:col-span-3 flex items-center gap-4">
                                    @if ($details['image'])
                                        <img src="{{ asset('storage/' . $details['image']) }}"
                                            class="w-20 h-20 object-cover rounded-xl">
                                    @endif

                                    <div>
                                        <h3 class="font-bold dark:text-white text-sm md:text-base">
                                            {{ $details['name'] }}
                                        </h3>

                                        <form action="{{ route('cart.remove', $id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-xs text-gray-400 hover:text-red-500 mt-2">
                                                ❌ Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- PRECIO -->
                                <div class="font-semibold text-sm md:text-base">
                                    ${{ number_format($details['price'], 0, ',', '.') }}
                                </div>

                                <!-- CANTIDAD -->
                                <div class="flex items-center gap-2">
                                        <form action="{{ route('cart.update', $id) }}" method="POST" class="flex items-center gap-2">
                                            @csrf
                                            <button type="button" onclick="decreaseQuantity(this)"
                                                class="w-6 h-6 md:w-8 md:h-8 rounded-lg bg-gray-100 dark:bg-[#0f0f0f] hover:bg-gray-200 dark:hover:bg-[#1a1a1a] flex items-center justify-center font-bold transition-colors text-sm">
                                                −
                                            </button>
                                            <input type="hidden" name="quantity" class="quantity-input"
                                                value="{{ $details['quantity'] }}">
                                            <span class="quantity-display w-6 text-center font-semibold text-sm">{{ $details['quantity'] }}</span>
                                            <button type="button" onclick="increaseQuantity(this)"
                                                class="w-6 h-6 md:w-8 md:h-8 rounded-lg bg-gray-100 dark:bg-[#0f0f0f] hover:bg-gray-200 dark:hover:bg-[#1a1a1a] flex items-center justify-center font-bold transition-colors text-sm">
                                                +
                                            </button>
                                            <button type="submit" class="hidden submit-btn">Actualizar</button>
                                        </form>
                                </div>

                                <!-- SUBTOTAL -->
                                <div class="font-bold text-sm md:text-base">
                                    ${{ number_format($subtotal, 0, ',', '.') }}
                                </div>
                            </div>
                        @endforeach

                    </div>
                    @endif

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
                                <label class="text-sm font-semibold">Teléfono</label>
                                <input type="text" name="phone" value="{{ old('phone') }}" required
                                    class="w-full mt-2 p-3 rounded-xl border border-gray-200 dark:border-[#2a2a2a] dark:bg-[#0f0f0f] dark:text-white dark:placeholder-gray-500">
                            </div>

                            <div class="md:col-span-2">
                                <label class="text-sm font-semibold">Email</label>
                                <input type="email" name="email" value="{{ old('email') }}"
                                    placeholder="tu@email.com"
                                    class="w-full mt-2 p-3 rounded-xl border border-gray-200 dark:border-[#2a2a2a] dark:bg-[#0f0f0f] dark:text-white dark:placeholder-gray-500">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

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

                            <div class="md:col-span-2">
                                <p class="text-sm font-semibold">Ubicación estimada en el mapa</p>
                                <div id="checkout-map" class="mt-2 h-64 w-full rounded-xl border border-gray-200 relative z-10" style="height: 16rem;"></div>
                                <p id="checkout-map-status" class="mt-2 text-xs text-gray-500">
                                    Ingresá calle y altura para ver la ubicación estimada.
                                </p>
                            </div>

                            {{-- Dirección completa compuesta (oculta, para guardar en BD) --}}
                            <input type="hidden" id="address-hidden" name="address" value="{{ old('address') }}">
                            <input type="hidden" id="shipping-zone-hidden" name="shipping_zone" value="{{ old('shipping_zone') }}">

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



                        <div class="space-y-4">

                            <label id="label-mercadopago"
                                class="flex items-start gap-4 p-4 rounded-2xl border border-gray-200 dark:border-[#2a2a2a] cursor-pointer transition-colors">
                                <input type="radio" name="payment_method" id="pm-mercadopago" value="mercadopago" class="sr-only"
                                    {{ !empty($onlyMercadoPago) || old('payment_method', 'mercadopago') === 'mercadopago' ? 'checked' : '' }}>
                                <span id="dot-mercadopago" class="flex-shrink-0 mt-1 w-4 h-4 rounded-full border-2 border-gray-300 transition-colors"></span>
                                <span>
                                    <span class="block font-semibold dark:text-white">Mercado Pago</span>
                                    <span class="block text-sm text-gray-500 dark:text-gray-400">Pagás online en el momento con tarjeta, débito, crédito o dinero en cuenta. La orden queda confirmada cuando Mercado Pago aprueba el pago.</span>
                                </span>
                            </label>

                            @if (!empty($manualWhatsAppPaymentEnabled))
                                <label class="flex items-start gap-4 p-4 rounded-2xl border border-gray-200 dark:border-[#2a2a2a] cursor-pointer transition-colors">
                                    <input type="radio" name="payment_method" id="pm-transferencia" value="transferencia" class="sr-only"
                                        {{ old('payment_method') === 'transferencia' ? 'checked' : '' }}>
                                    <span id="dot-transferencia" class="flex-shrink-0 mt-1 w-4 h-4 rounded-full border-2 border-gray-300 transition-colors"></span>
                                    <span>
                                        <span class="block font-semibold dark:text-white">Efectivo o transferencia por WhatsApp</span>
                                        <span class="block text-sm text-gray-500 dark:text-gray-400">Te redirigimos a WhatsApp con el detalle del pedido para coordinar el pago y la entrega.</span>
                                    </span>
                                </label>
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
                            <span id="summary-subtotal" data-value="{{ $total }}">${{ number_format($total, 0, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between mt-3 text-sm font-semibold text-gray-700 dark:text-gray-300">
                            <span>Envío</span>
                            <span id="summary-shipping">
                                @if (!is_null($selectedShippingCost))
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
                            class="w-full mt-8 bg-black hover:bg-gradient-to-r hover:from-purple-600 hover:to-purple-700 transition-colors text-white py-4 rounded-xl font-bold uppercase text-sm mb-36 md:mb-0">
                            Confirmar pedido
                        </button>

                    @endif

                </div>

            </div>

        </form>

    </main>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
            (() => {
                const mpInput      = document.getElementById('pm-mercadopago');
                const dotMP        = document.getElementById('dot-mercadopago');
                const dotTransf    = document.getElementById('dot-transferencia');
                const transferInput = document.getElementById('pm-transferencia');
                if (!mpInput) return;

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
                    const transfChecked = transferInput && transferInput.checked;

                    setDot(dotMP, mpChecked);
                    setDot(dotTransf, transfChecked);
                }

                // Al seleccionar MP
                if (mpInput) {
                    mpInput.addEventListener('change', () => {
                        if (transferInput) transferInput.checked = false;
                        syncAll();
                    });
                }

                if (transferInput) {
                    transferInput.addEventListener('change', () => {
                        if (mpInput) mpInput.checked = false;
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
            const mapElement    = document.getElementById('checkout-map');
            const mapStatus     = document.getElementById('checkout-map-status');
            const shippingZones = @json($shippingZones);

            if (!streetInput || !numberInput || !zoneHidden || !msgBox || !addressHidden || !datalist) return;

            let map = null;
            let marker = null;
            let geocodeTimeout = null;
            let geocodeController = null;

            function setMapStatus(text, isError = false) {
                if (!mapStatus) return;

                if (!text) {
                    mapStatus.classList.add('hidden');
                    return;
                }

                mapStatus.textContent = text;
                mapStatus.classList.remove('hidden');
                mapStatus.classList.toggle('text-red-500', isError);
                mapStatus.classList.toggle('text-gray-500', !isError);
            }

            function ensureMap() {
                if (!mapElement || map) return;

                if (typeof window.L === 'undefined') {
                    setMapStatus('No se pudo cargar el mapa en este momento.', true);
                    return;
                }

                map = L.map(mapElement, {
                    zoomControl: true,
                }).setView([-41.1335, -71.3103], 12);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap',
                    maxZoom: 19,
                }).addTo(map);

                setTimeout(() => map.invalidateSize(), 0);
            }

            function updateMapPin(lat, lon, label) {
                ensureMap();
                if (!map) return;

                const coords = [lat, lon];

                if (!marker) {
                    marker = L.marker(coords).addTo(map);
                } else {
                    marker.setLatLng(coords);
                }

                if (label) {
                    marker.bindPopup(label);
                }

                map.setView(coords, 16);
            }

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

            async function geocodeAddress() {
                const street = streetInput.value.trim();
                const number = numberInput.value.trim();

                if (street.length < 3 || number.length < 1) {
                    setMapStatus('Ingresá calle y altura para ver la ubicación estimada.');
                    return;
                }

                // Con dirección completa, ocultamos la leyenda de ayuda.
                setMapStatus('');

                clearTimeout(geocodeTimeout);
                geocodeTimeout = setTimeout(async () => {
                    if (geocodeController) {
                        geocodeController.abort();
                    }

                    geocodeController = new AbortController();
                    const query = `${street} ${number}, San Carlos de Bariloche, Rio Negro, Argentina`;

                    try {
                        const url = new URL('https://nominatim.openstreetmap.org/search');
                        url.searchParams.set('format', 'json');
                        url.searchParams.set('limit', '1');
                        url.searchParams.set('countrycodes', 'ar');
                        url.searchParams.set('q', query);

                        const res = await fetch(url.toString(), {
                            headers: { Accept: 'application/json' },
                            signal: geocodeController.signal,
                        });

                        if (!res.ok) {
                            throw new Error('geocode-failed');
                        }

                        const places = await res.json();
                        const match = Array.isArray(places) ? places[0] : null;

                        if (!match) {
                            setMapStatus('No pudimos ubicar esa dirección en el mapa. Revisá calle y altura.', true);
                            return;
                        }

                        const lat = parseFloat(match.lat);
                        const lon = parseFloat(match.lon);

                        if (!Number.isFinite(lat) || !Number.isFinite(lon)) {
                            setMapStatus('No pudimos ubicar esa dirección en el mapa. Revisá calle y altura.', true);
                            return;
                        }

                        updateMapPin(lat, lon, match.display_name || `${street} ${number}`);
                        setMapStatus('');
                    } catch (error) {
                        if (error.name === 'AbortError') return;
                        setMapStatus('No se pudo actualizar el mapa en este momento.', true);
                    }
                }, 800);
            }

            const initialZoneKey = zoneHidden.value;
            if (initialZoneKey && shippingZones[initialZoneKey]) {
                updateTotals(shippingZones[initialZoneKey].price);
            }

            streetInput.addEventListener('input', () => {
                fetchStreetSuggestions();
                detectZone();
                geocodeAddress();
            });
            numberInput.addEventListener('input', () => {
                detectZone();
                geocodeAddress();
            });
            streetInput.addEventListener('blur',  updateAddressHidden);
            numberInput.addEventListener('blur',  updateAddressHidden);

            ensureMap();
            geocodeAddress();
        })();

        // Funciones para editar cantidades del carrito
        function increaseQuantity(button) {
            const form = button.closest('form');
            const input = form.querySelector('.quantity-input');
            const display = form.querySelector('.quantity-display');
            let quantity = parseInt(input.value);
            quantity++;
            input.value = quantity;
            display.textContent = quantity;
            form.querySelector('.submit-btn').click();
        }

        function decreaseQuantity(button) {
            const form = button.closest('form');
            const input = form.querySelector('.quantity-input');
            const display = form.querySelector('.quantity-display');
            let quantity = parseInt(input.value);
            if (quantity > 1) {
                quantity--;
                input.value = quantity;
                display.textContent = quantity;
                form.querySelector('.submit-btn').click();
            }
        }
    </script>

</body>

</html>
