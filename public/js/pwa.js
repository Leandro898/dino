document.addEventListener('DOMContentLoaded', () => {
    if ('serviceWorker' in navigator && 'PushManager' in window) {
        navigator.serviceWorker.register('/sw.js')
            .then(function(swReg) {
                
                // Pedir permiso y suscribir
                document.body.addEventListener('click', function askPermissionOnce() {
                    document.body.removeEventListener('click', askPermissionOnce);
                    
                    if (Notification.permission !== 'granted') {
                        Notification.requestPermission().then(function(permission) {
                            if (permission === 'granted') {
                                subscribeUser(swReg);
                            }
                        });
                    } else {
                        // Ya tiene permisos, aseguramos la suscripción
                        subscribeUser(swReg);
                    }
                }, { once: true });
                
                if (Notification.permission === 'granted') {
                    subscribeUser(swReg);
                }
            })
            .catch(function(error) {
                console.error('Service Worker Error', error);
            });
    }

    function subscribeUser(swReg) {
        const vapidPublicKey = document.querySelector('meta[name="vapid-public-key"]')?.getAttribute('content');
        if (!vapidPublicKey) return;

        const convertedVapidKey = urlBase64ToUint8Array(vapidPublicKey);

        swReg.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: convertedVapidKey
        })
        .then(function(subscription) {
            console.log('User is subscribed.');
            // Send subscription to backend
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            fetch('/push-subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify(subscription)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Bad status code from server.');
                }
                return response.json();
            })
            .then(responseData => {
                console.log('Push subscription saved', responseData);
            })
            .catch(error => {
                console.error('Failed to save subscription', error);
            });
        })
        .catch(function(err) {
            // Silencioso
        });
    }

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/\-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }
});
