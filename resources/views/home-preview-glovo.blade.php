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
            --sun: #f4d35e;
            --sun-soft: #fde9a3;
            --ink: #1f2937;
            --paper: #fffdf8;
            --panel: #fff8e7;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Outfit', sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at 10% 0%, #f3ebff 0%, #f3ebff 20%, transparent 21%),
                radial-gradient(circle at 96% 10%, #fff6d7 0%, #fff6d7 18%, transparent 19%),
                linear-gradient(160deg, #fcf8ff 0%, #f8e9ac 42%, #f2cd67 100%);
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
            border: 1px solid #f1dca1;
            border-radius: 28px;
            padding: 1rem 0.85rem 1.1rem;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.55);
        }

        .location {
            font-size: 0.9rem;
            color: #5d4b29;
            font-weight: 700;
        }

        .search-wrap {
            margin-top: 0.45rem;
        }

        .search {
            width: 100%;
            border: 0;
            outline: none;
            border-radius: 999px;
            background: #fff;
            box-shadow: inset 0 0 0 1px #ddcafc;
            color: #4f2a8f;
            padding: 0.76rem 0.96rem;
            font-size: 0.94rem;
            font-family: inherit;
        }

        .search::placeholder {
            color: #8465ba;
        }

        .bubble-grid {
            margin: 1rem auto 0.2rem;
            width: min(100%, 430px);
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            grid-template-rows: repeat(3, minmax(84px, 1fr));
            gap: 0.72rem;
        }

        .bubble {
            border: 0;
            border-radius: 999px;
            background: #fff;
            box-shadow: 0 8px 22px rgba(67, 45, 9, 0.14);
            color: #4a3612;
            display: grid;
            place-items: center;
            text-align: center;
            padding: 0.45rem;
            cursor: pointer;
            transition: transform 180ms ease, box-shadow 180ms ease;
            animation: rise 600ms ease both;
        }

        .bubble:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(73, 34, 145, 0.2);
        }

        .bubble-1 { grid-column: 2; grid-row: 1; animation-delay: 60ms; }
        .bubble-2 { grid-column: 1; grid-row: 2; animation-delay: 120ms; }
        .bubble-3 { grid-column: 3; grid-row: 2; animation-delay: 180ms; }
        .bubble-4 { grid-column: 1; grid-row: 3; animation-delay: 240ms; }
        .bubble-5 { grid-column: 2; grid-row: 2; animation-delay: 300ms; }
        .bubble-6 { grid-column: 3; grid-row: 3; animation-delay: 360ms; }

        .bubble-main {
            background: linear-gradient(145deg, var(--brand) 0%, var(--brand-strong) 100%);
            color: #fff;
            transform: scale(1.13);
            box-shadow: 0 18px 30px rgba(70, 30, 152, 0.35);
        }

        .icon {
            width: 44px;
            height: 44px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: var(--brand-soft);
            margin: 0 auto 0.38rem;
            font-size: 1.15rem;
        }

        .bubble-main .icon {
            background: #f4edff;
        }

        .bubble-label {
            display: block;
            line-height: 1.05;
            font-size: 0.79rem;
            font-weight: 700;
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
                width: min(100%, 510px);
                grid-template-rows: repeat(3, minmax(96px, 1fr));
                gap: 0.8rem;
            }

            .bubble-label {
                font-size: 0.84rem;
            }
        }
    </style>
</head>

<body>
    <main class="page">
        <section class="menu-shell">
            <div class="search-wrap">
                <input class="search" type="search" placeholder="Que necesitas?" aria-label="Buscar en Bari Tienda">
            </div>

            <div class="bubble-grid">
                <button class="bubble bubble-1" type="button">
                    <div>
                        <span class="icon">🍽️</span>
                        <span class="bubble-label">Comida</span>
                    </div>
                </button>

                <button class="bubble bubble-2" type="button">
                    <div>
                        <span class="icon">🧁</span>
                        <span class="bubble-label">Regalos y mas</span>
                    </div>
                </button>

                <button class="bubble bubble-3" type="button">
                    <div>
                        <span class="icon">🛒</span>
                        <span class="bubble-label">Super y hogar</span>
                    </div>
                </button>

                <button class="bubble bubble-4" type="button">
                    <div>
                        <span class="icon">💊</span>
                        <span class="bubble-label">Farmacia</span>
                    </div>
                </button>

                <button class="bubble bubble-5 bubble-main" type="button">
                    <div>
                        <span class="icon">⭐</span>
                        <span class="bubble-label">Lo que sea</span>
                    </div>
                </button>

                <button class="bubble bubble-6" type="button">
                    <div>
                        <span class="icon">📦</span>
                        <span class="bubble-label">Retira y envia</span>
                    </div>
                </button>
            </div>
        </section>
    </main>
</body>

</html>
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
            --sun: #f4d35e;
            --sun-soft: #f9e28f;
            --ink: #1e1e1e;
            --paper: #fffdf7;
            --mint: #e9f3dd;
            --leaf: #2f7d32;
            --brand: #6d28d9;
            --brand-soft: #ede9fe;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Outfit', sans-serif;
            background:
                radial-gradient(circle at 15% 0%, #f5edff 0, #f5edff 14%, transparent 15%),
                radial-gradient(circle at 88% 16%, #fff8dd 0, #fff8dd 16%, transparent 17%),
                linear-gradient(165deg, #f8f3ff 0%, #f7e48f 42%, #f3d062 100%);
            color: var(--ink);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .page {
            max-width: 1150px;
            margin: 0 auto;
            padding: 1.25rem;
        }

        .hero {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            align-items: center;
            position: relative;
        }

        .badge {
            display: inline-flex;
            gap: 0.45rem;
            align-items: center;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.86);
            color: #403519;
            padding: 0.45rem 0.9rem;
            width: fit-content;
            font-size: 0.83rem;
            font-weight: 600;
            backdrop-filter: blur(3px);
        }

        .title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: clamp(1.9rem, 4.3vw, 4rem);
            line-height: 0.95;
            letter-spacing: -0.04em;
            margin: 0.8rem 0 0.7rem;
            max-width: 16ch;
        }

        .subtitle {
            max-width: 46ch;
            font-size: clamp(1rem, 1.7vw, 1.2rem);
            color: #4c3f1f;
            margin: 0;
        }

        .layout {
            margin-top: 1rem;
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .phone-stage {
            position: relative;
            min-height: 500px;
            display: grid;
            place-items: center;
            isolation: isolate;
        }

        .blob {
            position: absolute;
            inset: 10% 3% auto auto;
            width: min(94%, 560px);
            height: 560px;
            background: rgba(255, 255, 255, 0.46);
            border-radius: 48% 52% 63% 37% / 42% 34% 66% 58%;
            z-index: -1;
            filter: blur(0.4px);
            animation: drift 8s ease-in-out infinite;
        }

        .phone {
            width: min(95%, 360px);
            border-radius: 42px;
            background: #151515;
            padding: 10px;
            box-shadow:
                0 34px 65px rgba(0, 0, 0, 0.34),
                0 8px 26px rgba(0, 0, 0, 0.22);
            transform: rotate(-8deg);
            transform-origin: center;
            animation: settle 850ms cubic-bezier(0.17, 0.88, 0.32, 1.2) both;
        }

        .screen {
            border-radius: 34px;
            background: linear-gradient(180deg, #f8de81 0%, #f3c95a 100%);
            min-height: 650px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            border: 2px solid rgba(255, 255, 255, 0.26);
        }

        .topbar {
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            font-size: 0.77rem;
            color: #fef8d6;
            opacity: 0.95;
        }

        .topbar::before {
            content: '';
            position: absolute;
            top: 0;
            width: 120px;
            height: 16px;
            background: #0a0a0a;
            border-radius: 0 0 12px 12px;
        }

        .app-head {
            padding: 1.2rem 1rem 0.4rem;
            color: #2d174f;
        }

        .location {
            font-size: 0.93rem;
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        .search {
            margin-top: 0.45rem;
            width: 100%;
            border: 0;
            outline: none;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 999px;
            padding: 0.68rem 0.95rem;
            color: #5b3d8a;
            font-size: 0.9rem;
            box-shadow: inset 0 0 0 1px #ddccfb;
        }

        .search::placeholder {
            color: #8f74bc;
        }

        .bubble-grid {
            position: relative;
            padding: 1.8rem 1.15rem 1.5rem;
            flex: 1;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.8rem 0.6rem;
            align-content: center;
        }

        .bubble {
            border: 0;
            cursor: pointer;
            aspect-ratio: 1/1;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.97);
            border: 1px solid rgba(255, 255, 255, 0.8);
            display: grid;
            place-items: center;
            text-align: center;
            padding: 0.55rem;
            color: #47350c;
            box-shadow: 0 8px 22px rgba(72, 56, 20, 0.14);
            transform: translateY(18px);
            opacity: 0;
            animation: rise 650ms ease forwards;
            transition: transform 180ms ease, box-shadow 180ms ease;
        }

        .bubble:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(49, 22, 93, 0.18);
        }

        .bubble:nth-child(1) { animation-delay: 120ms; }
        .bubble:nth-child(2) { animation-delay: 180ms; }
        .bubble:nth-child(3) { animation-delay: 240ms; }
        .bubble:nth-child(4) { animation-delay: 300ms; }
        .bubble:nth-child(5) { animation-delay: 360ms; }
        .bubble:nth-child(6) { animation-delay: 420ms; }

        .bubble span {
            display: block;
            font-size: 0.8rem;
            line-height: 1.15;
            font-weight: 600;
        }

        .bubble-main {
            grid-column: 2;
            grid-row: 2;
            background: linear-gradient(145deg, var(--brand) 0%, #7c3aed 100%);
            color: white;
            transform: scale(1.12) translateY(18px);
            box-shadow: 0 14px 28px rgba(71, 24, 143, 0.36);
        }

        .bubble-main .mini {
            background: #f6f0ff;
        }

        .bubble-main span {
            color: #fefefe;
            font-weight: 700;
        }

        .mini {
            width: 44px;
            height: 44px;
            border-radius: 999px;
            margin: 0 auto 0.45rem;
            background: var(--brand-soft);
            display: grid;
            place-items: center;
            font-size: 1.15rem;
        }

        .bar {
            margin: 0.6rem auto 1rem;
            width: 42%;
            border-radius: 8px;
            height: 6px;
            background: rgba(255, 255, 255, 0.85);
        }

        .info-card {
            align-self: end;
            background: rgba(255, 255, 255, 0.92);
            border-radius: 22px;
            padding: 1.1rem;
            box-shadow: 0 16px 30px rgba(53, 41, 16, 0.16);
        }

        .info-card h2 {
            margin: 0;
            font-family: 'Space Grotesk', sans-serif;
            font-size: clamp(1.3rem, 2.1vw, 2rem);
            letter-spacing: -0.03em;
        }

        .pill-row {
            margin-top: 0.8rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .pill {
            border-radius: 999px;
            background: #faf2da;
            border: 1px solid #f0d99f;
            padding: 0.35rem 0.72rem;
            font-size: 0.84rem;
            color: #6f5116;
            font-weight: 600;
        }

        .actions {
            margin-top: 1rem;
            display: flex;
            gap: 0.7rem;
            flex-wrap: wrap;
        }

        .btn {
            border: 0;
            border-radius: 14px;
            padding: 0.72rem 0.96rem;
            font-size: 0.92rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform 180ms ease, filter 180ms ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            filter: brightness(0.97);
        }

        .btn-primary {
            background: var(--brand);
            color: white;
        }

        .btn-secondary {
            background: #f4ebff;
            color: #532495;
            border: 1px solid #d9c3ff;
        }

        .note {
            margin-top: 0.8rem;
            font-size: 0.86rem;
            color: #5b4b24;
        }

        @keyframes drift {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(-6px, 8px) scale(1.03); }
        }

        @keyframes settle {
            from {
                opacity: 0;
                transform: translateY(20px) rotate(-13deg) scale(0.97);
            }
            to {
                opacity: 1;
                transform: translateY(0) rotate(-8deg) scale(1);
            }
        }

        @keyframes rise {
            from {
                opacity: 0;
                transform: translateY(18px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (min-width: 980px) {
            .layout {
                margin-top: 1.6rem;
                grid-template-columns: 1.1fr 0.9fr;
                gap: 1.2rem;
            }

            .hero {
                grid-template-columns: 1fr;
            }

            .phone-stage {
                min-height: 740px;
            }

            .info-card {
                margin-top: 2.4rem;
            }
        }

        @media (max-width: 430px) {
            .page {
                padding: 1rem 0.75rem;
            }

            .phone {
                width: min(100%, 342px);
            }

            .screen {
                min-height: 620px;
            }

            .bubble span {
                font-size: 0.76rem;
            }
        }
    </style>
</head>

<body>
    <main class="page">
        <section class="hero">
            <span class="badge">Preview visual | Bari Tienda</span>
            <h1 class="title">Una home mobile-first con energia delivery.</h1>
            <p class="subtitle">Prototipo paralelo inspirado en apps de reparto clasicas: foco en accion rapida, categorias circulares y una mezcla entre tu identidad visual y una experiencia tipo app.</p>
        </section>

        <section class="layout">
            <div class="phone-stage">
                <div class="blob" aria-hidden="true"></div>

                <article class="phone" aria-label="Vista simulada de la app">
                    <div class="screen">
                        <div class="topbar">16:25</div>

                        <div class="app-head">
                            <div class="location">Ubicacion actual</div>
                            <input class="search" type="search" placeholder="Que necesitas?" aria-label="Buscar en Bari Tienda">
                        </div>

                        <div class="bubble-grid">
                            <button class="bubble" type="button">
                                <div>
                                    <div class="mini">🍽️</div>
                                    <span>Comida</span>
                                </div>
                            </button>
                            <button class="bubble" type="button">
                                <div>
                                    <div class="mini">🧁</div>
                                    <span>Regalos y mas</span>
                                </div>
                            </button>
                            <button class="bubble" type="button">
                                <div>
                                    <div class="mini">🛒</div>
                                    <span>Super y hogar</span>
                                </div>
                            </button>
                            <button class="bubble" type="button">
                                <div>
                                    <div class="mini">💊</div>
                                    <span>Farmacia</span>
                                </div>
                            </button>
                            <button class="bubble bubble-main" type="button">
                                <div>
                                    <div class="mini">⭐</div>
                                    <span>Lo que sea</span>
                                </div>
                            </button>
                            <button class="bubble" type="button">
                                <div>
                                    <div class="mini">📦</div>
                                    <span>Retira y envia</span>
                                </div>
                            </button>
                        </div>

                        <div class="bar" aria-hidden="true"></div>
                    </div>
                </article>
            </div>

            <aside class="info-card">
                <h2>Prueba lista para iterar</h2>
                <p class="note">Esta pagina vive en una ruta separada para que compares estilos y luego decidas que elementos pasar a la home real.</p>

                <div class="pill-row">
                    <span class="pill">Mobile first</span>
                    <span class="pill">Categorias destacadas</span>
                    <span class="pill">Visual calido</span>
                    <span class="pill">Animacion de entrada</span>
                </div>

                <div class="actions">
                    <button class="btn btn-primary" type="button" onclick="window.location.href='{{ route('home') }}'">Volver a la home actual</button>
                    <button class="btn btn-secondary" type="button" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">Subir</button>
                </div>
            </aside>
        </section>
    </main>
</body>

</html>
