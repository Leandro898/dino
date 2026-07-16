@vite(['resources/js/app.js'])

<x-filament-panels::page>
    <livewire:admin-orders-table />
</x-filament-panels::page>

<script type="module">
    // Al usar type="module", el script se ejecuta de forma diferida,
    // por lo que el DOM ya está listo.
    const setupEcho = () => {
        if (window.Echo) {
            console.log('Echo is ready! Listening for new orders...');
            window.Echo.channel('orders')
                .listen('.new-order-created', (e) => {
                    console.log('🔔 Nuevo pedido recibido vía Echo JS!', e);
                    if (window.Livewire) {
                        window.Livewire.dispatch('order-updated');
                    } else {
                        window.location.reload();
                    }
                });
        } else {
            console.warn('Echo no está disponible, reintentando...');
            setTimeout(setupEcho, 500);
        }
    };
    
    // Iniciar Echo
    setTimeout(setupEcho, 500);

    // Escuchar mensajes directamente del Service Worker (Push Notifications)
    // Esto es 100% a prueba de fallos: si llega la notificación al celular,
    // el SW le avisará a la ventana activa que recargue la tabla,
    // incluso si el WebSocket se durmió.
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.addEventListener('message', (event) => {
            if (event.data && event.data.type === 'NEW_ORDER') {
                console.log('📱 [ServiceWorker] Mensaje de nueva orden recibido en cliente!');
                if (window.Livewire) {
                    window.Livewire.dispatch('order-updated');
                } else {
                    window.location.reload();
                }
            }
        });
    }

    // Cuando la PWA vuelve a estar visible (por ejemplo, al volver del background),
    // refrescamos la tabla para recuperar órdenes que pudimos haber perdido
    // mientras el WebSocket estaba dormido.
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            console.log('📱 PWA volvió a estar activa, refrescando tabla...');
            if (window.Livewire) {
                window.Livewire.dispatch('order-updated');
            } else {
                window.location.reload();
            }
        }
    });
</script>
