<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $product->name }} — Bari Tienda</title>

    {{-- Open Graph / WhatsApp --}}
    <meta property="og:type" content="product">
    <meta property="og:site_name" content="Bari Tienda">
    <meta property="og:title" content="{{ $product->name }} — Bari Tienda">
    <meta property="og:description"
        content="{{ $product->description ? \Illuminate\Support\Str::limit(strip_tags($product->description), 150) : 'Comprá ' . $product->name . ' con entrega rápida en Bariloche.' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $product->image_src ?: config('app.url') . '/images/og-image.png' }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="es_AR">
    @if ($product->price)
        <meta property="product:price:amount" content="{{ $product->price }}">
        <meta property="product:price:currency" content="ARS">
    @endif

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon-arg.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.ga4')
    <style>
        #qty-input::-webkit-outer-spin-button,
        #qty-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>
</head>

<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] antialiased">

    @php
        $raffleSalesEnabled = (bool) config('raffle.sales_enabled', true);
    @endphp

    @include('partials.header')

    <main class="max-w-7xl mx-auto px-6 py-10 lg:py-20">
        <div class="flex flex-col lg:flex-row gap-12 xl:gap-20 lg:items-start">

            <div class="w-full lg:w-5/12 xl:w-[40%]">
                <div
                    class="max-w-md lg:max-w-lg mx-auto rounded-3xl overflow-hidden bg-gray-100 dark:bg-[#1d1d1d] shadow-2xl">
                    @if ($product->image)
                        <img src="{{ $product->image_src }}" alt="{{ $product->name }}"
                            class="w-full h-auto object-contain">
                    @else
                        <div class="aspect-square flex items-center justify-center text-gray-400 italic bg-gray-200">
                            Sin imagen disponible
                        </div>
                    @endif
                </div>
            </div>

            <div class="w-full lg:w-7/12 xl:w-[60%] flex flex-col">
                <div class="w-full max-w-2xl mx-auto lg:mx-0">
                    <div class="mb-6">
                        <h1 class="text-4xl md:text-5xl font-black dark:text-white uppercase leading-none mb-4">
                            {{ $product->name }}
                        </h1>
                    </div>

                    <div
                        class="bg-gray-50 dark:bg-[#161615] p-6 rounded-3xl mb-8 border border-gray-100 dark:border-white/5">
                        <div class="flex items-end gap-2 mb-2">
                            <span class="text-4xl font-black text-black dark:text-white">
                                ${{ number_format($product->price, 0, ',', '.') }}
                            </span>
                            <span class="text-sm font-bold text-gray-400 uppercase mb-1">ARS</span>
                        </div>
                        <p class="text-xs text-gray-500 font-bold uppercase tracking-tight">Precio final sin comisiones
                        </p>
                    </div>

                    <div class="mb-10">
                        <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400 mb-3">Descripción</h2>
                        <p class="text-gray-600 dark:text-gray-300 leading-relaxed max-w-xl">
                            {{ filled($product->description) ? $product->description : 'El vendedor no ha proporcionado una descripción detallada para este producto.' }}
                        </p>
                    </div>

                    <div class="mt-auto space-y-4">
                        <form id="addToCartForm" action="{{ route('cart.add', $product->id) }}" method="POST">
                            @csrf

                            @if ($product->is_raffle)
                                @if (!$raffleSalesEnabled)
                                    <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                                        <p class="font-semibold">La venta de numeros del sorteo esta cerrada temporalmente.</p>
                                        <p class="mt-1">En breve se habilitara nuevamente.</p>
                                    </div>
                                @endif

                                <div class="mb-5 space-y-2">
                                    <label for="raffle-number" class="text-sm font-semibold uppercase tracking-wide text-gray-500">Numero del sorteo (000-099)</label>
                                    <input
                                        id="raffle-number"
                                        type="text"
                                        name="raffle_number"
                                        inputmode="numeric"
                                        maxlength="3"
                                        pattern="[0-9]{3}"
                                        placeholder="Ej: 007"
                                        required
                                        @disabled(!$raffleSalesEnabled)
                                        class="w-40 h-11 px-4 rounded-xl border border-gray-200 dark:border-[#2a2a2a] bg-white dark:bg-[#161615] text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-600 font-bold tracking-[0.25em] text-center"
                                    >
                                    <button
                                        type="button"
                                        id="check-raffle-number"
                                        @disabled(!$raffleSalesEnabled)
                                        class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 text-sm font-semibold hover:bg-gray-50"
                                    >
                                        Verificar numero
                                    </button>
                                    <p id="raffle-status" class="text-sm font-semibold min-h-5"></p>
                                    <p class="text-xs text-gray-500">Cada numero solo se puede vender una vez.</p>
                                </div>

                                <div
                                    class="mb-6 rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-900">
                                    <p class="font-semibold mb-1">Transparencia del sorteo</p>
                                    <p>Si no se venden los 100 numeros, el sorteo se realiza igual en la fecha anunciada
                                        y participan solo los numeros vendidos.</p>
                                    <p class="mt-1">Para garantizar ganador, si el primer numero oficial no fue
                                        vendido, se toma el siguiente puesto oficial hasta encontrar un numero vendido.
                                    </p>
                                </div>
                            @else
                                <div class="mb-5">
                                    <label
                                        class="text-sm font-semibold uppercase tracking-wide text-gray-500">Cantidad</label>
                                    <div
                                        class="mt-2 inline-flex items-center rounded-xl border border-gray-200 dark:border-[#2a2a2a] overflow-hidden">
                                        <button type="button" id="qty-decrease"
                                            class="w-11 h-11 bg-gray-50 dark:bg-[#0f0f0f] hover:bg-gray-100 dark:hover:bg-[#1a1a1a] text-xl font-bold">-</button>
                                        <input id="qty-input" type="number" name="quantity" min="1"
                                            value="1"
                                            class="w-16 h-11 text-center bg-white dark:bg-[#161615] font-bold border-x border-gray-200 dark:border-[#2a2a2a]"
                                            style="-moz-appearance:textfield;appearance:textfield;">
                                        <button type="button" id="qty-increase"
                                            class="w-11 h-11 bg-gray-50 dark:bg-[#0f0f0f] hover:bg-gray-100 dark:hover:bg-[#1a1a1a] text-xl font-bold">+</button>
                                    </div>
                                </div>
                            @endif

                            @if ($product->is_raffle && !$raffleSalesEnabled)
                                <button type="submit" disabled
                                    class="block w-72 text-center bg-gray-300 text-gray-600 px-8 py-3.5 rounded-xl font-black text-lg uppercase tracking-tight cursor-not-allowed">
                                    Venta pausada
                                </button>
                            @else
                                <button type="submit"
                                    class="block w-72 text-center bg-gradient-to-r from-purple-600 to-purple-700 text-white px-8 py-3.5 rounded-xl font-black text-lg uppercase tracking-tight hover:from-purple-700 hover:to-purple-800 transition-all shadow-xl shadow-purple-500/20 active:scale-[0.98]">
                                    Comprar ahora
                                </button>
                            @endif
                        </form>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <footer class="py-10 text-center text-sm text-[#706f6c] border-t border-gray-100 dark:border-white/5">
        &copy; {{ date('Y') }} Marketplace Bariloche
    </footer>

    <script>
        (() => {
            const input = document.getElementById('qty-input');
            const dec = document.getElementById('qty-decrease');
            const inc = document.getElementById('qty-increase');
            if (!input || !dec || !inc) return;

            const clamp = () => {
                const next = parseInt(input.value || '1', 10);
                input.value = Number.isNaN(next) || next < 1 ? 1 : next;
            };

            dec.addEventListener('click', () => {
                clamp();
                input.value = Math.max(1, parseInt(input.value, 10) - 1);
            });

            inc.addEventListener('click', () => {
                clamp();
                input.value = parseInt(input.value, 10) + 1;
            });

            input.addEventListener('input', clamp);
            input.addEventListener('blur', clamp);
        })();

        document.getElementById('addToCartForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const formData = new FormData(form);
            const raffleStatus = document.getElementById('raffle-status');

            const raffleInput = document.getElementById('raffle-number');
            if (raffleInput) {
                const parsed = parseInt(raffleInput.value || '', 10);
                if (Number.isNaN(parsed) || parsed < 0 || parsed > 99) {
                    alert('Ingresa un numero valido entre 000 y 099.');
                    return;
                }

                formData.set('raffle_number', String(parsed).padStart(3, '0'));
                formData.set('quantity', '1');

                if (raffleStatus && raffleStatus.dataset.available !== '1') {
                    alert('Primero verifica que el numero este disponible.');
                    return;
                }
            }

            fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Redirect al carrito para revisar antes del checkout
                        window.location.href = '{{ route('cart.index') }}';
                        return;
                    }

                    if (data.message) {
                        alert(data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
        });

        (() => {
            const raffleInput = document.getElementById('raffle-number');
            const checkButton = document.getElementById('check-raffle-number');
            const status = document.getElementById('raffle-status');

            if (!raffleInput || !checkButton || !status) {
                return;
            }

            const setStatus = (message, isAvailable = null) => {
                status.textContent = message;
                status.dataset.available = isAvailable === true ? '1' : '0';
                status.classList.remove('text-green-600', 'text-red-600', 'text-gray-500');
                if (isAvailable === true) {
                    status.classList.add('text-green-600');
                } else if (isAvailable === false) {
                    status.classList.add('text-red-600');
                } else {
                    status.classList.add('text-gray-500');
                }
            };

            const normalizeInput = () => {
                const parsed = parseInt(raffleInput.value || '', 10);
                if (Number.isNaN(parsed) || parsed < 0 || parsed > 99) {
                    return null;
                }

                return String(parsed).padStart(3, '0');
            };

            raffleInput.addEventListener('input', () => {
                status.textContent = '';
                status.dataset.available = '0';
            });

            checkButton.addEventListener('click', async () => {
                const normalized = normalizeInput();
                if (!normalized) {
                    setStatus('Ingresa un numero valido entre 000 y 099.', false);
                    return;
                }

                raffleInput.value = normalized;
                setStatus('Verificando...', null);

                const url = new URL(
                    '{{ route('products.raffle.availability', ['product' => $product->slug]) }}',
                    window.location.origin);
                url.searchParams.set('raffle_number', normalized);

                try {
                    const response = await fetch(url.toString(), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    const data = await response.json();
                    setStatus(data.message || 'No se pudo verificar el numero.', !!data.available);
                } catch (_) {
                    setStatus('No se pudo verificar el numero. Intenta nuevamente.', false);
                }
            });
        })();
    </script>
</body>

</html>
