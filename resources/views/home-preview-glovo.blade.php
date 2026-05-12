<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bari Tienda | Home Preview</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,700|outfit:300,400,600,700" rel="stylesheet" />
    <style>
        :root {
            --brand: #6d28d9;
            --brand-strong: #4c1d95;
            --brand-soft: #ede9fe;
            --brand-mid: #7c3aed;
            --accent: #16a34a;
            --ink: #1f2937;
            --panel: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Outfit', sans-serif;
            color: var(--ink);
            background:
                radial-gradient(900px 460px at -15% -20%, rgba(124, 58, 237, 0.18) 0%, transparent 65%),
                radial-gradient(560px 320px at 110% 0%, rgba(34, 197, 94, 0.1) 0%, transparent 70%),
                linear-gradient(160deg, #f8f7ff 0%, #f2efff 45%, #eef7ff 100%);
            min-height: 100vh;
        }

        .page {
            max-width: 760px;
            margin: 0 auto;
            min-height: 100vh;
            display: grid;
            align-content: center;
            padding: 1rem 0.95rem 1.8rem;
        }

        .menu-shell {
            margin-top: 0;
            background: var(--panel);
            border: 1px solid #e6dcff;
            border-radius: 28px;
            padding: 1rem 0.85rem 1.1rem;
            box-shadow: 0 20px 44px rgba(69, 36, 140, 0.1), inset 0 0 0 1px rgba(255, 255, 255, 0.55);
        }

        .voice-panel {
            margin-bottom: 0.8rem;
            border: 1px solid #e7ddff;
            border-radius: 22px;
            background: linear-gradient(180deg, #fcfaff 0%, #f8f5ff 100%);
            padding: 0.9rem;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.85);
        }

        .voice-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .voice-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            color: #35205f;
        }

        .voice-status {
            font-size: 0.8rem;
            color: #66547f;
            font-weight: 700;
        }

        .voice-inline-hint {
            font-size: 0.78rem;
            font-weight: 700;
            color: #6f63a2;
            background: #f2edff;
            border: 1px solid #e3d8ff;
            border-radius: 999px;
            padding: 0.3rem 0.65rem;
        }

        .voice-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
            margin-top: 0.75rem;
        }

        .voice-button,
        .voice-secondary {
            border: 0;
            border-radius: 14px;
            padding: 0.72rem 0.95rem;
            font-family: inherit;
            font-size: 0.9rem;
            font-weight: 800;
            cursor: pointer;
            transition: transform 180ms ease, box-shadow 180ms ease, background 180ms ease;
        }

        .voice-button {
            color: #fff;
            background: linear-gradient(145deg, var(--brand-mid) 0%, var(--brand-strong) 100%);
            box-shadow: 0 12px 24px rgba(70, 30, 152, 0.25);
        }

        .voice-button.is-listening {
            background: linear-gradient(145deg, #ef4444 0%, #b91c1c 100%);
            box-shadow: 0 12px 24px rgba(185, 28, 28, 0.24);
        }

        .voice-secondary {
            color: #4b2c86;
            background: #f5f2ff;
            border: 1px solid #ddd0ff;
        }

        .voice-button:hover,
        .voice-secondary:hover {
            transform: translateY(-1px);
        }

        .voice-transcript {
            margin-top: 0.8rem;
            padding: 0.8rem 0.9rem;
            border-radius: 16px;
            background: #fff;
            border: 1px solid #ebe2ff;
            color: #34234f;
            min-height: 60px;
            line-height: 1.45;
            font-size: 0.92rem;
        }

        .voice-transcript strong {
            color: #5a2ec6;
        }

        .brand-strip {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.7rem;
        }

        .brand-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border-radius: 999px;
            padding: 0.35rem 0.65rem;
            background: linear-gradient(90deg, #f0eaff 0%, #e6f8ed 100%);
            color: #3b2070;
            font-size: 0.79rem;
            font-weight: 800;
            letter-spacing: 0.01em;
        }

        .brand-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: var(--accent);
            box-shadow: 0 0 0 4px rgba(22, 163, 74, 0.15);
        }

        .zone {
            color: #5f5b78;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .search-wrap {
            margin-top: 0.25rem;
        }

        .search {
            width: 100%;
            border: 0;
            outline: none;
            border-radius: 999px;
            background: #fff;
            box-shadow: inset 0 0 0 2px #dfd3ff;
            color: #4f2a8f;
            padding: 0.8rem 1rem;
            font-size: 1rem;
            font-family: inherit;
            transition: box-shadow 180ms ease;
        }

        .search:focus {
            box-shadow: inset 0 0 0 2px #bda5f8, 0 0 0 4px rgba(109, 40, 217, 0.12);
        }

        .search::placeholder {
            color: #8465ba;
        }

        .bubble-grid {
            margin: 1.1rem auto 0.2rem;
            width: min(100%, 460px);
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            grid-template-rows: repeat(3, minmax(98px, 1fr));
            gap: 0.8rem;
        }

        .bubble {
            border: 0;
            border-radius: 36px;
            background: #fff;
            box-shadow: 0 10px 26px rgba(51, 28, 102, 0.13);
            color: #2f1f4f;
            display: grid;
            place-items: center;
            text-align: center;
            padding: 0.55rem;
            cursor: pointer;
            transition: transform 180ms ease, box-shadow 180ms ease;
            animation: rise 600ms ease both;
        }

        .bubble:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 28px rgba(73, 34, 145, 0.24);
        }

        .bubble:active {
            transform: translateY(0);
        }

        .bubble.is-selected {
            box-shadow: 0 0 0 3px rgba(109, 40, 217, 0.2), 0 16px 28px rgba(73, 34, 145, 0.24);
        }

        .bubble.is-voice-match {
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.16), 0 16px 28px rgba(73, 34, 145, 0.24);
        }

        .bubble-1 { grid-column: 2; grid-row: 1; animation-delay: 60ms; }
        .bubble-2 { grid-column: 1; grid-row: 2; animation-delay: 120ms; }
        .bubble-3 { grid-column: 3; grid-row: 2; animation-delay: 180ms; }
        .bubble-4 { grid-column: 1; grid-row: 3; animation-delay: 240ms; }
        .bubble-5 { grid-column: 2; grid-row: 2; animation-delay: 300ms; }
        .bubble-6 { grid-column: 3; grid-row: 3; animation-delay: 360ms; }

        .bubble-main {
            background: linear-gradient(155deg, var(--brand-mid) 0%, var(--brand-strong) 100%);
            color: #fff;
            transform: scale(1.15);
            box-shadow: 0 18px 30px rgba(70, 30, 152, 0.35);
        }

        .icon {
            width: 58px;
            height: 58px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: var(--brand-soft);
            margin: 0 auto 0.5rem;
            font-size: 1.7rem;
            line-height: 1;
        }

        .bubble-main .icon {
            width: 64px;
            height: 64px;
            font-size: 1.85rem;
            background: #faf7ff;
        }

        .bubble-label {
            display: block;
            line-height: 1.05;
            font-size: 0.92rem;
            font-weight: 800;
        }

        .hint {
            margin: 0.9rem auto 0;
            width: min(100%, 460px);
            background: #f5f2ff;
            border: 1px solid #e2d6ff;
            color: #4b2c86;
            border-radius: 14px;
            font-size: 0.85rem;
            font-weight: 700;
            padding: 0.55rem 0.7rem;
            text-align: center;
        }

        .voice-fab {
            position: fixed;
            right: 16px;
            bottom: 96px;
            z-index: 80;
            border: 0;
            border-radius: 999px;
            min-width: 58px;
            height: 58px;
            padding: 0 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: linear-gradient(145deg, var(--brand-mid) 0%, var(--brand-strong) 100%);
            color: #fff;
            font-family: inherit;
            font-size: 0.9rem;
            font-weight: 800;
            box-shadow: 0 14px 26px rgba(70, 30, 152, 0.3);
            cursor: pointer;
            transition: transform 160ms ease, box-shadow 160ms ease, background 160ms ease;
            outline: 3px solid rgba(255, 255, 255, 0.92);
        }

        .voice-fab:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 30px rgba(70, 30, 152, 0.34);
        }

        .voice-fab.is-listening {
            background: linear-gradient(145deg, #ef4444 0%, #b91c1c 100%);
            box-shadow: 0 14px 26px rgba(185, 28, 28, 0.3);
        }

        .voice-fab[disabled] {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .voice-fab-icon {
            font-size: 1.1rem;
            line-height: 1;
        }

        .voice-fab-label {
            white-space: nowrap;
        }

        @keyframes rise {
            from {
                opacity: 0;
                transform: translateY(14px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (min-width: 860px) {
            .page {
                padding: 1.2rem;
            }

            .bubble-grid {
                width: min(100%, 560px);
                grid-template-rows: repeat(3, minmax(118px, 1fr));
                gap: 0.92rem;
            }

            .icon {
                width: 64px;
                height: 64px;
                font-size: 1.9rem;
            }

            .bubble-main .icon {
                width: 72px;
                height: 72px;
                font-size: 2.1rem;
            }

            .bubble-label {
                font-size: 0.98rem;
            }

            .hint {
                width: min(100%, 560px);
            }

            .voice-fab {
                right: 24px;
                bottom: 106px;
            }
        }
    </style>
</head>

<body>
    <main class="page">
        <section class="menu-shell">
            <div class="brand-strip">
                <div class="brand-tag">
                    <span class="brand-dot" aria-hidden="true"></span>
                    Bari Tienda Express
                </div>
                <span class="zone">Entrega en Bariloche</span>
            </div>

            <section class="voice-panel" aria-label="Pedido por voz">
                <div class="voice-top">
                    <div>
                        <div class="voice-title">Pedir con tu voz</div>
                        <div class="voice-status" id="voiceStatus">Listo para escuchar</div>
                    </div>
                    <span class="voice-inline-hint">Usa el boton flotante</span>
                </div>

                <div id="voiceTranscript" class="voice-transcript">
                    Deci algo como: <strong>quiero una coca, pan y farmacia</strong>
                </div>

                <div class="voice-actions">
                    <button id="voiceApply" class="voice-secondary" type="button">Usar este pedido</button>
                    <button id="voiceReset" class="voice-secondary" type="button">Limpiar</button>
                </div>
            </section>

            <div class="search-wrap">
                <input id="quickSearch" class="search" type="search" placeholder="Que necesitas hoy?" aria-label="Buscar en Bari Tienda">
            </div>

            <div class="bubble-grid">
                <button class="bubble bubble-1" type="button" data-query="comida">
                    <div>
                        <span class="icon">🍽️</span>
                        <span class="bubble-label">Comida</span>
                    </div>
                </button>

                <button class="bubble bubble-2" type="button" data-query="regalos">
                    <div>
                        <span class="icon">🧁</span>
                        <span class="bubble-label">Regalos y mas</span>
                    </div>
                </button>

                <button class="bubble bubble-3" type="button" data-query="super hogar">
                    <div>
                        <span class="icon">🛒</span>
                        <span class="bubble-label">Super y hogar</span>
                    </div>
                </button>

                <button class="bubble bubble-4" type="button" data-query="farmacia">
                    <div>
                        <span class="icon">💊</span>
                        <span class="bubble-label">Farmacia</span>
                    </div>
                </button>

                <button class="bubble bubble-5 bubble-main" type="button" data-query="lo que sea">
                    <div>
                        <span class="icon">⭐</span>
                        <span class="bubble-label">Lo que sea</span>
                    </div>
                </button>

                <button class="bubble bubble-6" type="button" data-query="retira envia">
                    <div>
                        <span class="icon">📦</span>
                        <span class="bubble-label">Retira y envia</span>
                    </div>
                </button>
            </div>

            <p class="hint">Toque un boton para autocompletar busqueda o escriba y presione Enter.</p>
        </section>
    </main>

    <button id="voiceFab" class="voice-fab" type="button" aria-label="Activar pedido por voz">
        <span class="voice-fab-icon">🎙️</span>
        <span class="voice-fab-label">Hablar</span>
    </button>

    <script>
        (() => {
            const searchInput = document.getElementById('quickSearch');
            const buttons = document.querySelectorAll('[data-query]');
            const voiceFab = document.getElementById('voiceFab');
            const voiceApply = document.getElementById('voiceApply');
            const voiceReset = document.getElementById('voiceReset');
            const voiceStatus = document.getElementById('voiceStatus');
            const voiceTranscript = document.getElementById('voiceTranscript');

            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            let recognition = null;
            let isListening = false;

            const normalize = (value) => (value || '')
                .toString()
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .trim();

            const categories = [
                { key: 'comida', button: document.querySelector('[data-query="comida"]') },
                { key: 'regalos', button: document.querySelector('[data-query="regalos"]') },
                { key: 'super hogar', button: document.querySelector('[data-query="super hogar"]') },
                { key: 'super', button: document.querySelector('[data-query="super hogar"]') },
                { key: 'hogar', button: document.querySelector('[data-query="super hogar"]') },
                { key: 'farmacia', button: document.querySelector('[data-query="farmacia"]') },
                { key: 'lo que sea', button: document.querySelector('[data-query="lo que sea"]') },
                { key: 'retira', button: document.querySelector('[data-query="retira envia"]') },
                { key: 'envia', button: document.querySelector('[data-query="retira envia"]') },
            ].filter((item) => item.button);

            const clearMatches = () => {
                buttons.forEach((item) => item.classList.remove('is-selected', 'is-voice-match'));
            };

            const markMatch = (query) => {
                const text = normalize(query);
                clearMatches();

                buttons.forEach((item) => {
                    const itemQuery = normalize(item.getAttribute('data-query'));
                    if (itemQuery && text.includes(itemQuery)) {
                        item.classList.add('is-voice-match');
                    }
                });

                categories.forEach(({ key, button }) => {
                    if (text.includes(key)) {
                        button.classList.add('is-voice-match');
                    }
                });

                if (!buttons.length) return;

                if (text.includes('lo que sea') || text.length === 0) {
                    const main = document.querySelector('[data-query="lo que sea"]');
                    if (main) main.classList.add('is-voice-match');
                }
            };

            const updateTranscript = (text, final = false) => {
                if (!voiceTranscript) return;
                voiceTranscript.innerHTML = final
                    ? `Te entendí: <strong>${text || 'nada todavía'}</strong>`
                    : `Escuchando: <strong>${text || '...'}</strong>`;
                if (searchInput) searchInput.value = text;
                markMatch(text);
            };

            const goToVoiceResult = (query) => {
                const target = '/pedido-voz?pedido=' + encodeURIComponent(query || '');
                window.location.href = target;
            };

            const stopListening = () => {
                if (recognition && isListening) {
                    recognition.stop();
                }
                isListening = false;
                if (voiceFab) {
                    const label = voiceFab.querySelector('.voice-fab-label');
                    if (label) label.textContent = 'Hablar';
                    voiceFab.classList.remove('is-listening');
                }
                if (voiceStatus) voiceStatus.textContent = 'Listo para escuchar';
            };

            const startListening = () => {
                if (!recognition || isListening) return;
                try {
                    recognition.start();
                } catch (error) {
                    if (voiceStatus) voiceStatus.textContent = 'El navegador bloqueó el micrófono.';
                    stopListening();
                }
            };

            if (voiceFab && SpeechRecognition) {
                recognition = new SpeechRecognition();
                recognition.lang = 'es-AR';
                recognition.interimResults = true;
                recognition.continuous = false;

                recognition.onstart = () => {
                    isListening = true;
                    const label = voiceFab.querySelector('.voice-fab-label');
                    if (label) label.textContent = 'Detener';
                    voiceFab.classList.add('is-listening');
                    if (voiceStatus) voiceStatus.textContent = 'Escuchando tu pedido...';
                };

                recognition.onresult = (event) => {
                    let transcript = '';

                    for (let i = 0; i < event.results.length; i += 1) {
                        transcript += event.results[i][0].transcript;
                    }

                    const cleaned = transcript.trim();
                    const lastResult = event.results[event.results.length - 1];
                    updateTranscript(cleaned, lastResult.isFinal);

                    if (lastResult.isFinal && cleaned) {
                        stopListening();
                        goToVoiceResult(cleaned);
                    }
                };

                recognition.onerror = () => {
                    if (voiceStatus) voiceStatus.textContent = 'No se pudo escuchar. Probá de nuevo.';
                    stopListening();
                };

                recognition.onend = () => {
                    stopListening();
                };

                voiceFab.addEventListener('click', () => {
                    if (isListening) {
                        stopListening();
                        return;
                    }
                    startListening();
                });
            } else if (voiceFab) {
                voiceFab.disabled = true;
                const label = voiceFab.querySelector('.voice-fab-label');
                if (label) label.textContent = 'Sin voz';
                if (voiceStatus) voiceStatus.textContent = 'Tu navegador no soporta reconocimiento de voz';
            }

            if (!searchInput || !buttons.length) return;

            const goToCatalog = (query) => {
                const target = '/?q=' + encodeURIComponent(query || '') + '#productos';
                window.location.href = target;
            };

            buttons.forEach((button) => {
                button.addEventListener('click', () => {
                    const query = button.getAttribute('data-query') || '';
                    searchInput.value = query;

                    buttons.forEach((item) => item.classList.remove('is-selected'));
                    button.classList.add('is-selected');

                    goToCatalog(query);
                });
            });

            if (voiceApply) {
                voiceApply.addEventListener('click', () => {
                    goToVoiceResult(searchInput.value.trim());
                });
            }

            if (voiceReset) {
                voiceReset.addEventListener('click', () => {
                    searchInput.value = '';
                    clearMatches();
                    if (voiceTranscript) {
                        voiceTranscript.innerHTML = 'Deci algo como: <strong>quiero una coca, pan y farmacia</strong>';
                    }
                    if (voiceStatus) voiceStatus.textContent = 'Listo para escuchar';
                    stopListening();
                });
            }

            searchInput.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter') return;
                goToCatalog(searchInput.value.trim());
            });
        })();
    </script>
</body>

</html>
