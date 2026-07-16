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

// Global listener for new client messages
if (window.Echo) {
    window.Echo.channel('orders')
        .listen('.message.sent', (e) => {
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
}
