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
</head>

<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] antialiased">

    @include('partials.header')

    <main class="max-w-7xl mx-auto px-4 py-8 md:px-10 lg:px-20 md:py-10">
        <section
            class="rounded-3xl bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-700 px-6 py-8 text-white shadow-xl md:px-8">
            <h1 class="text-3xl font-black uppercase md:text-4xl">{{ $categoryTitle ?? 'Categoria' }}</h1>

            <form id="search-form" method="GET"
                action="{{ $categorySlug === 'almacen' ? route('categories.supermarkets') : route('categories.carrefour', ['categorySlug' => $categorySlug]) }}"
                class="mt-5 max-w-xl">
                <label for="supermarket-search" class="sr-only">Buscar productos de supermercado</label>
                <div class="flex">
                    <input id="supermarket-search" type="text" name="q" value="{{ $search }}"
                        placeholder="¿Qué necesitás hoy? Ej: leche, arroz..." autocomplete="off"
                        class="w-full rounded-2xl border border-white/20 bg-white px-4 py-3 text-sm font-semibold text-gray-800 outline-none">
                </div>
                <p id="search-status" class="mt-2 text-xs font-semibold text-white/70 hidden">Buscando...</p>
            </form>
        </section>

        <section class="mt-8" id="products-section">
            <div class="voice-center-wrap">
                <button id="centerVoiceBtn" type="button" class="voice-center-btn"
                    aria-label="Activar búsqueda por voz">
                    <span class="voice-center-icon" aria-hidden="true">🎙️</span>
                </button>
                <p class="voice-center-text">¿Qué necesitás hoy?</p>
            </div>

            <div id="productsResultsContainer">
                @if ($products->isEmpty())
                    <div
                        class="rounded-3xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center text-gray-500">
                        No hay productos para esta búsqueda.
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
                                            Precio
                                            actualizado</p>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <div id="products-pagination" class="mt-8">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </section>
    </main>

    <div id="qmBackdrop" class="qm-backdrop" hidden></div>

    <aside id="quickMenu" class="qm" aria-label="Accesos rápidos">
        <button id="qmTrigger" class="qm-trigger" type="button" aria-label="Abrir accesos rápidos"
            aria-expanded="false">☰</button>

        <div id="qmItems">
            <button id="qmVoice" class="qm-item qm-voice" type="button" data-index="1" aria-label="Buscar por voz">
                <span class="qm-icon">🎙️</span>
                <span class="qm-label">Buscar por voz</span>
            </button>
            <a href="{{ route('home') }}" class="qm-item" data-index="2" aria-label="Inicio">
                🏠
                <span class="qm-label">Inicio</span>
            </a>
            <a href="{{ route('categories.pharmacy') }}" class="qm-item" data-index="3" aria-label="Farmacia">
                💊
                <span class="qm-label">Farmacia</span>
            </a>
        </div>
    </aside>

    <style>
        .voice-center-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            margin: 0 0 1rem;
            padding: 0.75rem 0;
        }

        .voice-center-btn {
            width: 62px;
            height: 62px;
            border: 0;
            border-radius: 999px;
            color: #fff;
            cursor: pointer;
            background: linear-gradient(160deg, #7c3aed 0%, #4c1d95 100%);
            box-shadow: 0 10px 22px rgba(48, 37, 94, 0.24);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 180ms ease, box-shadow 180ms ease;
        }

        .voice-center-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 26px rgba(48, 37, 94, 0.28);
        }

        .voice-center-btn.is-listening {
            background: linear-gradient(145deg, #ef4444 0%, #b91c1c 100%);
            box-shadow: 0 14px 24px rgba(185, 28, 28, 0.35);
        }

        .voice-center-icon {
            font-size: 1.35rem;
            line-height: 1;
        }

        .voice-center-text {
            margin: 0;
            font-size: 0.9rem;
            font-weight: 700;
            color: #3f4a72;
            letter-spacing: 0.01em;
        }

        .products-loader {
            min-height: 220px;
            border: 1px dashed #d4d9eb;
            border-radius: 1.5rem;
            background: linear-gradient(180deg, #ffffff 0%, #f7f9ff 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            color: #4b587f;
            text-align: center;
            padding: 1rem;
        }

        .products-loader-spinner {
            width: 34px;
            height: 34px;
            border-radius: 999px;
            border: 3px solid #dbe1f5;
            border-top-color: #4c1d95;
            animation: spin-loader 850ms linear infinite;
        }

        .products-loader-text {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 700;
        }

        @keyframes spin-loader {
            to {
                transform: rotate(360deg);
            }
        }

        .qm-backdrop {
            position: fixed;
            inset: 0;
            z-index: 88;
            background: transparent;
            opacity: 0;
            pointer-events: none;
            transition: opacity 180ms ease;
        }

        .qm-backdrop.is-open {
            opacity: 1;
            pointer-events: auto;
        }

        .qm {
            position: fixed;
            right: 20px;
            bottom: 22px;
            z-index: 89;
            width: 170px;
            height: 200px;
            pointer-events: none;
        }

        .qm button,
        .qm a {
            border: 0;
            cursor: pointer;
            font: inherit;
            text-decoration: none;
        }

        .qm-trigger,
        .qm-item {
            position: absolute;
            right: 0;
            bottom: 0;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 22px rgba(48, 37, 94, 0.22);
        }

        .qm-trigger {
            width: 62px;
            height: 62px;
            color: #fff;
            background: linear-gradient(160deg, #7c3aed 0%, #4c1d95 100%);
            pointer-events: auto;
            transition: transform 180ms ease, box-shadow 180ms ease;
        }

        .qm-trigger:hover {
            transform: scale(1.05);
            box-shadow: 0 14px 26px rgba(48, 37, 94, 0.28);
        }

        .qm-item {
            width: 52px;
            height: 52px;
            color: #4c1d95;
            background: #fff;
            border: 1px solid #d9def3;
            opacity: 0;
            pointer-events: none;
            transform: translate3d(0, 0, 0) scale(0.7);
            transition: transform 220ms ease, opacity 220ms ease, box-shadow 220ms ease;
            font-size: 1.35rem;
        }

        .qm-item:hover {
            box-shadow: 0 14px 24px rgba(48, 37, 94, 0.20);
        }

        .qm-item.qm-voice {
            color: #fff;
            border-color: rgba(255, 255, 255, 0.22);
            background: linear-gradient(160deg, #7c3aed 0%, #4c1d95 100%);
        }

        .qm-item.is-listening {
            color: #fff;
            border-color: rgba(255, 255, 255, 0.24);
            background: linear-gradient(145deg, #ef4444 0%, #b91c1c 100%);
            box-shadow: 0 14px 24px rgba(185, 28, 28, 0.35);
        }

        .qm-icon {
            font-size: 1.35rem;
            line-height: 1;
        }

        .qm-label {
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

        .qm-item:hover .qm-label,
        .qm-item:focus-visible .qm-label {
            opacity: 1;
        }

        .qm.is-open .qm-item {
            opacity: 1;
            pointer-events: auto;
        }

        .qm.is-open .qm-item[data-index='1'] {
            transform: translate3d(0, -104px, 0) scale(1);
        }

        .qm.is-open .qm-item[data-index='2'] {
            transform: translate3d(-74px, -74px, 0) scale(1);
        }

        .qm.is-open .qm-item[data-index='3'] {
            transform: translate3d(-104px, 0, 0) scale(1);
        }

        @media (max-width: 720px) {
            .qm {
                right: 14px;
                bottom: 14px;
                width: 150px;
                height: 180px;
            }

            .qm-trigger {
                width: 58px;
                height: 58px;
            }

            .qm-item {
                width: 46px;
                height: 46px;
            }

            .qm.is-open .qm-item[data-index='1'] {
                transform: translate3d(0, -86px, 0) scale(1);
            }

            .qm.is-open .qm-item[data-index='2'] {
                transform: translate3d(-62px, -62px, 0) scale(1);
            }

            .qm.is-open .qm-item[data-index='3'] {
                transform: translate3d(-86px, 0, 0) scale(1);
            }
        }
    </style>

    <script>
        (() => {
            const input = document.getElementById('supermarket-search');
            const status = document.getElementById('search-status');
            const baseUrl = document.getElementById('search-form').action;

            // ── Quick menu ────────────────────────────────────────────
            const qm = document.getElementById('quickMenu');
            const qmTrigger = document.getElementById('qmTrigger');
            const qmBackdrop = document.getElementById('qmBackdrop');
            const qmVoice = document.getElementById('qmVoice');
            const centerVoiceBtn = document.getElementById('centerVoiceBtn');
            let qmOpen = false;

            const setQmOpen = (val) => {
                qmOpen = val;
                qm.classList.toggle('is-open', val);
                qmTrigger.setAttribute('aria-expanded', val ? 'true' : 'false');
                qmBackdrop.hidden = !val;
                qmBackdrop.classList.toggle('is-open', val);
            };

            qmTrigger.addEventListener('click', () => setQmOpen(!qmOpen));
            qmBackdrop.addEventListener('click', () => setQmOpen(false));

            // ── Voice search ──────────────────────────────────────────
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            let recognition = null;
            let isListening = false;

            const showToast = (msg) => {
                let t = document.getElementById('voiceToast');
                if (!t) {
                    t = document.createElement('div');
                    t.id = 'voiceToast';
                    t.style.cssText =
                        'position:fixed;bottom:90px;left:50%;transform:translateX(-50%);background:rgba(20,20,20,.9);color:#fff;padding:10px 18px;border-radius:20px;font-size:14px;z-index:9999;max-width:88vw;text-align:center;transition:opacity .3s;';
                    document.body.appendChild(t);
                }
                t.textContent = msg;
                t.style.opacity = '1';
                clearTimeout(t._t);
                t._t = setTimeout(() => {
                    t.style.opacity = '0';
                }, 3500);
            };

            const setListening = (val) => {
                isListening = val;

                if (centerVoiceBtn) {
                    centerVoiceBtn.classList.toggle('is-listening', val);
                    centerVoiceBtn.setAttribute('aria-label', val ? 'Detener búsqueda por voz' :
                        'Activar búsqueda por voz');
                }

                if (qmVoice) {
                    qmVoice.classList.toggle('is-listening', val);
                    qmVoice.setAttribute('aria-label', val ? 'Detener búsqueda por voz' : 'Buscar por voz');
                    const lbl = qmVoice.querySelector('.qm-label');
                    if (lbl) lbl.textContent = val ? 'Escuchando' : 'Buscar por voz';
                }
            };

            const stopListening = () => {
                if (recognition && isListening) {
                    try {
                        recognition.stop();
                    } catch (e) {
                        // noop
                    }
                }
                setListening(false);
            };

            const startVoice = () => {
                const isSecure = location.protocol === 'https:' || location.hostname === 'localhost' || location
                    .hostname === '127.0.0.1';
                if (!isSecure) {
                    showToast('El micrófono necesita HTTPS. Funciona en la versión en línea.');
                    return;
                }
                if (!recognition) {
                    showToast('Tu navegador no soporta búsqueda por voz.');
                    return;
                }
                try {
                    recognition.start();
                } catch (e) {
                    stopListening();
                }
            };

            if (SpeechRecognition) {
                recognition = new SpeechRecognition();
                recognition.lang = 'es-AR';
                recognition.interimResults = true;
                recognition.continuous = false;

                recognition.onstart = () => setListening(true);
                recognition.onend = () => stopListening();
                recognition.onerror = (e) => {
                    stopListening();
                    if (e.error === 'not-allowed' || e.error === 'service-not-allowed') {
                        showToast('El micrófono requiere HTTPS. Funciona en la versión en línea.');
                    } else if (e.error === 'no-speech') {
                        showToast('No se escuchó nada. Intentá de nuevo.');
                    } else {
                        showToast('No se pudo iniciar el micrófono.');
                    }
                };
                recognition.onresult = (event) => {
                    let transcript = '';
                    for (let i = 0; i < event.results.length; i++) {
                        transcript += event.results[i][0].transcript;
                    }
                    input.value = transcript.trim();
                    if (event.results[event.results.length - 1].isFinal) {
                        stopListening();
                        fetchProducts(input.value.trim());
                    }
                };
            }

            if (qmVoice) {
                qmVoice.addEventListener('click', () => {
                    setQmOpen(false);
                    if (isListening) {
                        stopListening();
                        return;
                    }
                    startVoice();
                });
            }

            if (centerVoiceBtn) {
                centerVoiceBtn.addEventListener('click', () => {
                    if (isListening) {
                        stopListening();
                        return;
                    }
                    startVoice();
                });
            }

            // ── Live search ───────────────────────────────────────────

            const replaceSection = (html) => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                const newContainer = doc.getElementById('productsResultsContainer');
                const currentContainer = document.getElementById('productsResultsContainer');
                if (!newContainer || !currentContainer) return;

                currentContainer.innerHTML = newContainer.innerHTML;
            };

            const renderLoading = () => {
                const container = document.getElementById('productsResultsContainer');
                if (!container) return;

                container.innerHTML = '<div class="products-loader">' +
                    '<div class="products-loader-spinner" aria-hidden="true"></div>' +
                    '<p class="products-loader-text">Buscando los mejores resultados...</p>' +
                    '</div>';
            };

            let timer = null;
            let controller = null;

            input.addEventListener('input', () => {
                clearTimeout(timer);
                const q = input.value.trim();

                if (q.length === 0) {
                    timer = setTimeout(() => fetchProducts(''), 300);
                    return;
                }

                if (q.length < 2) return;

                status.classList.remove('hidden');
                status.textContent = 'Buscando...';

                timer = setTimeout(() => fetchProducts(q), 350);
            });

            const fetchProducts = (q) => {
                if (controller) controller.abort();
                controller = new AbortController();

                renderLoading();

                const url = new URL(baseUrl);
                if (q) url.searchParams.set('q', q);

                fetch(url.toString(), {
                        signal: controller.signal,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(r => r.text())
                    .then(html => {
                        replaceSection(html);
                        status.classList.add('hidden');
                    })
                    .catch(err => {
                        if (err.name !== 'AbortError') {
                            status.textContent = 'Error al buscar.';
                        }
                    });
            };
        })();
    </script>
</body>

</html>
