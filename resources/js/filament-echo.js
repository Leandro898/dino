import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

window.addEventListener('play-notification-sound', () => {
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        
        // Simple "ding" sound
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.type = 'sine';
        oscillator.frequency.setValueAtTime(800, audioContext.currentTime); // High pitch start
        oscillator.frequency.exponentialRampToValueAtTime(1200, audioContext.currentTime + 0.1);
        
        gainNode.gain.setValueAtTime(0, audioContext.currentTime);
        gainNode.gain.linearRampToValueAtTime(0.5, audioContext.currentTime + 0.05);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        oscillator.start();
        oscillator.stop(audioContext.currentTime + 0.5);
    } catch (e) {
        console.warn('Notification sound failed', e);
    }
});

// Global listener for new client messages and new orders
if (window.Echo) {
    const ordersChannel = window.Echo.channel('orders');

    ordersChannel.listen('.message.sent', (e) => {
        // Only process if user is admin and not on the chat/list page to avoid duplicates
        if (window.authUserRole === 'admin') {
            const path = window.location.pathname;
            if (!path.includes('/admin/custom-requests')) {
                // Play notification sound
                window.dispatchEvent(new CustomEvent('play-notification-sound'));
                
                // Show Filament toast notification
                if (window.FilamentNotification) {
                    const sender = e.senderName || 'Cliente';
                    const text = e.messageText || 'Ha enviado un mensaje.';
                    new FilamentNotification()
                        .title('Nuevo mensaje de ' + sender)
                        .body(text)
                        .info()
                        .duration(10000)
                        .actions([
                            new FilamentNotificationAction('view')
                                .label('Ver Chat')
                                .url('/admin/custom-requests/' + e.requestId + '/chat')
                                .button()
                        ])
                        .send();
                }
            }
        }
    });

    ordersChannel.listen('.new-order-created', (e) => {
        console.log('🎉 [Live Notification] Nueva orden recibida #' + e.order_id, e);
        if (window.authUserRole === 'admin' || window.authUserRole === 'vendor') {
            try {
                const audio = new Audio('/sounds/admin.mp3');
                audio.volume = 1.0;
                audio.play().catch(() => {
                    window.dispatchEvent(new CustomEvent('play-notification-sound'));
                });
            } catch(err) {
                window.dispatchEvent(new CustomEvent('play-notification-sound'));
            }
            
            window.dispatchEvent(new CustomEvent('play-notification-sound'));

            if (window.FilamentNotification) {
                new FilamentNotification()
                    .title('🎉 ¡Nuevo Pedido #' + e.order_id + '!')
                    .body('Cliente: ' + (e.customer_name || e.name || 'Cliente') + ' - Total: $' + Number(e.total).toLocaleString('es-AR', {minimumFractionDigits: 2}) + ' ARS (' + (e.payment_method || 'Efectivo') + ')')
                    .success()
                    .duration(15000)
                    .actions([
                        new FilamentNotificationAction('view')
                            .label('Ver Pedidos')
                            .url('/admin/orders')
                            .button()
                    ])
                    .send();
            }

            window.dispatchEvent(new CustomEvent('vendor-new-order-assigned', { detail: e }));
            window.dispatchEvent(new CustomEvent('new-order-created', { detail: e }));
        }
    });

    ordersChannel.listen('.order-status-updated', (e) => {
        if (window.authUserRole === 'admin' || window.authUserRole === 'vendor') {
            if (e.new_status === 'completed') {
                try {
                    const audio = new Audio('/sounds/retirar.mp3');
                    audio.volume = 1.0;
                    audio.play().catch(() => {});
                } catch(err) {}
            }
            window.dispatchEvent(new CustomEvent('order-status-updated', { detail: e }));
        }
    });
}
