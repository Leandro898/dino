self.addEventListener('push', function (e) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    if (e.data) {
        var msg = e.data.json();
        
        // Notify any open PWA windows
        e.waitUntil(
            clients.matchAll({ type: 'window' }).then(windowClients => {
                windowClients.forEach(client => {
                    client.postMessage({
                        type: 'NEW_ORDER',
                        data: msg
                    });
                });
            })
        );

        e.waitUntil(self.registration.showNotification(msg.title, {
            body: msg.body,
            icon: msg.icon || '/favicon.ico',
            badge: msg.badge || '/favicon.ico',
            vibrate: [200, 100, 200, 100, 200, 100, 200],
            requireInteraction: true,
            data: msg.data,
            actions: msg.actions || []
        }));
    }
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    var url = '/admin';
    if (event.notification.data && event.notification.data.url) {
        url = event.notification.data.url;
    }

    event.waitUntil(
        clients.matchAll({ type: 'window' }).then(windowClients => {
            for (var i = 0; i < windowClients.length; i++) {
                var client = windowClients[i];
                if (client.url === url && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});
