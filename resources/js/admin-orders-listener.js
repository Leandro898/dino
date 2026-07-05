// 🔌 WebSocket Listener para Admin Orders
// Escucha nuevas órdenes y actualizaciones de estado vía Reverb

let adminAudioInitialized = false;

// Inicializar AudioContext con el primer click del usuario
function adminInitAudio() {
    if (adminAudioInitialized) return;
    const silent = new Audio(import.meta.env.VITE_APP_URL + '/sounds/admin.mp3');
    silent.volume = 0;
    silent.play().then(() => {
        adminAudioInitialized = true;
    }).catch(() => {});
}

document.addEventListener('click', adminInitAudio, { once: true });

// Solicitar permisos de notificación
if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
}

// Reproducir sonido de nueva orden
function playNewOrderSound() {
    const audio = new Audio(import.meta.env.VITE_APP_URL + '/sounds/admin.mp3');
    audio.volume = 1.0;
    audio.play().catch(() => {});
}

// Reproducir sonido de pedido listo para retirar
function playReadyToPickupSound() {
    const audio = new Audio(import.meta.env.VITE_APP_URL + '/sounds/retirar.mp3');
    audio.volume = 1.0;
    audio.play().catch(() => {});
}

// Mostrar notificación del navegador
function showAdminBrowserNotification(title, body, tag) {
    if ('Notification' in window && Notification.permission === 'granted') {
        new Notification(title, { body, tag, requireInteraction: false });
    }
}

// ─── Inicializar listener WebSocket ──────────────────────────
function initAdminOrderListener() {
    if (!window.Pusher) {
        setTimeout(initAdminOrderListener, 300);
        return;
    }

    const pusher = new window.Pusher(import.meta.env.VITE_REVERB_APP_KEY || 'reverb-key', {
        wsHost: import.meta.env.VITE_REVERB_HOST || 'localhost',
        wsPort: import.meta.env.VITE_REVERB_PORT || 8080,
        wssPort: import.meta.env.VITE_REVERB_PORT || 8080,
        forceTLS: false,
        enabledTransports: ['ws'],
        cluster: 'mt1',
    });

    pusher.connection.bind('connected', () => {
        /* console.log */('%c✅ [Admin] WebSocket conectado a Reverb', 'color: green; font-weight: bold');
    });

    pusher.connection.bind('disconnected', () => {
        console.warn('%c⚠️ [Admin] WebSocket desconectado', 'color: orange; font-weight: bold');
    });

    // Canal público: nuevas órdenes de clientes
    const ordersChannel = pusher.subscribe('orders');

    ordersChannel.bind('pusher:subscription_succeeded', () => {
        /* console.log */('%c✅ [Admin] Suscrito al canal "orders"', 'color: green; font-weight: bold');
    });

    ordersChannel.bind('new-order-created', (data) => {
        /* console.log */('%c🎉 [Admin] Nueva orden recibida #' + data.order_id, 'color: green; font-weight: bold');

        playNewOrderSound();
        showAdminBrowserNotification(
            '🎉 ¡Nueva Orden!',
            `#${data.order_id} - ${data.customer_name || data.name} - $${data.total}`,
            'admin-new-order-' + data.order_id
        );
    });

    // También escuchar actualizaciones de estado (vendor cambió estado → admin se entera)
    ordersChannel.bind('order-status-updated', (data) => {
        /* console.log */('%c🔄 [Admin] Estado de orden actualizado:', 'color: purple; font-weight: bold', data.order_id, data.new_status);

        if (data.new_status === 'completed') {
            playReadyToPickupSound();
            showAdminBrowserNotification(
                '📦 Pedido Listo para Retirar',
                `Pedido #${data.order_id} está listo`,
                'admin-order-ready-' + data.order_id
            );
        }
    });
}

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminOrderListener);
} else {
    initAdminOrderListener();
}
