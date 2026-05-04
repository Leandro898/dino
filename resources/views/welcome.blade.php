<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Bari Tienda') }} — Tienda online en Bariloche</title>

    {{-- Open Graph / WhatsApp --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('app.name', 'Bari Tienda') }}">
    <meta property="og:title" content="Bari Tienda — Tienda online en Bariloche">
    <meta property="og:description" content="Comprá cigarrillos, bebidas, snacks y más con entrega rápida en Bariloche. ¡Pedí ahora!">
    <meta property="og:url" content="{{ config('app.url') }}">
    <meta property="og:image" content="{{ config('app.url') }}/images/og-image.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="es_AR">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon-arg.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.ga4')

    <style>
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>

<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] antialiased">

    @php
        $cartCount = collect(session('cart', []))->sum('quantity');
    @endphp

    @include('partials.header')

    <section class="relative w-full bg-[#FDFDFC] pt-8 pb-3 md:pt-10 md:pb-4">
        <div class="max-w-7xl mx-auto px-4 md:px-10 lg:px-20">
            <div class="max-w-2xl">
                <label for="home-search" class="block text-sm font-bold uppercase tracking-widest text-gray-500 mb-3">
                    Buscar productos
                </label>
                <div class="relative">
                    <input id="home-search" type="search" placeholder="Ej: cigarrillos, coca cola, fernet..."
                        class="w-full rounded-2xl border-2 border-purple-300 bg-white px-5 py-4 pr-12 text-sm font-medium text-gray-700 placeholder:text-gray-400 outline-none focus:border-purple-600 focus:ring-2 focus:ring-purple-300 transition shadow-lg hover:shadow-xl">
                    <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-purple-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                </div>
            </div>

            @if ($raffleProduct)
                @php
                    $raffleBannerPath = collect([
                        'images/sorteo-banner.jpg',
                        'images/sorteo-banner.jpeg',
                        'images/sorteo-banner.png',
                        'images/sorteo-banner.webp',
                    ])->first(fn ($path) => file_exists(public_path($path)));
                @endphp

                <a href="{{ route('products.show', ['product' => $raffleProduct->slug]) }}"
                    class="mt-6 block rounded-3xl overflow-hidden transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:ring-offset-2">
                    @if ($raffleBannerPath)
                        <div class="relative w-full h-[170px] sm:h-[230px] md:h-[300px] lg:h-[340px] overflow-hidden bg-[#2b0f5b]">
                            <img src="{{ asset($raffleBannerPath) }}" alt="Participar del sorteo"
                                class="block w-full h-full object-cover object-center scale-[1.25] sm:scale-[1.16] md:scale-[1.12] lg:scale-[1.08]">
                        </div>
                    @else
                        <div
                            class="relative w-full aspect-[16/8] sm:aspect-[16/7] md:aspect-[16/6] bg-gradient-to-r from-[#f97316] via-[#f59e0b] to-[#facc15] px-6 py-8 sm:px-10 sm:py-10 text-white flex items-center">
                            <div class="absolute inset-y-0 right-0 w-1/3 bg-white/10 blur-2xl"></div>
                            <div class="relative z-10 max-w-2xl">
                                <p class="text-xs sm:text-sm font-black uppercase tracking-[0.2em] text-white/90">Sorteo Bari Tienda</p>
                                <h2 class="mt-2 text-2xl sm:text-3xl font-black uppercase leading-tight">Participa ahora por el premio</h2>
                                <p class="mt-2 text-sm sm:text-base text-white/90">Toca este flyer para elegir tu numero y entrar al sorteo.</p>
                                <span
                                    class="inline-flex mt-4 items-center rounded-full bg-white text-[#7c2d12] px-4 py-2 text-xs sm:text-sm font-extrabold uppercase tracking-wide">Ver sorteo</span>
                                <p class="mt-3 text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-white/80">Aqui ira tu imagen de banner cuando la subas en public/images/sorteo-banner.jpg</p>
                            </div>
                        </div>
                    @endif
                </a>
            @endif
        </div>
    </section>

    <main id="productos" class="w-full max-w-7xl mx-auto px-4 md:px-10 lg:px-20 pt-1 md:pt-2 pb-8 md:pb-10 lg:pb-20">

        @if ($products->isEmpty())
            <div class="text-center py-20">
                <p class="text-gray-500">No hay productos disponibles actualmente.</p>
            </div>
        @else
            <div id="categories-wrapper" class="space-y-6">
                @foreach ($categorizedProducts as $category => $categoryProducts)
                    <details data-category-details class="group rounded-2xl border border-gray-200 bg-white overflow-hidden">
                        <summary class="list-none cursor-pointer px-5 py-4 bg-gradient-to-r from-white to-purple-50 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-purple-100 text-purple-700 font-black text-xs">
                                    {{ $categoryProducts->count() }}
                                </span>
                                <h2 class="text-lg font-extrabold uppercase tracking-wide text-[#1b1b18]">{{ $category }}</h2>
                            </div>
                            <span class="text-xs font-bold uppercase tracking-wider text-purple-600 group-open:hidden">Mostrar</span>
                            <span class="text-xs font-bold uppercase tracking-wider text-purple-600 hidden group-open:inline">Ocultar</span>
                        </summary>

                        <div class="px-5 py-5 border-t border-gray-100">
                            <div class="relative">
                                <button type="button"
                                    data-carousel-prev
                                    class="hidden md:flex items-center justify-center absolute left-2 top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full bg-white/95 border border-gray-200 text-gray-700 shadow hover:bg-white hover:shadow-md transition"
                                    aria-label="Desplazar carrusel a la izquierda">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>

                                <button type="button"
                                    data-carousel-next
                                    class="hidden md:flex items-center justify-center absolute right-2 top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full bg-white/95 border border-gray-200 text-gray-700 shadow hover:bg-white hover:shadow-md transition"
                                    aria-label="Desplazar carrusel a la derecha">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>

                                <div data-carousel-track class="flex gap-4 overflow-x-auto pb-2 snap-x snap-mandatory no-scrollbar md:px-10">
                                @foreach ($categoryProducts as $product)
                                    <a href="{{ route('products.show', ['product' => $product->slug]) }}"
                                        data-product-card
                                        data-product-name="{{ Str::lower($product->name) }}"
                                        data-product-category="{{ Str::lower($category) }}"
                                        class="group/card shrink-0 bg-white dark:bg-[#1d1d1d] rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all flex flex-col h-full border border-gray-100 dark:border-white/5 snap-start"
                                        style="width: 220px; min-width: 220px; max-width: 220px; height: 390px;">

                                        <div class="relative bg-gray-100 p-3" style="height: 220px;">
                                            @if ($product->image)
                                                <div class="w-full h-full rounded-xl bg-white/80 flex items-center justify-center overflow-hidden">
                                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                                        class="w-40 h-40 object-contain transition-transform duration-500 group-hover/card:scale-105">
                                                </div>
                                            @else
                                                <div class="w-full h-full rounded-xl bg-white/80 flex items-center justify-center text-gray-400 italic">
                                                    Sin imagen
                                                </div>
                                            @endif
                                        </div>

                                        <div class="p-4 flex flex-col" style="height: 170px;">
                                            <h3 class="text-sm font-extrabold dark:text-white uppercase leading-tight mb-2 line-clamp-2 group-hover/card:text-purple-600 transition-colors"
                                                style="height: 2.75rem; overflow: hidden;">
                                                {{ $product->name }}
                                            </h3>

                                            <div class="mt-auto pt-3 border-t border-gray-100 dark:border-white/5 flex items-center justify-between">
                                                <span class="text-lg font-black text-black dark:text-white">
                                                    ${{ number_format($product->price, 0, ',', '.') }}
                                                </span>
                                                <span class="text-[10px] font-bold text-purple-600 uppercase">Ver más</span>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                                </div>
                            </div>
                        </div>
                    </details>
                @endforeach
            </div>

            <div id="search-empty-state" class="hidden text-center py-14">
                <p class="text-gray-500 font-semibold">No encontramos productos con esa búsqueda.</p>
            </div>
        @endif
    </main>

    <footer class="py-10 text-center text-sm text-[#706f6c]">
        &copy; {{ date('Y') }} Bari Tienda
    </footer>

    <script>
        (() => {
            const input = document.getElementById('home-search');
            const cards = document.querySelectorAll('[data-product-card]');
            const categoryDetails = document.querySelectorAll('[data-category-details]');
            const emptyState = document.getElementById('search-empty-state');
            if (!input || !cards.length) return;

            const carouselTracks = document.querySelectorAll('[data-carousel-track]');
            carouselTracks.forEach((track) => {
                const wrapper = track.parentElement;
                const prevBtn = wrapper?.querySelector('[data-carousel-prev]');
                const nextBtn = wrapper?.querySelector('[data-carousel-next]');
                if (!prevBtn || !nextBtn) return;

                const scrollByStep = (direction) => {
                    const firstCard = track.querySelector('[data-product-card]');
                    const cardWidth = firstCard ? firstCard.getBoundingClientRect().width : 220;
                    const gap = 16;
                    const step = cardWidth * 3 + gap * 2;
                    track.scrollBy({
                        left: direction * step,
                        behavior: 'smooth',
                    });
                };

                prevBtn.addEventListener('click', () => scrollByStep(-1));
                nextBtn.addEventListener('click', () => scrollByStep(1));
            });

            categoryDetails.forEach((section) => {
                section.dataset.initialOpen = section.open ? '1' : '0';
            });

            const normalize = (value) =>
                (value || '')
                .toString()
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .trim();

            const isSubsequence = (query, text) => {
                let i = 0;
                let j = 0;
                while (i < query.length && j < text.length) {
                    if (query[i] === text[j]) i += 1;
                    j += 1;
                }
                return i === query.length;
            };

            const levenshtein = (a, b) => {
                if (a === b) return 0;
                if (!a.length) return b.length;
                if (!b.length) return a.length;

                const matrix = Array.from({ length: a.length + 1 }, () => []);
                for (let i = 0; i <= a.length; i += 1) matrix[i][0] = i;
                for (let j = 0; j <= b.length; j += 1) matrix[0][j] = j;

                for (let i = 1; i <= a.length; i += 1) {
                    for (let j = 1; j <= b.length; j += 1) {
                        const cost = a[i - 1] === b[j - 1] ? 0 : 1;
                        matrix[i][j] = Math.min(
                            matrix[i - 1][j] + 1,
                            matrix[i][j - 1] + 1,
                            matrix[i - 1][j - 1] + cost
                        );
                    }
                }

                return matrix[a.length][b.length];
            };

            const tokenScore = (queryToken, text) => {
                if (!queryToken) return 0;
                if (text.startsWith(queryToken)) return 120;
                if (text.includes(queryToken)) return 90;
                if (isSubsequence(queryToken, text)) return 70;

                if (queryToken.length >= 3) {
                    const words = text.split(/\s+/);
                    const bestDistance = words.reduce((best, word) => {
                        const dist = levenshtein(queryToken, word);
                        return Math.min(best, dist);
                    }, Infinity);

                    if (bestDistance <= 1) return 60;
                    if (bestDistance === 2 && queryToken.length >= 5) return 45;
                }

                return 0;
            };

            const applyFilter = () => {
                const query = normalize(input.value);
                const tokens = query.split(/\s+/).filter(Boolean);
                let visible = 0;

                cards.forEach((card) => {
                    const name = normalize(card.getAttribute('data-product-name'));
                    const category = normalize(card.getAttribute('data-product-category'));
                    const searchable = `${name} ${category}`.trim();
                    let score = 0;

                    if (!tokens.length) {
                        score = 1;
                    } else {
                        const tokenScores = tokens.map((token) => tokenScore(token, searchable));
                        const allMatched = tokenScores.every((s) => s > 0);
                        if (allMatched) {
                            score = tokenScores.reduce((acc, s) => acc + s, 0);
                        }
                    }

                    const matches = score > 0;
                    card.classList.toggle('hidden', !matches);
                    card.style.order = matches ? String(10000 - score) : '99999';
                    if (matches) visible += 1;
                });

                categoryDetails.forEach((section) => {
                    const visibleInSection = section.querySelectorAll('[data-product-card]:not(.hidden)').length;
                    const hasQuery = tokens.length > 0;
                    section.classList.toggle('hidden', visibleInSection === 0);

                    if (hasQuery && visibleInSection > 0) {
                        section.open = true;
                    } else if (!hasQuery) {
                        section.open = section.dataset.initialOpen === '1';
                    }
                });

                if (emptyState) {
                    emptyState.classList.toggle('hidden', visible > 0);
                }
            };

            input.addEventListener('input', applyFilter);
            applyFilter();
        })();
    </script>
</body>

</html>
