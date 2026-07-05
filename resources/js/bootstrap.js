import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.Pusher = Pusher;

// 🔌 Configurar Echo para conectarse a Reverb
// Usamos axios como authorizer para que el CSRF token se lea en cada request
// (no en el momento que se carga el script, cuando el DOM puede no estar listo)
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST || 'localhost',
    wsPort: import.meta.env.VITE_REVERB_PORT || 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT || 8080,
    forceTLS: false,
    enabledTransports: ['ws'],
    // Authorizer personalizado que usa axios → maneja CSRF automáticamente
    authorizer: (channel) => ({
        authorize: (socketId, callback) => {
            window.axios.post('/broadcasting/auth', {
                socket_id: socketId,
                channel_name: channel.name,
            })
            .then(response => callback(null, response.data))
            .catch(error => callback(error, null));
        }
    }),
});

// Logs de estado de la conexión (solo en desarrollo)
window.Echo.connector.pusher.connection.bind('connected', () => {
    /* console.log */('%c✅ [Echo] WebSocket conectado a Reverb', 'color: green; font-weight: bold');
});
window.Echo.connector.pusher.connection.bind('disconnected', () => {
    console.warn('%c⚠️ [Echo] WebSocket desconectado', 'color: orange');
});
