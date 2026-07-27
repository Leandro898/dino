<x-front-layout bodyClass="home-body">
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        @vite(['resources/css/home.css'])
    @endpush

    <main class="w-full max-w-[1920px] mx-auto px-4 md:px-8 xl:px-12 py-10 lg:py-16 pb-36 md:pb-10">



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

        @php $cart = session()->get('cart', []); @endphp
        @foreach ($cart as $id => $details)
            <form id="remove-form-{{ $id }}" action="{{ route('cart.remove', $id) }}" method="POST" class="hidden">
                @csrf
            </form>
            <form id="update-form-{{ $id }}" action="{{ route('cart.update', $id) }}" method="POST" class="hidden">
                @csrf
                <input type="hidden" name="quantity" id="quantity-input-{{ $id }}" value="{{ $details['quantity'] }}">
            </form>
        @endforeach

        <form action="{{ route('checkout.process') }}" method="POST" id="checkout-form">
            @csrf

            <div id="step-1" class="max-w-5xl mx-auto">
                <div class="grid md:grid-cols-2 gap-6 h-fit">
                    <!-- 1. DATOS DE ENVÍO -->
                    <div class="bg-white dark:bg-[#161615] p-6 rounded-2xl shadow-sm h-full flex flex-col justify-between">
                        <div>
                            <h2 class="text-lg font-bold mb-4 dark:text-white">Datos de envío</h2>
                            
                            <div class="space-y-4">
                                <div>
                                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Nombre completo"
                                        class="w-full p-2.5 rounded-xl border border-gray-200 dark:border-[#2a2a2a] dark:bg-[#0f0f0f] dark:text-white text-sm">
                                </div>
                                <div>
                                    <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="Teléfono"
                                        class="w-full p-2.5 rounded-xl border border-gray-200 dark:border-[#2a2a2a] dark:bg-[#0f0f0f] dark:text-white text-sm">
                                </div>
                                <div>
                                    <input type="email" name="email" value="{{ old('email') }}" placeholder="tu@email.com"
                                        class="w-full p-2.5 rounded-xl border border-gray-200 dark:border-[#2a2a2a] dark:bg-[#0f0f0f] dark:text-white text-sm">
                                </div>

                                <hr class="border-gray-100 dark:border-gray-800 my-4">

                                <div>
                                    <input type="text" id="street-name-input" name="street_name"
                                        value="{{ old('street_name') }}" required autocomplete="off"
                                        list="street-suggestions-list" placeholder="Calle (Ej: Albarracín)"
                                        class="w-full p-2.5 rounded-xl border border-gray-200 dark:border-[#2a2a2a] dark:bg-[#0f0f0f] dark:text-white text-sm">
                                    <datalist id="street-suggestions-list"></datalist>
                                </div>
                                <div>
                                    <input type="number" id="street-number-input" name="street_number"
                                        value="{{ old('street_number') }}" required min="1"
                                        placeholder="Altura (Ej: 1430)"
                                        class="w-full p-2.5 rounded-xl border border-gray-200 dark:border-[#2a2a2a] dark:bg-[#0f0f0f] dark:text-white text-sm">
                                </div>

                                <input type="hidden" id="address-hidden" name="address" value="{{ old('address') }}">
                                <input type="hidden" id="shipping-zone-hidden" name="shipping_zone" value="{{ old('shipping_zone') }}">
                            </div>
                        </div>
                        <div id="zone-detection-msg" class="hidden text-sm mt-4"></div>
                    </div>

                    <!-- 2. MAPA -->
                    <div class="bg-white dark:bg-[#161615] p-6 rounded-2xl shadow-sm h-full flex flex-col">
                        <h2 class="text-lg font-bold mb-4 dark:text-white">Ubicación estimada</h2>
                        <div id="delivery-map" class="w-full rounded-xl border border-gray-200 relative z-10 flex-1 min-h-[320px]" style="height: 350px;"></div>
                        <p id="map-status" class="mt-2 text-xs text-gray-500">Ingresá calle y altura.</p>
                        
                        <input type="hidden" id="lat" name="lat" value="{{ old('lat', -41.133472) }}">
                        <input type="hidden" id="lng" name="lng" value="{{ old('lng', -71.310278) }}">
                    </div>
                </div>
                
                <div class="mt-8 text-right mb-20 md:mb-0">
                    <button type="button" id="btn-next-step" class="bg-black text-white px-8 py-4 rounded-xl font-bold uppercase text-sm shadow-lg hover:shadow-xl hover:bg-gradient-to-r hover:from-purple-600 hover:to-purple-700 transition-all w-full md:w-auto">
                        Continuar al pago
                    </button>
                </div>
            </div>

            <div id="step-2" class="hidden max-w-5xl mx-auto">
                <div class="mb-6">
                    <button type="button" id="btn-prev-step" class="flex items-center gap-2 text-gray-500 hover:text-black dark:hover:text-white font-bold uppercase text-sm transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                        Volver a los datos de envío
                    </button>
                </div>
                <div class="grid md:grid-cols-2 gap-6 lg:gap-10 h-fit">
                    <!-- MÉTODO DE PAGO -->
                    <div class="bg-white dark:bg-[#161615] p-6 rounded-2xl shadow-sm">
                        <h2 class="text-lg font-bold mb-4 dark:text-white">Método de pago</h2>
                        <div class="space-y-3">
                            
                            <label id="label-mercadopago" class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 dark:border-[#2a2a2a] cursor-pointer has-[:checked]:border-purple-600 has-[:checked]:bg-purple-50 dark:has-[:checked]:bg-purple-900/20 transition-colors">
                                <input type="radio" name="payment_method" id="pm-mercadopago" value="mercadopago" class="sr-only peer"
                                    {{ !empty($onlyMercadoPago) || old('payment_method', 'mercadopago') === 'mercadopago' ? 'checked' : '' }}>
                                <span id="dot-mercadopago" class="flex-shrink-0 mt-1 w-4 h-4 rounded-full border-2 border-gray-300 peer-checked:border-purple-600 peer-checked:border-[5px] transition-all"></span>
                                <span>
                                    <span class="block text-sm font-bold dark:text-white">Mercado Pago</span>
                                    <span class="block text-xs text-gray-500">Pagás online.</span>
                                </span>
                            </label>

                            @if (!empty($manualWhatsAppPaymentEnabled))
                                <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 dark:border-[#2a2a2a] cursor-pointer has-[:checked]:border-purple-600 has-[:checked]:bg-purple-50 dark:has-[:checked]:bg-purple-900/20 transition-colors">
                                    <input type="radio" name="payment_method" id="pm-transferencia" value="transferencia" class="sr-only peer"
                                        {{ old('payment_method') === 'transferencia' ? 'checked' : '' }}>
                                    <span id="dot-transferencia" class="flex-shrink-0 mt-1 w-4 h-4 rounded-full border-2 border-gray-300 peer-checked:border-purple-600 peer-checked:border-[5px] transition-all"></span>
                                    <span>
                                        <span class="block text-sm font-bold dark:text-white">Transferencia</span>
                                        <span class="block text-xs text-gray-500">Enviar comprobante por WhatsApp.</span>
                                    </span>
                                </label>

                                <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 dark:border-[#2a2a2a] cursor-pointer has-[:checked]:border-purple-600 has-[:checked]:bg-purple-50 dark:has-[:checked]:bg-purple-900/20 transition-colors">
                                    <input type="radio" name="payment_method" id="pm-efectivo" value="efectivo" class="sr-only peer"
                                        {{ old('payment_method') === 'efectivo' ? 'checked' : '' }}>
                                    <span id="dot-efectivo" class="flex-shrink-0 mt-1 w-4 h-4 rounded-full border-2 border-gray-300 peer-checked:border-purple-600 peer-checked:border-[5px] transition-all"></span>
                                    <span>
                                        <span class="block text-sm font-bold dark:text-white">Efectivo</span>
                                        <span class="block text-xs text-gray-500">Pagas al recibir tu pedido.</span>
                                    </span>
                                </label>
                            @endif

                        </div>
                    </div>

                    <!-- TU PEDIDO -->
                    <div class="bg-white dark:bg-[#161615] rounded-2xl shadow-sm p-6">
                        <h2 class="text-xl font-bold mb-6 dark:text-white flex items-center gap-2">📦 Tu pedido</h2>





                    @if (session('cart'))

                        @php
                            $selectedShippingZone = old('shipping_zone');
                            $selectedShippingCost = $selectedShippingZone && isset($shippingZones[$selectedShippingZone])
                                    ? (float) $shippingZones[$selectedShippingZone]['price']
                                    : null;
                        @endphp

                        <div class="divide-y border-b mb-6 dark:divide-gray-800 dark:border-gray-800 pb-2">
                        @foreach (session('cart') as $id => $details)
                            @php
                                $subtotal = $details['price'] * $details['quantity'];
                                $total += $subtotal;
                            @endphp

                            <div class="py-4">
                                <div class="flex items-center gap-3">
                                    @if ($details['image'])
                                        <img src="{{ asset('storage/' . $details['image']) }}"
                                            class="w-12 h-12 md:w-16 md:h-16 flex-shrink-0 object-cover rounded-lg">
                                    @endif
                                    <div class="flex-1">
                                        <h3 class="font-bold dark:text-white text-sm">
                                            {{ $details['name'] }}
                                        </h3>
                                        <div class="flex items-center justify-between mt-2">
                                            <!-- Qty Controls -->
                                            <div class="flex items-center gap-2">
                                                <button type="button" class="w-6 h-6 rounded-md bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 flex items-center justify-center btn-decrease-qty text-xs font-bold"
                                                    data-id="{{ $id }}" data-url="{{ route('cart.update', $id) }}">-</button>
                                                <span class="font-bold w-4 text-center text-xs" id="quantity-display-{{ $id }}">{{ $details['quantity'] }}</span>
                                                <button type="button" class="w-6 h-6 rounded-md bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 flex items-center justify-center btn-increase-qty text-xs font-bold"
                                                    data-id="{{ $id }}" data-url="{{ route('cart.update', $id) }}">+</button>
                                            </div>
                                            <!-- Price -->
                                            <div class="font-bold text-sm text-purple-700 dark:text-purple-400" id="item-subtotal-{{ $id }}">
                                                ${{ number_format($subtotal, 0, ',', '.') }}
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" form="remove-form-{{ $id }}" class="text-gray-400 hover:text-red-500 self-start p-1" title="Eliminar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                        </div>

                        <div class="flex justify-between mt-4 pt-4 border-t text-base font-bold dark:text-white">
                            <span>Subtotal productos</span>
                            <span id="summary-subtotal" data-value="{{ $total }}">${{ number_format($total, 0, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between items-center mt-3 pt-3 border-t text-base font-semibold text-gray-800 dark:text-gray-200">
                            <span class="flex items-center gap-1.5 text-purple-700 dark:text-purple-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                    <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7h-3v7h.05a2.5 2.5 0 004.9 0H17a1 1 0 001-1v-2.828a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7.414V7z" />
                                </svg>
                                Envío
                            </span>
                            <span id="summary-shipping" class="text-purple-700 dark:text-purple-400 font-bold">
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
                            class="w-full mt-6 bg-black hover:bg-gradient-to-r hover:from-purple-600 hover:to-purple-700 transition-colors text-white py-3 rounded-xl font-bold uppercase text-sm mb-20 md:mb-0">
                            Confirmar pedido
                        </button>

                    @endif

                    </div>
                </div>
            </div>

        </form>

        <div id="checkout-config" class="hidden" 
            data-shipping-zones='@json($shippingZones)'
            data-google-maps-key='{{ config('services.google_maps.key') }}'
            data-reverse-geocode-url='{{ route('shipping.reverse-geocode') }}'
            data-street-suggestions-url='{{ route('shipping.street-suggestions') }}'
            data-detect-zone-url='{{ route('shipping.detect-zone') }}'>
        </div>

    </main>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        @vite(['resources/js/checkout.js'])
    @endpush
</x-front-layout>
