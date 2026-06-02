
let assignedOrdersCount = window.filamentAssignedOrdersCount || 0;
const vendorCheckInterval = 2000; // 2 segundos para pruebas rápidas

// Solicitar permiso de notificaciones al cargar el panel
if (window.Notification && Notification.permission !== 'granted') {
    Notification.requestPermission();
}

function checkNewOrders() {
    fetch('/api/vendor/new-orders', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
    })
    .then(res => res.json())
    .then(data => {
        if (typeof data.assigned_orders_count !== 'undefined' && data.assigned_orders_count > assignedOrdersCount) {
            if (window.Notification && Notification.permission !== 'granted') {
                Notification.requestPermission();
            }
            if (window.Notification && Notification.permission === 'granted') {
                new Notification('¡Nuevo pedido!', {
                    body: 'Tienes un nuevo pedido asignado',
                    icon: '/images/og-image.png',
                });
            }
            const audio = new Audio('/sounds/order.mp3');
            audio.play();
            // Recargar la página para ver el nuevo pedido automáticamente
            location.reload();
        }
    })
    .catch(() => {});
}

setInterval(checkNewOrders, vendorCheckInterval);
