<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bari Tienda | Inicio</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --bg-top: #f2f3ff;
            --bg-bottom: #e9edff;
            --card: #ffffff;
            --line: #dde3fb;
            --text: #1e2442;
            --muted: #657099;
            --brand: #6a31df;
            --brand-strong: #3f2194;
            --brand-soft: #eee8ff;
            --shadow: 0 18px 34px rgba(40, 49, 92, 0.14);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Outfit', sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 15% 10%, rgba(121, 86, 225, 0.14), transparent 42%),
                radial-gradient(circle at 84% 20%, rgba(95, 157, 255, 0.12), transparent 38%),
                linear-gradient(180deg, var(--bg-top) 0%, var(--bg-bottom) 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        #globalVoiceFab {
            display: none !important;
        }

        main {
            width: min(100%, 860px);
            margin: 0 auto;
            min-height: calc(100vh - 84px);
            display: grid;
            align-content: center;
            padding: 1.2rem 1rem 2rem;
        }

        .panel {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 28px;
            padding: 1.35rem;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .title {
            margin: 0 0 1rem;
            font-size: clamp(1.05rem, 2.3vw, 1.28rem);
            font-weight: 600;
            letter-spacing: 0.01em;
            color: var(--muted);
            text-wrap: balance;
        }

        .subtitle {
            margin: 0.35rem 0 1rem;
            color: var(--muted);
            font-size: 0.97rem;
            font-weight: 500;
        }

        .search-wrap {
            position: relative;
            margin-bottom: 0.65rem;
        }

        .results-space {
            height: 232px;
            margin-bottom: 1.05rem;
            max-width: 100%;
            overflow: hidden;
        }

        .results-space.is-empty {
            border: 1px dashed #e5e9f7;
            border-radius: 14px;
            background: linear-gradient(180deg, #fbfcff 0%, #f7f9ff 100%);
        }

        .results-status {
            margin: 0;
            color: var(--muted);
            font-size: 0.9rem;
            font-weight: 500;
            padding: 0.35rem 0.2rem;
        }

        .results-track {
            display: flex;
            gap: 0.7rem;
            overflow-x: auto;
            padding: 0.15rem 0.1rem 0.3rem;
            scroll-snap-type: x proximity;
            -webkit-overflow-scrolling: touch;
            width: 100%;
            max-width: 100%;
        }

        .results-track.is-placeholder {
            height: 100%;
            overflow: hidden;
        }

        .results-track::-webkit-scrollbar {
            height: 8px;
        }

        .results-track::-webkit-scrollbar-thumb {
            background: #ccd6f3;
            border-radius: 999px;
        }

        .result-card {
            flex: 0 0 176px;
            min-width: 176px;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: #fff;
            text-decoration: none;
            color: inherit;
            box-shadow: 0 6px 14px rgba(52, 69, 116, 0.08);
            scroll-snap-align: start;
            transition: transform 150ms ease, box-shadow 150ms ease, border-color 150ms ease;
        }

        .result-card:hover {
            transform: translateY(-2px);
            border-color: #cfd8f4;
            box-shadow: 0 10px 18px rgba(52, 69, 116, 0.12);
        }

        .result-image {
            width: 100%;
            height: 118px;
            border-bottom: 1px solid #edf1fc;
            border-radius: 14px 14px 0 0;
            background: #f8faff;
            display: grid;
            place-items: center;
            overflow: hidden;
        }

        .result-image img {
            width: 102px;
            height: 102px;
            object-fit: contain;
        }

        .result-meta {
            padding: 0.56rem 0.6rem 0.62rem;
            text-align: center;
        }

        .result-name {
            margin: 0;
            font-size: 0.83rem;
            font-weight: 700;
            line-height: 1.24;
            min-height: 2.05rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .result-price {
            margin: 0.36rem 0 0;
            font-size: 1.02rem;
            font-weight: 700;
            color: var(--brand-strong);
        }

        /* Voice transcript display */
        .voice-transcript {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            padding: 1rem 1.2rem;
            text-align: center;
            gap: 0.55rem;
        }

        .voice-transcript .voice-text {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--ink);
            margin: 0;
            line-height: 1.25;
            letter-spacing: -0.01em;
        }

        .voice-transcript.is-interim .voice-text {
            color: #8a97c4;
        }

        .voice-transcript .voice-status {
            font-size: 0.88rem;
            color: var(--muted);
            margin: 0;
        }

        .voice-transcript .voice-hint {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #fff;
            background: linear-gradient(135deg, #7c3aed 0%, #4c1d95 100%);
            padding: 0.3rem 0.75rem;
            border-radius: 999px;
            margin: 0;
            letter-spacing: 0.01em;
        }

        .voice-transcript .voice-hint.is-pending {
            background: linear-gradient(135deg, #f59e0b 0%, #b45309 100%);
        }

        .voice-transcript .voice-hint.is-paused {
            background: linear-gradient(135deg, #6b7280 0%, #374151 100%);
        }

        .voice-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .voice-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            border: none;
            border-radius: 999px;
            padding: 0.35rem 1rem;
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 0.01em;
            transition: transform 120ms ease, box-shadow 120ms ease;
        }

        .voice-action-btn:active {
            transform: scale(0.96);
        }

        .voice-action-btn.is-new {
            background: linear-gradient(135deg, #7c3aed 0%, #4c1d95 100%);
            color: #fff;
            box-shadow: 0 4px 12px rgba(76, 29, 149, 0.28);
        }

        .voice-action-btn.is-resume {
            background: linear-gradient(135deg, #10b981 0%, #065f46 100%);
            color: #fff;
            box-shadow: 0 4px 12px rgba(6, 95, 70, 0.28);
        }

        .voice-countdown {
            font-size: 1rem;
            font-weight: 900;
            opacity: 0.9;
        }

        /* Mic button inside results area */
        .results-wrapper {
            position: relative;
        }

        .results-mic-btn {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10;
            width: 58px;
            height: 58px;
            border: none;
            border-radius: 50%;
            background: linear-gradient(145deg, #7c3aed 0%, #4c1d95 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(76, 29, 149, 0.32);
            transition: transform 140ms ease, box-shadow 140ms ease;
            outline: 3px solid rgba(255, 255, 255, 0.9);
            outline-offset: 0;
        }

        .results-mic-btn:hover {
            transform: translate(-50%, -52%);
            box-shadow: 0 12px 30px rgba(76, 29, 149, 0.42);
        }

        .results-mic-btn.is-listening {
            background: linear-gradient(145deg, #ef4444 0%, #b91c1c 100%);
            box-shadow: 0 8px 24px rgba(185, 28, 28, 0.32);
        }

        .results-mic-btn.is-pending {
            background: linear-gradient(145deg, #f59e0b 0%, #b45309 100%);
            box-shadow: 0 8px 24px rgba(180, 83, 9, 0.30);
        }

        .results-mic-label {
            position: absolute;
            top: calc(50% + 48px);
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            font-size: 0.84rem;
            font-weight: 800;
            letter-spacing: 0.01em;
            color: #5b617e;
            background: rgba(255, 255, 255, 0.88);
            border: 1px solid #dbe1f6;
            border-radius: 999px;
            padding: 0.26rem 0.65rem;
            white-space: nowrap;
            pointer-events: none;
        }

        .mic-stop-loader {
            position: absolute;
            inset: 0;
            z-index: 24;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(247, 250, 255, 0.92);
            border: 1px solid #dbe3fb;
            border-radius: 14px;
            pointer-events: all;
        }

        .mic-stop-loader.is-active {
            display: flex;
        }

        .mic-stop-loader-inner {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.45rem 0.78rem;
            border-radius: 999px;
            background: #ffffff;
            border: 1px solid #dbe3fb;
            color: #4f5680;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .mic-stop-loader-spinner {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2px solid #cfdbff;
            border-top-color: #6a31df;
            animation: mic-stop-spin 0.7s linear infinite;
        }

        @keyframes mic-stop-spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Hide center mic when results or transcript are shown */
        .results-space:not(.is-empty)~.results-mic-btn,
        .results-mic-btn.is-hidden,
        .results-space:not(.is-empty)~.results-mic-label,
        .results-mic-btn.is-hidden~.results-mic-label {
            display: none;
        }

        .voice-pulse {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #e8edfd;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: voice-pulse-anim 1.2s ease-in-out infinite;
            font-size: 1.2rem;
        }

        @keyframes voice-pulse-anim {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.2);
                opacity: 0.7;
            }
        }

        .search-icon {
            position: absolute;
            left: 0.8rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.95rem;
            color: #7b85a8;
            pointer-events: none;
        }

        .search {
            width: 100%;
            border: 1px solid #d5dcf7;
            border-radius: 999px;
            padding: 0.88rem 1rem 0.88rem 2.35rem;
            font: inherit;
            font-size: 1rem;
            color: var(--text);
            outline: none;
            transition: border-color 180ms ease, box-shadow 180ms ease;
        }

        .search:focus {
            border-color: #b9c6f1;
            box-shadow: 0 0 0 4px rgba(106, 49, 223, 0.13);
        }

        .tiles {
            margin-top: 0.2rem;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .tile {
            border: 1px solid var(--line);
            border-radius: 22px;
            background: #ffffff;
            color: var(--text);
            padding: 1rem 0.75rem;
            text-align: center;
            cursor: pointer;
            box-shadow: 0 8px 18px rgba(58, 72, 116, 0.06);
            transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease, background 160ms ease;
        }

        .tile:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 24px rgba(50, 63, 104, 0.12);
            border-color: #cfd8f4;
        }

        .tile-primary {
            background: linear-gradient(180deg, #ffffff 0%, #f7f9ff 100%);
            color: var(--text);
            border-color: #d7def4;
            box-shadow: 0 10px 22px rgba(52, 72, 128, 0.08);
        }

        .tile-icon-wrap {
            width: 72px;
            height: 72px;
            margin: 0 auto;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: var(--brand-soft);
            box-shadow: inset 0 0 0 1px rgba(105, 85, 174, 0.08);
        }

        .tile-primary .tile-icon-wrap {
            background: linear-gradient(160deg, #eef2ff 0%, #e3ebff 100%);
            box-shadow: 0 10px 18px rgba(76, 105, 180, 0.10);
        }

        .tile-primary:hover {
            border-color: #c9d4f1;
            box-shadow: 0 16px 26px rgba(52, 72, 128, 0.12);
        }

        .tile-icon {
            font-size: 1.95rem;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--brand-strong);
        }

        .tile-icon svg {
            width: 34px;
            height: 34px;
            display: block;
            fill: currentColor;
        }

        .tile-primary .tile-icon {
            color: var(--brand);
        }

        .tile-label {
            margin-top: 0.62rem;
            font-size: 1.03rem;
            font-weight: 700;
        }

        .quick-menu-backdrop {
            position: fixed;
            inset: 0;
            z-index: 88;
            background: transparent;
            opacity: 0;
            pointer-events: none;
            transition: opacity 180ms ease;
        }

        .quick-menu-backdrop.is-open {
            opacity: 1;
            pointer-events: auto;
        }

        .quick-menu {
            position: fixed;
            right: 20px;
            bottom: 22px;
            z-index: 89;
            width: 236px;
            height: 236px;
            pointer-events: none;
        }

        .quick-menu button {
            border: 0;
            cursor: pointer;
            font: inherit;
        }

        .quick-menu-trigger,
        .quick-menu-item {
            position: absolute;
            right: 0;
            bottom: 0;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 22px rgba(48, 37, 94, 0.22);
        }

        .quick-menu-trigger {
            width: 62px;
            height: 62px;
            color: #fff;
            background: linear-gradient(160deg, var(--brand) 0%, var(--brand-strong) 100%);
            pointer-events: auto;
            transition: transform 180ms ease, box-shadow 180ms ease;
        }

        .quick-menu-trigger:hover {
            transform: scale(1.05);
            box-shadow: 0 14px 26px rgba(48, 37, 94, 0.28);
        }

        .quick-menu-item {
            width: 52px;
            height: 52px;
            color: var(--brand-strong);
            background: #fff;
            border: 1px solid #d9def3;
            opacity: 0;
            pointer-events: none;
            transform: translate3d(0, 0, 0) scale(0.7);
            transition: transform 220ms ease, opacity 220ms ease, box-shadow 220ms ease;
        }

        .quick-menu-item:hover {
            box-shadow: 0 14px 24px rgba(48, 37, 94, 0.2);
        }

        .quick-menu-item.is-listening {
            color: #fff;
            border-color: rgba(255, 255, 255, 0.24);
            background: linear-gradient(145deg, #ef4444 0%, #b91c1c 100%);
            box-shadow: 0 14px 24px rgba(185, 28, 28, 0.35);
        }

        .quick-menu-item.is-voice {
            color: #fff;
            border-color: rgba(255, 255, 255, 0.22);
            background: linear-gradient(160deg, var(--brand) 0%, var(--brand-strong) 100%);
        }

        .quick-menu-item-icon {
            font-size: 1.35rem;
            line-height: 1;
        }

        .quick-menu-label {
            position: absolute;
            right: 58px;
            white-space: nowrap;
            padding: 0.35rem 0.55rem;
            border-radius: 999px;
            border: 1px solid #dde3f6;
            background: rgba(255, 255, 255, 0.98);
            color: #3f4a72;
            font-size: 0.72rem;
            font-weight: 700;
            opacity: 0;
            pointer-events: none;
            transition: opacity 140ms ease;
        }

        .quick-menu-item:hover .quick-menu-label,
        .quick-menu-item:focus-visible .quick-menu-label {
            opacity: 1;
        }

        .quick-menu.is-open .quick-menu-item {
            opacity: 1;
            pointer-events: auto;
        }

        .quick-menu.is-open .quick-menu-item[data-index='1'] {
            transform: translate3d(0, -148px, 0) scale(1);
        }

        .quick-menu.is-open .quick-menu-item[data-index='2'] {
            transform: translate3d(-57px, -138px, 0) scale(1);
        }

        .quick-menu.is-open .quick-menu-item[data-index='3'] {
            transform: translate3d(-106px, -106px, 0) scale(1);
        }

        .quick-menu.is-open .quick-menu-item[data-index='4'] {
            transform: translate3d(-138px, -57px, 0) scale(1);
        }

        .quick-menu.is-open .quick-menu-item[data-index='5'] {
            transform: translate3d(-148px, 0, 0) scale(1);
        }

        @media (max-width: 720px) {
            main {
                width: 100%;
                align-content: start;
                padding: 1rem 0.8rem 1.7rem;
            }

            .panel {
                border-radius: 20px;
                padding: 1rem;
            }

            .search {
                font-size: 0.98rem;
            }

            .search-wrap {
                margin-bottom: 0.55rem;
            }

            .results-space {
                height: 224px;
                margin-bottom: 0.95rem;
            }

            .result-card {
                flex: 0 0 164px;
                min-width: 164px;
            }

            .result-image {
                height: 110px;
            }

            .result-image img {
                width: 90px;
                height: 90px;
            }

            .result-name {
                font-size: 0.8rem;
                min-height: 1.95rem;
            }

            .tiles {
                gap: 0.75rem;
            }

            .tile {
                border-radius: 18px;
                padding: 0.9rem 0.6rem;
            }

            .tile-icon-wrap {
                width: 64px;
                height: 64px;
            }

            .tile-icon {
                font-size: 1.7rem;
            }

            .tile-label {
                margin-top: 0.5rem;
                font-size: 0.95rem;
            }

            .quick-menu {
                right: 14px;
                bottom: 14px;
                width: 198px;
                height: 198px;
            }

            .quick-menu-trigger {
                width: 58px;
                height: 58px;
            }

            .quick-menu-item {
                width: 46px;
                height: 46px;
            }

            .quick-menu.is-open .quick-menu-item[data-index='1'] {
                transform: translate3d(0, -122px, 0) scale(1);
            }

            .quick-menu.is-open .quick-menu-item[data-index='2'] {
                transform: translate3d(-47px, -113px, 0) scale(1);
            }

            .quick-menu.is-open .quick-menu-item[data-index='3'] {
                transform: translate3d(-86px, -86px, 0) scale(1);
            }

            .quick-menu.is-open .quick-menu-item[data-index='4'] {
                transform: translate3d(-113px, -47px, 0) scale(1);
            }

            .quick-menu.is-open .quick-menu-item[data-index='5'] {
                transform: translate3d(-122px, 0, 0) scale(1);
            }
        }
    </style>
</head>

<body>
    @include('partials.header')

    <main>
        <section class="panel">
            <p class="title">Buscá lo que necesitas</p>

            <div class="search-wrap">
                <span class="search-icon">🔎</span>
                <input id="quickSearch" class="search" type="search" placeholder="Ej: azucar, ibuprofeno, yerba"
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
                <p class="results-mic-label" aria-hidden="true">Apretá y pedí</p>
                <div id="micStopLoader" class="mic-stop-loader" aria-hidden="true">
                    <div class="mic-stop-loader-inner">
                        <span class="mic-stop-loader-spinner" aria-hidden="true"></span>
                        <span>Apagando mic...</span>
                    </div>
                </div>
            </div>

            <div class="tiles">
                <button class="tile tile-primary" type="button" data-query="bebidas">
                    <span class="tile-icon-wrap">
                        <span class="tile-icon" aria-hidden="true">🥤</span>
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
        </section>
    </main>

    <script>
        (() => {
            const searchInput = document.getElementById('quickSearch');
            const tiles = document.querySelectorAll('[data-query]');
            const resultsGallery = document.getElementById('searchResultsGallery');
            const searchEndpoint = '{{ route('home.search.products') }}';
            const bebidasUrl = @json(route('catalog', ['q' => 'bebidas']));
            const pharmacyUrl = @json(route('categories.pharmacy'));
            const almacenUrl = @json(route('categories.almacen'));
            const comidasUrl = @json(route('food-vendors.index'));
            const quickMenu = document.getElementById('quickMenu');
            const quickMenuTrigger = document.getElementById('quickMenuTrigger');
            const quickMenuBackdrop = document.getElementById('quickMenuBackdrop');
            const quickMenuItems = document.querySelectorAll('.quick-menu-item');
            const quickMenuVoiceAction = document.getElementById('quickMenuVoiceAction');
            const galleryMicBtn = document.getElementById('galleryMicBtn');
            const micStopLoader = document.getElementById('micStopLoader');

            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

            let searchTimer = null;
            let activeController = null;
            let isQuickMenuOpen = false;
            let recognition = null;
            let isListening = false;
            let isVoiceMode = false;
            let voiceSearchTimer = null;
            let isPaused = false;
            let micStopTimer = null;

            const showVoiceToast = (message) => {
                let toast = document.getElementById('voiceToast');
                if (!toast) {
                    toast = document.createElement('div');
                    toast.id = 'voiceToast';
                    toast.style.cssText = 'position:fixed;bottom:90px;left:50%;transform:translateX(-50%);' +
                        'background:rgba(30,30,30,0.92);color:#fff;padding:10px 18px;border-radius:20px;' +
                        'font-size:14px;z-index:9999;max-width:88vw;text-align:center;pointer-events:none;' +
                        'transition:opacity .3s;';
                    document.body.appendChild(toast);
                }
                toast.textContent = message;
                toast.style.opacity = '1';
                clearTimeout(toast._timer);
                toast._timer = setTimeout(() => {
                    toast.style.opacity = '0';
                }, 3500);
            };

            const normalize = (value) => (value || '')
                .toString()
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .trim();

            const goToCatalog = (query) => {
                const text = normalize(query);

                if (text.includes('bebida') || text.includes('cerveza') || text.includes('vino') || text.includes(
                        'gaseosa')) {
                    window.location.href = bebidasUrl;
                    return;
                }

                if (text.includes('comida') || text.includes('menu') || text.includes('hamburguesa') || text.includes(
                        'pizza')) {
                    window.location.href = comidasUrl;
                    return;
                }

                if (text.includes('farmacia') || text.includes('remedio') || text.includes('medicamento')) {
                    window.location.href = pharmacyUrl;
                    return;
                }

                window.location.href = almacenUrl;
            };

            const escapeHtml = (value) => (value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            let voiceCountdownInterval = null;

            const clearVoiceSearchTimer = () => {
                if (voiceSearchTimer) {
                    clearTimeout(voiceSearchTimer);
                    voiceSearchTimer = null;
                }
                if (voiceCountdownInterval) {
                    clearInterval(voiceCountdownInterval);
                    voiceCountdownInterval = null;
                }
            };

            const hasPendingVoiceSearch = () => Boolean(voiceSearchTimer);

            const setMicStoppingState = (active) => {
                if (micStopTimer) {
                    clearTimeout(micStopTimer);
                    micStopTimer = null;
                }

                if (micStopLoader) {
                    micStopLoader.classList.toggle('is-active', active);
                }

                if (galleryMicBtn) {
                    galleryMicBtn.disabled = active;
                }

                if (quickMenuVoiceAction) {
                    quickMenuVoiceAction.disabled = active;
                }

                if (active) {
                    micStopTimer = setTimeout(() => {
                        setMicStoppingState(false);
                    }, 560);
                }
            };

            const resetMicToInitialState = () => {
                clearVoiceSearchTimer();
                isVoiceMode = false;
                isListening = false;
                setMicState('idle');
                renderResults([], '');
            };

            const cancelPendingVoiceSearch = () => {
                if (!hasPendingVoiceSearch()) return;

                clearVoiceSearchTimer();
                setMicState('paused');
                const query = searchInput ? searchInput.value.trim() : '';
                if (query && resultsGallery) {
                    resultsGallery.classList.remove('is-empty');
                    if (galleryMicBtn) galleryMicBtn.classList.add('is-hidden');
                    resultsGallery.innerHTML =
                        '<div class="voice-transcript is-final">' +
                        '<p class="voice-text">' + escapeHtml(query) + '</p>' +
                        '<div class="voice-actions">' +
                        '<button class="voice-action-btn is-new" data-voice-action="new">🎤 Buscar de nuevo</button>' +
                        '<button class="voice-action-btn is-resume" data-voice-action="resume">▶️ Continuar</button>' +
                        '</div>' +
                        '</div>';
                }
            };

            const renderResults = (products, query) => {
                if (!resultsGallery) return;

                if (!query || query.length < 2) {
                    resultsGallery.classList.add('is-empty');
                    resultsGallery.innerHTML = '<div class="results-track is-placeholder"></div>';
                    if (galleryMicBtn) galleryMicBtn.classList.remove('is-hidden');
                    return;
                }

                if (galleryMicBtn) galleryMicBtn.classList.add('is-hidden');

                if (!products.length) {
                    resultsGallery.classList.remove('is-empty');
                    resultsGallery.innerHTML = '<p class="results-status">No encontramos resultados para "' +
                        escapeHtml(query) + '".</p>';
                    return;
                }

                const cards = products.map((product) => {
                    const imageHtml = product.image ?
                        '<img src="' + escapeHtml(product.image) + '" alt="' + escapeHtml(product.name) +
                        '">' :
                        '<span class="results-status">Sin imagen</span>';

                    return '<a class="result-card" href="' + escapeHtml(product.url) + '">' +
                        '<div class="result-image">' + imageHtml + '</div>' +
                        '<div class="result-meta">' +
                        '<p class="result-name">' + escapeHtml(product.name) + '</p>' +
                        '<p class="result-price">' + escapeHtml(product.price) + '</p>' +
                        '</div>' +
                        '</a>';
                }).join('');

                resultsGallery.classList.remove('is-empty');
                resultsGallery.innerHTML = '<div class="results-track">' + cards + '</div>';
            };

            const renderVoiceTranscript = (text, isFinal, hintText = 'Tocá el micrófono para pausar') => {
                if (!resultsGallery) return;
                resultsGallery.classList.remove('is-empty');
                if (galleryMicBtn) galleryMicBtn.classList.add('is-hidden');

                if (!text) {
                    resultsGallery.innerHTML = '<div class="voice-transcript is-listening">' +
                        '<span class="voice-pulse">🎙️</span>' +
                        '</div>';
                    return;
                }

                const escaped = escapeHtml(text);
                if (isFinal) {
                    resultsGallery.innerHTML = '<div class="voice-transcript is-final">' +
                        '<p class="voice-text">' + escaped + '</p>' +
                        '<p class="voice-hint" id="voiceHintLabel">' + escapeHtml(hintText) + '</p>' +
                        '</div>';
                } else {
                    resultsGallery.innerHTML = '<div class="voice-transcript is-interim">' +
                        '<p class="voice-text">' + escaped + '</p>' +
                        '<p class="voice-hint" id="voiceHintLabel">🎤 Tocá para pausar</p>' +
                        '</div>';
                }
            };

            const scheduleVoiceResults = (query) => {
                const cleaned = (query || '').trim();
                clearVoiceSearchTimer();

                if (cleaned.length < 2) return;

                setMicState('pending');

                let secs = 3;

                const updateHint = () => {
                    const hint = document.getElementById('voiceHintLabel');
                    if (hint) {
                        hint.className = 'voice-hint is-pending';
                        hint.innerHTML = '⏳ Buscando en <span class="voice-countdown">' + secs +
                            '</span>s · tocá para pausar';
                    }
                };

                renderVoiceTranscript(cleaned, true, '');
                updateHint();

                voiceCountdownInterval = setInterval(() => {
                    secs -= 1;
                    if (secs > 0) {
                        updateHint();
                    } else {
                        clearVoiceSearchTimer();
                        isVoiceMode = false;
                        setMicState('idle');
                        fetchLiveResults(cleaned, 5);
                    }
                }, 1000);

                voiceSearchTimer = setTimeout(() => {
                    // fallback — should already be triggered by interval
                }, 3200);
            };

            const fetchLiveResults = (query, limit = null) => {
                if (activeController) {
                    activeController.abort();
                }

                if (!resultsGallery) return;

                activeController = new AbortController();
                resultsGallery.classList.remove('is-empty');
                resultsGallery.innerHTML = '<p class="results-status">Buscando...</p>';

                fetch(searchEndpoint + '?q=' + encodeURIComponent(query), {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        signal: activeController.signal
                    })
                    .then((response) => {
                        if (!response.ok) throw new Error('search-error');
                        return response.json();
                    })
                    .then((data) => {
                        const products = Array.isArray(data.products) ? data.products : [];
                        renderResults(limit ? products.slice(0, limit) : products, query);
                    })
                    .catch((error) => {
                        if (error.name === 'AbortError') return;
                        resultsGallery.innerHTML = '<p class="results-status">No se pudo buscar ahora.</p>';
                    });
            };

            const setQuickMenuOpen = (open) => {
                isQuickMenuOpen = open;
                if (quickMenu) {
                    quickMenu.classList.toggle('is-open', open);
                }

                if (quickMenuTrigger) {
                    quickMenuTrigger.setAttribute('aria-expanded', open ? 'true' : 'false');
                }

                if (quickMenuBackdrop) {
                    quickMenuBackdrop.hidden = !open;
                    quickMenuBackdrop.classList.toggle('is-open', open);
                }
            };

            const setMicState = (state) => {
                // state: 'idle' | 'listening' | 'pending' | 'paused'
                isPaused = (state === 'paused');
                const micBtns = [quickMenuVoiceAction, galleryMicBtn].filter(Boolean);

                micBtns.forEach((btn) => {
                    btn.classList.remove('is-listening', 'is-pending');
                    if (state === 'listening') btn.classList.add('is-listening');
                    if (state === 'pending') btn.classList.add('is-pending');
                });

                if (quickMenuVoiceAction) {
                    const label = quickMenuVoiceAction.querySelector('.quick-menu-label');
                    if (state === 'listening') {
                        quickMenuVoiceAction.setAttribute('aria-label', 'Detener búsqueda por voz');
                        if (label) label.textContent = 'Escuchando';
                    } else if (state === 'pending') {
                        quickMenuVoiceAction.setAttribute('aria-label', 'Pausar búsqueda por voz');
                        if (label) label.textContent = 'Pausar';
                    } else if (state === 'paused') {
                        quickMenuVoiceAction.setAttribute('aria-label', 'Reanudar búsqueda');
                        if (label) label.textContent = 'Reanudar';
                    } else {
                        quickMenuVoiceAction.setAttribute('aria-label', 'Buscar por voz');
                        if (label) label.textContent = 'Buscar por voz';
                    }
                }

                if (galleryMicBtn) {
                    if (state === 'listening') galleryMicBtn.setAttribute('aria-label', 'Detener escucha');
                    else if (state === 'pending') galleryMicBtn.setAttribute('aria-label', 'Pausar búsqueda');
                    else if (state === 'paused') galleryMicBtn.setAttribute('aria-label', 'Reanudar búsqueda');
                    else galleryMicBtn.setAttribute('aria-label', 'Buscar por voz');
                }
            };

            const stopListening = () => {
                if (recognition && isListening) {
                    recognition.stop();
                }

                isListening = false;
                setMicState('idle');
            };

            const startListening = () => {
                if (!recognition || isListening) return;

                clearVoiceSearchTimer();

                try {
                    recognition.start();
                } catch (error) {
                    stopListening();
                }
            };

            tiles.forEach((tile) => {
                tile.addEventListener('click', () => {
                    const query = tile.getAttribute('data-query') || '';
                    if (searchInput) searchInput.value = query;
                    goToCatalog(query);
                });
            });

            quickMenuItems.forEach((item) => {
                item.addEventListener('click', () => {
                    const query = item.getAttribute('data-menu-query');
                    if (!query) return;
                    setQuickMenuOpen(false);
                    if (searchInput) searchInput.value = query;
                    goToCatalog(query);
                });
            });

            if (SpeechRecognition && quickMenuVoiceAction) {
                recognition = new SpeechRecognition();
                recognition.lang = 'es-AR';
                recognition.interimResults = true;
                recognition.continuous = false;

                recognition.onstart = () => {
                    isListening = true;
                    isVoiceMode = true;
                    setMicState('listening');
                    renderVoiceTranscript('', false);
                };

                recognition.onresult = (event) => {
                    let transcript = '';

                    for (let i = 0; i < event.results.length; i += 1) {
                        transcript += event.results[i][0].transcript;
                    }

                    const cleaned = transcript.trim();
                    const lastResult = event.results[event.results.length - 1];

                    if (searchInput) {
                        searchInput.value = cleaned;
                        // Do NOT dispatch input event — voice mode shows transcript, not products
                    }

                    renderVoiceTranscript(cleaned, lastResult.isFinal);

                    if (lastResult.isFinal) {
                        stopListening();
                        scheduleVoiceResults(cleaned);
                    }
                };

                recognition.onerror = (event) => {
                    stopListening();
                    const code = event.error || '';
                    if (code === 'not-allowed' || code === 'service-not-allowed' || code === 'network') {
                        showVoiceToast('El micrófono requiere HTTPS. Usá la versión en línea.');
                    } else if (code === 'no-speech') {
                        showVoiceToast('No se escuchó nada. Intentá de nuevo.');
                    } else {
                        showVoiceToast('No se pudo iniciar el micrófono.');
                    }
                };

                recognition.onend = () => {
                    stopListening();
                    setMicStoppingState(false);

                    const query = searchInput ? searchInput.value.trim() : '';
                    if (!hasPendingVoiceSearch() && query.length < 2) {
                        resetMicToInitialState();
                    }
                };

                const handleVoiceBtnClick = () => {
                    if (isListening) {
                        setMicStoppingState(true);
                        stopListening();
                        const query = searchInput ? searchInput.value.trim() : '';
                        if (query.length < 2) {
                            resetMicToInitialState();
                        }
                        return;
                    }
                    if (hasPendingVoiceSearch()) {
                        const query = searchInput ? searchInput.value.trim() : '';
                        if (query.length < 2) {
                            resetMicToInitialState();
                            return;
                        }
                        cancelPendingVoiceSearch();
                        return;
                    }
                    const isSecure = location.protocol === 'https:' || location.hostname === 'localhost' || location
                        .hostname === '127.0.0.1';
                    if (!isSecure) {
                        showVoiceToast('El micrófono necesita HTTPS. Funciona en la versión en línea.');
                        return;
                    }
                    startListening();
                };

                quickMenuVoiceAction.addEventListener('click', () => {
                    setQuickMenuOpen(false);
                    handleVoiceBtnClick();
                });

                if (galleryMicBtn) {
                    galleryMicBtn.addEventListener('click', handleVoiceBtnClick);
                }
            } else if (quickMenuVoiceAction) {
                quickMenuVoiceAction.addEventListener('click', () => {
                    setQuickMenuOpen(false);
                    showVoiceToast('Tu navegador no soporta búsqueda por voz.');
                });
                const label = quickMenuVoiceAction.querySelector('.quick-menu-label');
                if (label) label.textContent = 'Sin voz';
            }

            if (quickMenuTrigger) {
                quickMenuTrigger.addEventListener('click', () => {
                    setQuickMenuOpen(!isQuickMenuOpen);
                });
            }

            if (quickMenuBackdrop) {
                quickMenuBackdrop.addEventListener('click', () => {
                    setQuickMenuOpen(false);
                });
            }

            if (resultsGallery) {
                resultsGallery.addEventListener('click', (event) => {
                    const actionBtn = event.target.closest('[data-voice-action]');
                    if (actionBtn) {
                        const action = actionBtn.getAttribute('data-voice-action');
                        if (action === 'resume') {
                            const query = searchInput ? searchInput.value.trim() : '';
                            if (query.length >= 2) scheduleVoiceResults(query);
                        } else if (action === 'new') {
                            if (searchInput) searchInput.value = '';
                            renderResults([], '');
                            const isSecure = location.protocol === 'https:' || location.hostname ===
                                'localhost' || location.hostname === '127.0.0.1';
                            if (!isSecure) {
                                showVoiceToast('El micrófono necesita HTTPS. Funciona en la versión en línea.');
                                return;
                            }
                            startListening();
                        }
                        return;
                    }

                    // click on transcript during countdown → pause
                    const transcript = event.target.closest('.voice-transcript');
                    if (!transcript || !hasPendingVoiceSearch()) return;
                    cancelPendingVoiceSearch();
                });
            }

            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    isVoiceMode = false; // Manual typing resets voice mode
                    clearVoiceSearchTimer();
                    const query = searchInput.value.trim();

                    if (searchTimer) {
                        clearTimeout(searchTimer);
                    }

                    if (query.length < 2) {
                        renderResults([], '');
                        return;
                    }

                    searchTimer = setTimeout(() => {
                        fetchLiveResults(query);
                    }, 260);
                });

                searchInput.addEventListener('keydown', (event) => {
                    if (event.key !== 'Enter') return;
                    if (isVoiceMode) {
                        // Voice mode: show products for the transcribed text
                        clearVoiceSearchTimer();
                        isVoiceMode = false;
                        const query = searchInput.value.trim();
                        if (query.length >= 2) {
                            fetchLiveResults(query);
                        }
                    } else {
                        goToCatalog(searchInput.value);
                    }
                });
            }

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    setQuickMenuOpen(false);
                }
            });
        })();
    </script>
</body>

</html>
