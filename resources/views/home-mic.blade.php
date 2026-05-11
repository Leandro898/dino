<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bari Tienda | Home Mic</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,700|outfit:300,400,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --bg-1: #f3f5fb;
            --bg-2: #eef1fb;
            --panel: #ffffff;
            --panel-soft: #f8f9ff;
            --brand: #5b2bc9;
            --brand-strong: #3f1f92;
            --brand-soft: #ece6ff;
            --ink: #1f2937;
            --ink-muted: #5f6781;
            --line: #e3e7f4;
            --line-brand: #d7cdf9;
            --accent: #16a34a;
            --shadow: 0 18px 38px rgba(37, 50, 86, 0.12);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Outfit', sans-serif;
            color: var(--ink);
            background: linear-gradient(180deg, var(--bg-1) 0%, var(--bg-2) 100%);
            min-height: 100vh;
        }

        .page {
            width: min(100%, 940px);
            margin: 0 auto;
            min-height: calc(100vh - 60px);
            display: grid;
            align-content: start;
            padding: 1rem;
            gap: 1rem;
        }

        #globalVoiceFab {
            display: none !important;
        }

        .menu-shell {
            background: linear-gradient(180deg, #ffffff 0%, #fafbff 100%);
            border: 1px solid var(--line);
            border-radius: 32px;
            padding: 1rem;
            box-shadow: var(--shadow);
            backdrop-filter: blur(2px);
        }

        .voice-strip {
            display: grid;
            gap: 0.75rem;
            margin-bottom: 0.9rem;
        }

        .voice-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.65rem;
            flex-wrap: wrap;
        }

        .voice-status {
            font-size: 0.88rem;
            color: var(--ink-muted);
            font-weight: 600;
        }

        .voice-transcript {
            padding: 0.8rem 0.95rem;
            border-radius: 16px;
            background: #fff;
            border: 1px solid #e3ddfa;
            color: #35295d;
            min-height: 56px;
            line-height: 1.45;
            font-size: 0.95rem;
        }

        .voice-transcript strong {
            color: var(--brand);
            font-weight: 700;
        }

        .voice-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
        }

        .voice-secondary {
            border: 1px solid #d6cef4;
            .voice-primary-action {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                border: 0;
                border-radius: 999px;
                width: 88px;
                height: 88px;
                font-family: inherit;
                font-size: 1.4rem;
                font-weight: 800;
                letter-spacing: 0.01em;
                cursor: pointer;
                color: #fff;
                background: linear-gradient(160deg, #6a35e1 0%, #4620a5 100%);
                box-shadow: 0 18px 40px rgba(70, 32, 165, 0.35);
                transition: transform 180ms ease, box-shadow 180ms ease, filter 180ms ease;
                animation: pulse-ring 2.5s ease-in-out infinite;
            }

            .voice-primary-action:hover {
                transform: scale(1.08);
                box-shadow: 0 24px 48px rgba(70, 32, 165, 0.45);
            }

            .voice-primary-action:active {
                transform: scale(0.96);
            }

            .voice-primary-action.is-listening {
                background: linear-gradient(160deg, #ef4444 0%, #b91c1c 100%);
                box-shadow: 0 18px 40px rgba(185, 28, 28, 0.35);
                animation: pulse-ring-active 1.2s ease-in-out infinite;
            }

            @keyframes pulse-ring {
                0% {
                    box-shadow: 0 18px 40px rgba(70, 32, 165, 0.35), 0 0 0 0 rgba(106, 53, 225, 0.4);
                }
                50% {
                    box-shadow: 0 18px 40px rgba(70, 32, 165, 0.35), 0 0 0 12px rgba(106, 53, 225, 0);
                }
                100% {
                    box-shadow: 0 18px 40px rgba(70, 32, 165, 0.35), 0 0 0 0 rgba(106, 53, 225, 0);
                }
            }

            @keyframes pulse-ring-active {
                0%, 100% {
                    box-shadow: 0 18px 40px rgba(185, 28, 28, 0.35), 0 0 0 0 rgba(239, 68, 68, 0.6);
                }
                50% {
                    box-shadow: 0 18px 40px rgba(185, 28, 28, 0.35), 0 0 0 16px rgba(239, 68, 68, 0);
                }
            }

            border-radius: 13px;
            padding: 0.68rem 0.95rem;
            font-family: inherit;
            font-size: 0.9rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            cursor: pointer;
            color: #34286b;
            background: #f5f2ff;
            transition: transform 160ms ease, box-shadow 160ms ease, background 160ms ease;
        }

        .voice-secondary:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(72, 45, 145, 0.12);
            background: #f0ebff;
        }

        .voice-hint-chip {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            border-radius: 999px;
            padding: 0.42rem 0.7rem;
            border: 1px solid #d8ccff;
            background: #ece6ff;
            color: #534293;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.01em;
        }

        .search-wrap {
            margin-top: 0.4rem;
        }

        .search {
            width: 100%;
            border: 1px solid #d6def2;
            outline: none;
            border-radius: 999px;
            background: #fff;
            color: #2f3650;
            padding: 0.9rem 1.1rem;
            font-size: 1.02rem;
            font-family: inherit;
            transition: border-color 180ms ease, box-shadow 180ms ease;
        }

        .search:focus {
            border-color: #bca9f1;
            box-shadow: 0 0 0 4px rgba(91, 43, 201, 0.13);
        }

        .search::placeholder {
            color: #8a93ac;
        }

        .bubble-grid {
            margin: 1.35rem auto 0.2rem;
            width: min(100%, 640px);
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            grid-template-rows: repeat(3, minmax(126px, 1fr));
            gap: 1rem;
        }

        .bubble {
            border: 1px solid #edf0fa;
            border-radius: 28px;
            background: linear-gradient(180deg, #ffffff 0%, #f8faff 100%);
            box-shadow: 0 14px 30px rgba(33, 51, 99, 0.1);
            color: #2f3650;
            display: grid;
            place-items: center;
            text-align: center;
            padding: 0.85rem 0.6rem;
            cursor: pointer;
            transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease;
            animation: rise 520ms ease both;
        }

        .bubble:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 34px rgba(37, 55, 108, 0.16);
            border-color: #dde4f7;
        }

        .bubble:active {
            transform: translateY(0);
        }

        .bubble.is-selected {
            border-color: rgba(91, 43, 201, 0.4);
            box-shadow: 0 0 0 3px rgba(91, 43, 201, 0.16), 0 20px 34px rgba(37, 55, 108, 0.16);
        }

        .bubble.is-voice-match {
            border-color: rgba(22, 163, 74, 0.4);
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.13), 0 20px 34px rgba(37, 55, 108, 0.16);
        }

        .bubble-1 { grid-column: 2; grid-row: 1; animation-delay: 50ms; }
        .bubble-2 { grid-column: 1; grid-row: 2; animation-delay: 110ms; }
        .bubble-3 { grid-column: 3; grid-row: 2; animation-delay: 170ms; }
        .bubble-4 { grid-column: 1; grid-row: 3; animation-delay: 230ms; }
        .bubble-5 { grid-column: 2; grid-row: 2; animation-delay: 290ms; }
        .bubble-6 { grid-column: 3; grid-row: 3; animation-delay: 350ms; }

        .bubble-main {
            background: linear-gradient(160deg, #6a35e1 0%, #4620a5 100%);
            color: #fff;
            border-color: rgba(106, 53, 225, 0.52);
            transform: scale(1.07);
            box-shadow: 0 22px 34px rgba(70, 32, 165, 0.35);
        }

        .icon {
            width: 62px;
            height: 62px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: #ebe6ff;
            margin: 0 auto 0.65rem;
            font-size: 1.75rem;
            line-height: 1;
        }

        .bubble-main .icon {
            width: 66px;
            height: 66px;
            font-size: 1.95rem;
            background: rgba(255, 255, 255, 0.2);
        }

        .bubble-label {
            display: block;
            line-height: 1.15;
            font-size: 1.09rem;
            font-weight: 700;
        }

        .hint {
            margin: 1.1rem auto 0;
            width: min(100%, 640px);
            background: var(--panel-soft);
            border: 1px solid var(--line);
            color: #4d5673;
            border-radius: 14px;
            font-size: 0.9rem;
            font-weight: 600;
            padding: 0.65rem 0.8rem;
            text-align: center;
        }

        .mic-menu {
            position: fixed;
            right: 36px;
            bottom: 90px;
            z-index: 85;
            width: 172px;
            height: 270px;
            pointer-events: none;
            filter: drop-shadow(0 14px 24px rgba(48, 37, 94, 0.12));
        }

        .mic-menu button {
            border: 0;
            cursor: pointer;
            font: inherit;
        }

        .mic-menu-trigger,
        .mic-menu-item {
            position: absolute;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            box-shadow: 0 10px 22px rgba(43, 34, 92, 0.24);
        }

        .mic-menu-trigger {
            right: 0;
            top: 50%;
            width: 68px;
            height: 68px;
            transform: translateY(-50%);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.24);
            background: linear-gradient(160deg, #5f2fd4 0%, #3f2194 100%);
            pointer-events: auto;
            transition: transform 180ms ease, box-shadow 180ms ease;
        }

        .mic-menu-trigger:hover {
            transform: translateY(-50%) scale(1.04);
            box-shadow: 0 18px 30px rgba(63, 33, 148, 0.38);
        }

        .mic-menu-trigger svg {
            width: 28px;
            height: 28px;
        }

        .mic-menu-item {
            right: 12px;
            top: 50%;
            width: 58px;
            height: 58px;
            color: #513595;
            border: 1px solid #d7cdf9;
            background: linear-gradient(180deg, #ffffff 0%, #f7f4ff 100%);
            pointer-events: none;
            opacity: 0;
            transform: translate3d(0, -50%, 0) scale(0.7);
            transition: transform 220ms ease, opacity 220ms ease, box-shadow 220ms ease;
        }

        .mic-menu-item:hover {
            box-shadow: 0 14px 24px rgba(63, 33, 148, 0.22);
        }

        .mic-menu-item.is-primary {
            color: #fff;
            border-color: rgba(255, 255, 255, 0.24);
            background: linear-gradient(145deg, #6a35e1 0%, #4521a5 100%);
        }

        .mic-menu-item.is-listening {
            background: linear-gradient(145deg, #ef4444 0%, #b91c1c 100%);
            box-shadow: 0 18px 30px rgba(185, 28, 28, 0.35);
        }

        .mic-menu[data-open='true'] .mic-menu-item {
            opacity: 1;
            pointer-events: auto;
        }

        .mic-menu[data-open='true'] .mic-menu-item[data-index='1'] {
            transform: translate3d(-30px, -132px, 0) scale(1);
        }

        .mic-menu[data-open='true'] .mic-menu-item[data-index='2'] {
            transform: translate3d(-84px, -94px, 0) scale(1);
        }

        .mic-menu[data-open='true'] .mic-menu-item[data-index='3'] {
            transform: translate3d(-108px, -22px, 0) scale(1);
        }

        .mic-menu[data-open='true'] .mic-menu-item[data-index='4'] {
            transform: translate3d(-88px, 46px, 0) scale(1);
        }

        .mic-menu[data-open='true'] .mic-menu-item[data-index='5'] {
            transform: translate3d(-48px, 104px, 0) scale(1);
        }

        .mic-menu[data-open='true'] .mic-menu-trigger {
            transform: translateY(-50%) scale(0.96);
        }

        .mic-menu-icon {
            width: 28px;
            height: 28px;
            display: block;
            stroke: currentColor;
            stroke-width: 1.9;
        }

        .mic-menu-label {
            position: absolute;
            right: 58px;
            top: 50%;
            transform: translateY(-50%);
            white-space: nowrap;
            padding: 0.42rem 0.62rem;
            border-radius: 999px;
            border: 1px solid #d8cdfa;
            background: rgba(255, 255, 255, 0.98);
            color: #3f2e78;
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            opacity: 0;
            pointer-events: none;
            transition: opacity 160ms ease;
        }

        .mic-menu-item:hover .mic-menu-label,
        .mic-menu-item:focus-visible .mic-menu-label {
            opacity: 1;
        }

        .mic-menu-backdrop {
            position: fixed;
            inset: 0;
            z-index: 84;
            background: transparent;
            opacity: 0;
            pointer-events: none;
        }

        .mic-menu-backdrop.is-active {
            opacity: 1;
            pointer-events: auto;
        }

        @keyframes rise {
            from {
                opacity: 0;
                transform: translateY(16px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (min-width: 900px) {
            .page {
                padding: 2.3rem 1.2rem 4rem;
            }

            .menu-shell {
                padding: 1.45rem;
            }

            .mic-menu {
                right: 46px;
                bottom: 96px;
            }
        }

        @media (max-width: 720px) {
            .page {
                padding: 1.1rem 0.8rem 2.8rem;
            }

            .menu-shell {
                border-radius: 26px;
                padding: 0.95rem;
            }

            .bubble-grid {
                width: min(100%, 520px);
                grid-template-rows: repeat(3, minmax(110px, 1fr));
                gap: 0.8rem;
            }

            .bubble-label {
                font-size: 0.98rem;
            }

            .hint {
                width: min(100%, 520px);
                font-size: 0.83rem;
            }

            .mic-menu {
                right: 22px;
                bottom: 78px;
                width: 136px;
                height: 218px;
            }

            .mic-menu-trigger {
                width: 62px;
                height: 62px;
            }

            .mic-menu-item {
                width: 48px;
                height: 48px;
            }

            .mic-menu[data-open='true'] .mic-menu-item[data-index='1'] {
                transform: translate3d(-22px, -102px, 0) scale(1);
            }

            .mic-menu[data-open='true'] .mic-menu-item[data-index='2'] {
                transform: translate3d(-58px, -80px, 0) scale(1);
            }

            .mic-menu[data-open='true'] .mic-menu-item[data-index='3'] {
                transform: translate3d(-80px, -20px, 0) scale(1);
            }

            .mic-menu[data-open='true'] .mic-menu-item[data-index='4'] {
                transform: translate3d(-66px, 32px, 0) scale(1);
            }

            .mic-menu[data-open='true'] .mic-menu-item[data-index='5'] {
                transform: translate3d(-38px, 82px, 0) scale(1);
            }
        }
    </style>
</head>

<body>
    <div id="micMenuBackdrop" class="mic-menu-backdrop" hidden></div>

    @include('partials.header')

    <main class="page">
        <section class="menu-shell">
            <section class="voice-strip" aria-label="Pedido por voz">
                <div class="voice-top">
                    <div class="voice-status" id="voiceStatus">Listo para escuchar</div>
                    <span class="voice-hint-chip">Usá el menú de la derecha</span>
                </div>

                <div id="voiceTranscript" class="voice-transcript">
                    Decí algo como: <strong>quiero una coca, pan y farmacia</strong>
                </div>

                <div style="display: flex; justify-content: center; margin: 1rem 0;">
                    <button id="voiceStartButton" class="voice-primary-action" type="button">🎤</button>
                </div>
                </div>

                <div class="voice-actions">
                    <button id="voiceApply" class="voice-secondary" type="button">Usar este pedido</button>
                    <button id="voiceReset" class="voice-secondary" type="button">Limpiar</button>
                </div>
            </section>

            <div class="search-wrap">
                <input id="quickSearch" class="search" type="search" placeholder="¿Qué necesitás hoy?" aria-label="Buscar en Bari Tienda">
            </div>

            <div class="bubble-grid">
                <button class="bubble bubble-1" type="button" data-query="comida">
                    Comida
                </button>

                <button class="bubble bubble-2" type="button" data-query="regalos">
                    Regalos y más
                </button>

                <button class="bubble bubble-3" type="button" data-query="super hogar">
                    Super y hogar
                </button>

                <button class="bubble bubble-4" type="button" data-query="farmacia">
                    Farmacia
                </button>

                <button class="bubble bubble-5 bubble-main" type="button" data-query="lo que sea">
                    Lo que sea
                </button>

                <button class="bubble bubble-6" type="button" data-query="retira envia">
                    Retirá y envía
                </button>
            </div>

            <p class="hint">Tocá una categoría para autocompletar la búsqueda o escribí y presioná Enter.</p>
        </section>
    </main>

    <aside id="micMenu" class="mic-menu" data-open="false" aria-label="Menú radial de accesos rápidos">
        <button id="micMenuTrigger" class="mic-menu-trigger" type="button" aria-label="Abrir accesos rápidos" aria-expanded="false" aria-controls="micMenuActions">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                <path d="M5 7h14"></path>
                <path d="M5 12h14"></path>
                <path d="M5 17h14"></path>
            </svg>
        </button>

        <div id="micMenuActions">
            <button id="micMenuVoiceAction" class="mic-menu-item is-primary" type="button" data-index="1" data-action="voice" aria-label="Pedir con voz">
                <svg class="mic-menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 14c1.7 0 3-1.3 3-3V6a3 3 0 1 0-6 0v5c0 1.7 1.3 3 3 3Z"></path>
                    <path d="M7 11a5 5 0 0 0 10 0"></path>
                    <path d="M12 16v4"></path>
                    <path d="M9 20h6"></path>
                </svg>
                <span class="mic-menu-label">Pedir con voz</span>
            </button>

            <button class="mic-menu-item" type="button" data-index="2" data-menu-query="comida" aria-label="Abrir comida">
                <svg class="mic-menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 8h16"></path>
                    <path d="M6 8c0 5 2.7 9 6 9s6-4 6-9"></path>
                    <path d="M12 17v3"></path>
                    <path d="M9 20h6"></path>
                </svg>
                <span class="mic-menu-label">Comida</span>
            </button>

            <button class="mic-menu-item" type="button" data-index="3" data-menu-query="super hogar" aria-label="Abrir super y hogar">
                <svg class="mic-menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 7h16l-1.5 9H6L4 7Z"></path>
                    <path d="M9 11h6"></path>
                    <path d="M9 15h6"></path>
                </svg>
                <span class="mic-menu-label">Super</span>
            </button>

            <button class="mic-menu-item" type="button" data-index="4" data-menu-query="farmacia" aria-label="Abrir farmacia">
                <svg class="mic-menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 5v14"></path>
                    <path d="M5 12h14"></path>
                    <path d="M12 3v18"></path>
                </svg>
                <span class="mic-menu-label">Farmacia</span>
            </button>

            <button class="mic-menu-item" type="button" data-index="5" data-menu-query="regalos" aria-label="Abrir regalos">
                <svg class="mic-menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9h18"></path>
                    <path d="M5 9v10h14V9"></path>
                    <path d="M12 9v10"></path>
                    <path d="M7 6a2 2 0 1 1 4 0v3H7z"></path>
                    <path d="M13 6a2 2 0 1 1 4 0v3h-4z"></path>
                </svg>
                <span class="mic-menu-label">Regalos</span>
            </button>
        </div>
    </aside>

    <script>
        (() => {
            const searchInput = document.getElementById('quickSearch');
            const buttons = document.querySelectorAll('.bubble-grid [data-query]');
            const voiceApply = document.getElementById('voiceApply');
            const voiceReset = document.getElementById('voiceReset');
            const voiceStartButton = document.getElementById('voiceStartButton');
            const voiceStatus = document.getElementById('voiceStatus');
            const voiceTranscript = document.getElementById('voiceTranscript');
            const micMenu = document.getElementById('micMenu');
            const micMenuTrigger = document.getElementById('micMenuTrigger');
            const micMenuBackdrop = document.getElementById('micMenuBackdrop');
            const micMenuVoiceAction = document.getElementById('micMenuVoiceAction');
            const micMenuItems = document.querySelectorAll('.mic-menu-item');

            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            let recognition = null;
            let isListening = false;
            let isMenuOpen = false;

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
                if (micMenuVoiceAction) {
                    micMenuVoiceAction.classList.remove('is-listening');
                    micMenuVoiceAction.setAttribute('aria-label', 'Pedir con voz');
                    const label = micMenuVoiceAction.querySelector('.mic-menu-label');
                    if (label) label.textContent = 'Pedir con voz';
                }
                if (voiceStartButton) {
                    voiceStartButton.classList.remove('is-listening');
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

            const setMenuOpen = (open) => {
                isMenuOpen = open;
                if (micMenu) {
                    micMenu.dataset.open = open ? 'true' : 'false';
                }
                if (micMenuTrigger) {
                    micMenuTrigger.setAttribute('aria-expanded', open ? 'true' : 'false');
                }
                if (micMenuBackdrop) {
                    micMenuBackdrop.hidden = !open;
                    micMenuBackdrop.classList.toggle('is-active', open);
                }
            };

            const goToCatalog = (query) => {
                const categoryMap = {
                    'comida': '/categoria/comida',
                    'regalos': '/categoria/regalos',
                    'super hogar': '/categoria/super-hogar',
                    'super': '/categoria/super-hogar',
                    'hogar': '/categoria/super-hogar',
                    'farmacia': '/categoria/farmacia',
                    'lo que sea': '/categoria/lo-que-sea',
                    'retira envia': '/categoria/retira-envia'
                };
                const categorySlug = categoryMap[query] || '/';
                window.location.href = categorySlug;
            };

            const applyQuery = (query, shouldNavigate = true) => {
                if (!searchInput) return;
                searchInput.value = query;
                markMatch(query);
                buttons.forEach((item) => {
                    item.classList.toggle('is-selected', item.getAttribute('data-query') === query);
                });
                if (shouldNavigate) {
                    goToCatalog(query);
                }
            };

            if (SpeechRecognition) {
                recognition = new SpeechRecognition();
                recognition.lang = 'es-AR';
                recognition.interimResults = true;
                recognition.continuous = false;

                recognition.onstart = () => {
                    isListening = true;
                    if (micMenuVoiceAction) {
                        micMenuVoiceAction.classList.add('is-listening');
                        micMenuVoiceAction.setAttribute('aria-label', 'Detener grabación');
                        const label = micMenuVoiceAction.querySelector('.mic-menu-label');
                        if (label) label.textContent = 'Escuchando';
                    }
                    if (voiceStartButton) {
                        voiceStartButton.classList.add('is-listening');
                    }
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

                if (micMenuVoiceAction) {
                    micMenuVoiceAction.addEventListener('click', () => {
                        setMenuOpen(false);
                        if (isListening) {
                            stopListening();
                            return;
                        }
                        startListening();
                    });
                }

                if (voiceStartButton) {
                    voiceStartButton.addEventListener('click', () => {
                        if (isListening) {
                            stopListening();
                            return;
                        }
                        startListening();
                    });
                }
            } else if (micMenuVoiceAction) {
                micMenuVoiceAction.disabled = true;
                const label = micMenuVoiceAction.querySelector('.mic-menu-label');
                if (label) label.textContent = 'Sin voz';
                if (voiceStatus) voiceStatus.textContent = 'Tu navegador no soporta reconocimiento de voz';
            }

            if (!searchInput || !buttons.length) return;

            buttons.forEach((button) => {
                button.addEventListener('click', () => {
                    const query = button.getAttribute('data-query') || '';
                    applyQuery(query);
                });
            });

            micMenuItems.forEach((item) => {
                item.addEventListener('click', () => {
                    const query = item.getAttribute('data-menu-query');
                    if (!query) return;
                    setMenuOpen(false);
                    applyQuery(query);
                });
            });

            if (micMenuTrigger) {
                micMenuTrigger.addEventListener('click', () => {
                    setMenuOpen(!isMenuOpen);
                });
            }

            if (micMenuBackdrop) {
                micMenuBackdrop.addEventListener('click', () => {
                    setMenuOpen(false);
                });
            }

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    setMenuOpen(false);
                }
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
                        voiceTranscript.innerHTML = 'Decí algo como: <strong>quiero una coca, pan y farmacia</strong>';
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