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
    @if ($product->adjusted_price)
        <meta property="product:price:amount" content="{{ $product->adjusted_price }}">
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



    @include('partials.header')

    <main class="max-w-7xl mx-auto px-4 md:px-6 py-6 md:py-10 lg:py-20 pb-[calc(10rem+env(safe-area-inset-bottom))] md:pb-10">
        <div class="flex flex-col lg:flex-row gap-8 lg:gap-12 xl:gap-20 lg:items-start">

            <div class="w-full lg:w-5/12 xl:w-[40%]">
                @if ($product->image)
                    <div class="h-[380px] md:h-[420px] lg:h-[480px] rounded-2xl md:rounded-3xl overflow-hidden flex items-center justify-center">
                        <img src="{{ $product->image_src }}" alt="{{ $product->name }}"
                            class="w-full h-full object-contain">
                    </div>
                @else
                    <div class="h-[380px] md:h-[420px] lg:h-[480px] flex items-center justify-center text-gray-400 italic bg-gray-200 rounded-2xl md:rounded-3xl">
                        Sin imagen disponible
                    </div>
                @endif
            </div>

            <div class="w-full lg:w-7/12 xl:w-[60%] flex flex-col">
                <div class="w-full lg:max-w-2xl lg:mx-0">
                    <div class="mb-6">
                        <h1 class="text-3xl md:text-4xl lg:text-5xl font-black dark:text-white uppercase leading-tight mb-4">
                            {{ $product->name }}
                        </h1>
                    </div>

                    <div
                        class="bg-gray-50 dark:bg-[#161615] p-5 md:p-6 rounded-2xl md:rounded-3xl mb-8 border border-gray-100 dark:border-white/5">
                        <div class="flex items-end gap-2 mb-2">
                            <span class="text-3xl md:text-4xl font-black text-black dark:text-white">
                                ${{ number_format($product->adjusted_price, 0, ',', '.') }}
                            </span>
                            <span class="text-xs md:text-sm font-bold text-gray-400 uppercase mb-1">ARS</span>
                        </div>
                        <p class="text-xs text-gray-500 font-bold uppercase tracking-tight">Precio final sin comisiones
                        </p>
                    </div>

                    <div class="mb-8 md:mb-10">
                        <h2 class="text-xs md:text-sm font-bold uppercase tracking-widest text-gray-400 mb-3">Descripción</h2>
                        <p class="text-sm md:text-base text-gray-600 dark:text-gray-300 leading-relaxed max-w-xl">
                            {{ filled($product->description) ? $product->description : 'El vendedor no ha proporcionado una descripción detallada para este producto.' }}
                        </p>
                    </div>

                    <div class="mt-auto space-y-4">
                        <form id="addToCartForm" action="{{ route('cart.add', $product->id) }}" method="POST">
                            @csrf

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

                                <button type="submit"
                                    class="block w-72 text-center bg-gradient-to-r from-purple-600 to-purple-700 text-white px-8 py-3.5 rounded-xl font-black text-lg uppercase tracking-tight hover:from-purple-700 hover:to-purple-800 transition-all shadow-xl shadow-purple-500/20 active:scale-[0.98]">
                                    Comprar ahora
                                </button>
                        </form>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <footer class="py-10 text-center text-sm text-[#706f6c] border-t border-gray-100 dark:border-white/5 mb-32 md:mb-0">
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

            formData.set('quantity', '1');

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

    </script>
</body>

</html>
