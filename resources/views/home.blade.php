<x-front-layout bodyClass="home-body">
    @push('styles')
        <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700" rel="stylesheet" />
        
        <script>
            window.HomeConfig = {
                searchEndpoint: @json(route('home.search.products')),
                bebidasUrl: @json(route('categories.almacen.beverages')),
                pharmacyUrl: @json(route('categories.pharmacy')),
                almacenUrl: @json(route('categories.almacen')),
                comidasUrl: @json(route('food-vendors.index'))
            };
        </script>
        @vite(['resources/css/home.css'])
    @endpush

    <main>
        <section class="panel">

            <div class="tiles" style="margin-bottom: 1.5rem;">
                <button class="tile tile-primary" type="button" data-query="bebidas">
                    <span class="tile-icon-wrap">
                        <span class="tile-icon" aria-hidden="true">🍾</span>
                    </span>
                    <span class="tile-label">Bebidas</span>
                </button>

                <button class="tile" type="button" data-query="almacen">
                    <span class="tile-icon-wrap">
                        <span class="tile-icon">🛒</span>
                    </span>
                    <span class="tile-label">Almacen</span>
                </button>

                <button class="tile" type="button" data-query="comidas">
                    <span class="tile-icon-wrap">
                        <span class="tile-icon">🍽️</span>
                    </span>
                    <span class="tile-label">Comidas</span>
                </button>

                <button class="tile" type="button" data-query="farmacia">
                    <span class="tile-icon-wrap">
                        <span class="tile-icon">💊</span>
                    </span>
                    <span class="tile-label">Farmacia</span>
                </button>
            </div>

            <p class="title" style="margin-top: 1rem;">Buscá lo que necesitas</p>
            <div class="search-wrap" style="margin-bottom: 1.5rem;">
                <span class="search-icon">🔎</span>
                <input id="quickSearch" class="search" type="search" placeholder="Ej: azucar, yerba, gaseosa"
                    aria-label="Buscar categoria">
            </div>

            <div class="results-wrapper">
                <div id="searchResultsGallery" class="results-space is-empty" aria-live="polite">
                    <div class="results-track is-placeholder"></div>
                </div>
                <button type="button" id="galleryMicBtn" class="results-mic-btn" aria-label="Buscar por voz">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="22"
                        height="22">
                        <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z" />
                        <path d="M19 10v2a7 7 0 0 1-14 0v-2H3v2a9 9 0 0 0 8 8.94V23h2v-2.06A9 9 0 0 0 21 12v-2h-2z" />
                    </svg>
                </button>
                <p class="results-mic-label" aria-hidden="true">Pedí lo que quieras</p>
                <div id="micStopLoader" class="mic-stop-loader" aria-hidden="true">
                    <div class="mic-stop-loader-inner">
                        <span class="mic-stop-loader-spinner" aria-hidden="true"></span>
                        <span>Apagando mic...</span>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <livewire:live-chat />

    @push('scripts')
        @vite(['resources/js/home.js'])
    @endpush
</x-front-layout>
