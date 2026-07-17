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
            // Check if there is already a window/tab open with the seller app
            for (const client of clients) {
                if (client.url.includes('/vendedor/app') || client.url.endsWith('vendedor.' + new URL(self.registration.scope).hostname + '/')) {
                    return client.focus();
                }
            }

            // If not, open a new window
            return self.clients.openWindow('/vendedor/app');
        })
    );
});
