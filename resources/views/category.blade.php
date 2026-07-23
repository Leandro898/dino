<x-front-layout bodyClass="home-body">

    @push('styles')
        <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700" rel="stylesheet" />
        <script>
            window.CategoryConfig = {
                searchEndpoint: @json(route('category.api.search', ['slug' => $slug]))
            };
        </script>
        @vite(['resources/css/home.css'])
    @endpush

    <main>
        <section class="panel">
            <div class="flex items-center justify-between mb-2">
                <a href="{{ route('home') }}"
                    class="text-purple-600 hover:text-purple-700 font-semibold text-sm inline-flex items-center gap-1">
                    ← Volver a home
                </a>
            </div>
            
            <h1 class="text-4xl font-bold text-gray-900 text-center mb-2">{{ $categoryName }}</h1>
            <p class="title" style="margin-top: 1rem;">Buscá lo que necesitas</p>

            <div class="search-wrap" style="margin-bottom: 1.5rem;">
                <span class="search-icon">🔎</span>
                <input id="quickSearch" class="search" type="search" placeholder="Buscar productos en {{ $categoryName }}..."
                    aria-label="Buscar categoria" value="{{ $search ?? '' }}">
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
                <p class="results-mic-label" aria-hidden="true">Pedí con tu voz</p>
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
        @vite(['resources/js/category.js'])
    @endpush
</x-front-layout>
