self.addEventListener('install', (event) => {
    event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
            for (const client of clients) {
                if (client.url.includes('/repartidor/app')) {
                    return client.focus();
                }
            }

            return self.clients.openWindow('/repartidor/app');
        })
    );
});

self.addEventListener('push', function (e) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    if (e.data) {
        var msg = e.data.json();

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
