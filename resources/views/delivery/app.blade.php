<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b1220">
    <title>Repartidor | Alertas de pedidos</title>
    <link rel="manifest" href="{{ asset('delivery-manifest.json') }}">
    <link rel="icon" href="{{ asset('images/og-image.png') }}">
    <style>
        :root {
            --bg: #0b1220;
            --card: #151f34;
            --text: #e7eefc;
            --muted: #a9b8d5;
            --ok: #20c997;
            --warn: #ffb703;
            --accent: #4dabf7;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text);
            min-height: 100vh;
            background:
                radial-gradient(1000px 600px at 120% -10%, rgba(77, 171, 247, 0.18), transparent 60%),
                radial-gradient(700px 500px at -10% 110%, rgba(32, 201, 151, 0.14), transparent 60%),
                var(--bg);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px;
        }

        .panel {
            width: 100%;
            max-width: 460px;
            background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.01));
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 20px;
            padding: 20px;
            backdrop-filter: blur(4px);
            box-shadow: 0 16px 40px rgba(0,0,0,0.35);
        }

        h1 {
            margin: 0 0 8px;
            font-size: 1.35rem;
        }

        p {
            margin: 0;
            color: var(--muted);
            line-height: 1.45;
        }

        .status {
            margin-top: 16px;
            padding: 14px;
            border-radius: 14px;
            background: var(--card);
            border: 1px solid rgba(255,255,255,0.08);
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
            background: var(--warn);
        }

        .ok .dot {
            background: var(--ok);
        }

        .row {
            margin-top: 14px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        button, a.btn {
            appearance: none;
            border: 0;
            border-radius: 12px;
            padding: 11px 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: transform .12s ease, opacity .2s ease;
        }

        button:active, a.btn:active {
            transform: translateY(1px);
        }

        .primary {
            background: var(--accent);
            color: #061224;
        }

        .secondary {
            background: #22304d;
            color: var(--text);
            border: 1px solid rgba(255,255,255,0.12);
        }

        .tiny {
            margin-top: 14px;
            font-size: .9rem;
            color: var(--muted);
        }

        .install-help {
            margin-top: 10px;
            font-size: .88rem;
            color: #b9c9e8;
            line-height: 1.45;
            border-top: 1px dashed rgba(255,255,255,0.14);
            padding-top: 10px;
        }

        .order-meta {
            margin-top: 8px;
            font-size: .94rem;
            color: #cde1ff;
        }

        @media (max-width: 420px) {
            .panel { padding: 16px; }
            h1 { font-size: 1.2rem; }
        }
    </style>
</head>
<body>
    <main class="panel">
        <h1>App Repartidor (MVP)</h1>
        <p>Esta version avisa cuando entra un pedido nuevo. Mantenla abierta para recibir alertas inmediatas.</p>

        <section id="statusBox" class="status">
            <div><span class="dot"></span><strong id="statusTitle">Inicializando...</strong></div>
            <div class="order-meta" id="orderMeta">Conectando con el servidor...</div>
        </section>

        <div class="row">
            <button id="enableNotifications" class="primary">Activar notificaciones</button>
            <button id="installApp" class="secondary">Instalar en el celular</button>
            <a href="{{ route('logout') }}" class="btn secondary"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Salir</a>
        </div>

        <div class="tiny" id="tinyInfo">Sondeo activo cada 10 segundos.</div>
        <div class="install-help" id="installHelp">Verificando si este navegador permite instalar la app...</div>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </main>

    <script>
        const latestUrl = @json(route('delivery.orders.latest'));
        const appUrl = @json(route('delivery.app'));
        const statusBox = document.getElementById('statusBox');
        const statusTitle = document.getElementById('statusTitle');
        const orderMeta = document.getElementById('orderMeta');
        const tinyInfo = document.getElementById('tinyInfo');
        const installHelp = document.getElementById('installHelp');
        const enableButton = document.getElementById('enableNotifications');
        const installButton = document.getElementById('installApp');

        let deferredPrompt = null;
        let baselineInitialized = false;

        function isStandalone() {
            return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
        }

        function isIOS() {
            return /iphone|ipad|ipod/i.test(navigator.userAgent);
        }

        function updateInstallUI() {
            if (isStandalone()) {
                installButton.disabled = true;
                installButton.textContent = 'App ya instalada';
                installHelp.textContent = 'La app ya esta instalada en este dispositivo.';
                return;
            }

            if (deferredPrompt) {
                installButton.disabled = false;
                installHelp.textContent = 'Instalacion disponible. Toca "Instalar en el celular".';
                return;
            }

            if (isIOS()) {
                installButton.disabled = false;
                installHelp.textContent = 'iPhone/iPad: toca Compartir y luego "Agregar a pantalla de inicio".';
                return;
            }

            installButton.disabled = false;
            installHelp.textContent = 'Si no aparece el cuadro de instalacion, abre el menu del navegador y usa "Instalar app" o "Agregar a pantalla de inicio".';
        }

        function markOk(title, meta) {
            statusBox.classList.add('ok');
            statusTitle.textContent = title;
            orderMeta.textContent = meta;
        }

        function markWarn(title, meta) {
            statusBox.classList.remove('ok');
            statusTitle.textContent = title;
            orderMeta.textContent = meta;
        }

        function getSavedOrderId() {
            const value = localStorage.getItem('delivery_last_order_id');
            return value ? parseInt(value, 10) : null;
        }

        function saveOrderId(orderId) {
            localStorage.setItem('delivery_last_order_id', String(orderId));
        }

        function playBeep() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = ctx.createOscillator();
                const gainNode = ctx.createGain();

                oscillator.type = 'sine';
                oscillator.frequency.value = 880;
                gainNode.gain.value = 0.12;

                oscillator.connect(gainNode);
                gainNode.connect(ctx.destination);

                oscillator.start();
                setTimeout(() => oscillator.stop(), 220);
            } catch (error) {
                console.warn('No se pudo reproducir beep', error);
            }
        }

        async function showOrderNotification(order) {
            const body = `Pedido #${order.id} - ${order.customer_name} - $${Number(order.total).toFixed(2)}`;

            if (Notification.permission === 'granted') {
                new Notification('Nuevo pedido recibido', {
                    body,
                    icon: '{{ asset('images/og-image.png') }}',
                    badge: '{{ asset('images/og-image.png') }}',
                    tag: `order-${order.id}`,
                });
            }

            if (navigator.vibrate) {
                navigator.vibrate([150, 60, 150]);
            }

            playBeep();
        }

        async function checkLatestOrder() {
            try {
                const response = await fetch(latestUrl, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    cache: 'no-store',
                });

                if (!response.ok) {
                    markWarn('Error de conexion', `HTTP ${response.status}`);
                    return;
                }

                const data = await response.json();

                if (!data.has_order) {
                    markOk('Sin pedidos pendientes', 'No hay nuevos pedidos por ahora.');
                    return;
                }

                const savedId = getSavedOrderId();

                if (!baselineInitialized) {
                    baselineInitialized = true;
                    if (!savedId) {
                        saveOrderId(data.id);
                        markOk('Escuchando pedidos', `Ultimo pedido actual #${data.id}.`);
                        return;
                    }
                }

                if (!savedId || data.id > savedId) {
                    saveOrderId(data.id);
                    await showOrderNotification(data);
                    markOk('Nuevo pedido detectado', `Pedido #${data.id} de ${data.customer_name}.`);
                    return;
                }

                markOk('Escuchando pedidos', `Ultimo pedido detectado #${data.id}.`);
            } catch (error) {
                markWarn('Sin conexion', 'Reintentando automaticamente...');
                console.error(error);
            }
        }

        async function requestNotificationPermission() {
            if (!('Notification' in window)) {
                tinyInfo.textContent = 'Este navegador no soporta notificaciones web.';
                return;
            }

            const permission = await Notification.requestPermission();
            if (permission === 'granted') {
                tinyInfo.textContent = 'Notificaciones activadas correctamente.';
                enableButton.disabled = true;
            } else {
                tinyInfo.textContent = 'Notificaciones bloqueadas. Habilitalas en el navegador.';
            }
        }

        async function registerServiceWorker() {
            if (!('serviceWorker' in navigator)) {
                return;
            }

            try {
                await navigator.serviceWorker.register('{{ asset('delivery-sw.js') }}');
            } catch (error) {
                console.warn('No se pudo registrar service worker', error);
            }
        }

        window.addEventListener('beforeinstallprompt', (event) => {
            event.preventDefault();
            deferredPrompt = event;
            updateInstallUI();
        });

        installButton.addEventListener('click', async () => {
            if (isStandalone()) {
                installHelp.textContent = 'La app ya esta instalada en este dispositivo.';
                return;
            }

            if (!deferredPrompt) {
                if (isIOS()) {
                    installHelp.textContent = 'iPhone/iPad: Safari > Compartir > Agregar a pantalla de inicio.';
                } else {
                    installHelp.textContent = 'Android/Chrome: Menu (tres puntos) > Instalar app o Agregar a pantalla de inicio.';
                }
                return;
            }

            deferredPrompt.prompt();
            const result = await deferredPrompt.userChoice;

            if (result.outcome === 'accepted') {
                installHelp.textContent = 'Instalacion aceptada. Busca el icono en tu pantalla de inicio.';
            } else {
                installHelp.textContent = 'Instalacion cancelada. Puedes intentarlo de nuevo cuando quieras.';
            }

            deferredPrompt = null;
            updateInstallUI();
        });

        enableButton.addEventListener('click', requestNotificationPermission);

        window.addEventListener('load', async () => {
            await registerServiceWorker();
            updateInstallUI();

            if ('Notification' in window && Notification.permission === 'granted') {
                enableButton.disabled = true;
                tinyInfo.textContent = 'Notificaciones activadas correctamente.';
            }

            await checkLatestOrder();
            setInterval(checkLatestOrder, 10000);
        });
    </script>
</body>
</html>
