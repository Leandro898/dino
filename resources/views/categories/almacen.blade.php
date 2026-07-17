<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $categoryTitle ?? 'Categoria' }} - {{ config('app.name', 'Bari Tienda') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon-arg.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.ga4')

    <style>
        @media (max-width: 768px) {
            .category-mobile-scroll-safe {
                padding-bottom: calc(9rem + env(safe-area-inset-bottom)) !important;
            }
        }
    </style>
</head>

<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] antialiased">

    @include('partials.header')

    <main class="category-mobile-scroll-safe max-w-7xl mx-auto px-4 py-8 md:px-10 lg:px-20 md:py-10">
        <section
            class="rounded-3xl bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-700 px-6 py-8 text-white shadow-xl md:px-8">
            <h1 class="text-3xl font-black uppercase md:text-4xl">{{ $categoryTitle ?? 'Categoria' }}</h1>

            <form id="almacen-form" method="GET" action="{{ route('categories.almacen') }}" class="mt-5 max-w-xl">
                <label for="almacen-search" class="sr-only">Buscar productos de almacén</label>
                <div class="flex">
                    <input id="almacen-search" type="text" name="q" value="{{ $search }}"
                        placeholder="¿Qué necesitás hoy? Ej: pan, arroz, etc..." autocomplete="off"
                        class="w-full rounded-2xl border border-white/20 bg-white px-4 py-3 text-sm font-semibold text-gray-800 outline-none">
                </div>
                <p id="search-status" class="mt-2 text-xs font-semibold text-white/70 hidden">Buscando...</p>
            </form>
        </section>

        <section class="mt-8" id="products-section">
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; margin-bottom: 2rem;">
                <button type="button" onclick="startVoiceSearch('almacen-search', 'almacen-form')" class="shared-mic-btn" style="margin-bottom: 12px;" title="Búsqueda por voz">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="22" height="22">
                        <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z" />
                        <path d="M19 10v2a7 7 0 0 1-14 0v-2H3v2a9 9 0 0 0 8 8.94V23h2v-2.06A9 9 0 0 0 21 12v-2h-2z" />
                    </svg>
                </button>
                <p class="shared-mic-label">Pedi lo que quieras</p>
            </div>

            <div id="productsResultsContainer">
                @if ($products->isEmpty())
                    <div class="rounded-3xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center text-gray-500">
                        <p class="mb-4">No hay productos para esta búsqueda.</p>
                        <button type="button" onclick="window.Livewire.dispatch('open-live-chat')" class="rounded-full bg-[#6a31df] px-6 py-3 font-bold text-white shadow-lg transition hover:-translate-y-1 hover:shadow-xl">
                            Pídelo por chat
                        </button>
                    </div>
                @else
                    <div id="products-grid" class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-4">
                        @foreach ($products as $product)
                            <a href="{{ route('products.show', ['product' => $product->slug]) }}"
                                class="group flex h-full flex-col overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                                <div class="flex h-52 items-center justify-center bg-gray-50 p-4">
                                    @if ($product->image_src)
                                        <img src="{{ $product->image_src }}" alt="{{ $product->name }}"
                                            class="h-40 w-40 object-contain transition duration-300 group-hover:scale-105">
                                    @else
                                        <div class="text-sm font-semibold text-gray-400">Sin imagen</div>
                                    @endif
                                </div>

                                <div class="flex flex-1 flex-col p-4">
                                    <h2
                                        class="min-h-[3.25rem] text-sm font-black uppercase leading-tight text-[#1b1b18]">
                                        {{ $product->name }}
                                    </h2>

                                    <div class="mt-auto border-t border-gray-100 pt-3">
                                        <p class="text-2xl font-black text-emerald-700">
                                            ${{ number_format($product->adjusted_price, 0, ',', '.') }}
                                        </p>
                                        <p class="mt-1 text-[11px] font-bold uppercase tracking-wide text-gray-400">
                                            Precio actualizado
                                        </p>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <div class="mt-12 text-center mb-4">
                        <button type="button" onclick="window.Livewire.dispatch('open-live-chat')" class="rounded-full bg-[#6a31df] px-6 py-3 font-bold text-white shadow-lg transition hover:-translate-y-1 hover:shadow-xl">
                            ¿No encontrás lo que buscabas? Pídelo por chat
                        </button>
                    </div>
                    <div id="products-pagination" class="mt-8">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </section>
    </main>

    <livewire:live-chat />

    <script>
        function startVoiceSearch(inputId, formId) {
            const inputElement = document.getElementById(inputId);
            const formElement = document.getElementById(formId);
            
            if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
                alert('Lo siento, tu navegador no soporta búsqueda por voz.');
                return;
            }

            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            const recognition = new SpeechRecognition();
            
            recognition.lang = 'es-AR';
            recognition.interimResults = false;
            recognition.maxAlternatives = 1;

            recognition.onstart = function() {
                inputElement.placeholder = 'Escuchando...';
                inputElement.value = '';
            };

            recognition.onresult = function(event) {
                const transcript = event.results[0][0].transcript;
                inputElement.value = transcript;
                if(formElement) {
                    formElement.submit();
                } else {
                    inputElement.dispatchEvent(new KeyboardEvent('keydown', {'key': 'Enter'}));
                }
            };

            recognition.onerror = function(event) {
                console.error('Error en reconocimiento de voz:', event.error);
                inputElement.placeholder = 'Error al escuchar. Intenta de nuevo.';
            };

            recognition.onend = function() {
                if(!inputElement.value) {
                    inputElement.placeholder = '¿Qué necesitás hoy? Ej: pan, arroz, etc...';
                }
            };

            recognition.start();
        }
    </script>
</body>

</html>