// --- Sidebar & Views ---
function toggleDrawer() {
    const drawer = document.getElementById('sideDrawer');
    if (drawer.classList.contains('active')) {
        history.back(); // Triggers popstate to close
    } else {
        drawer.classList.add('active');
        document.getElementById('drawerBackdrop').classList.add('active');
        history.pushState({ modal: 'drawer' }, '', '#menu');
    }
}

function openView(id) {
    const drawer = document.getElementById('sideDrawer');
    if (drawer.classList.contains('active')) {
        drawer.classList.remove('active');
        document.getElementById('drawerBackdrop').classList.remove('active');
        history.replaceState({ modal: id }, '', '#' + id);
    } else {
        history.pushState({ modal: id }, '', '#' + id);
    }
    document.getElementById(id).classList.add('active');

    if (id === 'supportView') {
        openSupportChat();
    }
}

function closeView(id) {
    history.back(); // Triggers popstate to close
}

window.addEventListener('popstate', function(event) {
    // Close all full screen views
    document.querySelectorAll('.pwa-view').forEach(function(el) {
        el.classList.remove('active');
    });
    isSupportViewActive = false;
    // Close drawer
    document.getElementById('sideDrawer').classList.remove('active');
    document.getElementById('drawerBackdrop').classList.remove('active');

    // Restore state if we are going back to a nested view (e.g. from view -> menu)
    if (event.state && event.state.modal) {
        if (event.state.modal === 'drawer') {
            document.getElementById('sideDrawer').classList.add('active');
            document.getElementById('drawerBackdrop').classList.add('active');
        } else {
            let view = document.getElementById(event.state.modal);
            if (view) view.classList.add('active');
        }
    }
});

function togglePush(checkbox) {
    if(checkbox.checked) {
        if ('Notification' in window && Notification.permission !== 'granted') {
            Notification.requestPermission();
        }
    }
}

function testNotification() {
    if (!('Notification' in window)) {
        alert("Tu navegador no soporta notificaciones push.");
        return;
    }

    if (Notification.permission === "granted") {
        if (navigator.serviceWorker) {
            navigator.serviceWorker.ready.then(function(registration) {
                registration.showNotification("¡Todo bien! 🎉", {
                    body: "Tus notificaciones están funcionando y deberías poder recibirlas.",
                    icon: "https://ui-avatars.com/api/?name=B&size=192&background=ffffff&color=7e22ce&bold=true",
                    vibrate: [200, 100, 200]
                });
            });
        } else {
            new Notification("¡Todo bien! 🎉", {
                body: "Tus notificaciones están funcionando y deberías poder recibirlas."
            });
        }
    } else if (Notification.permission !== "denied") {
        Notification.requestPermission().then(function (permission) {
            if (permission === "granted") {
                testNotification();
            } else {
                alert("Necesitas aceptar los permisos para recibir alertas.");
            }
        });
    } else {
        alert("Las notificaciones están bloqueadas. Debes activarlas desde la configuración de tu navegador.");
    }
}

// --- API & State ---
const latestUrl = window.BariDeliveryConfig.routes.ordersLatest;
let isConnected = false;
let isSupportViewActive = false;
let baselineInitialized = false;
let deferredPrompt = null;
let currentActiveOrderData = null;

// --- WebSocket configuration (Laravel Echo + Reverb) ---
window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: window.BariDeliveryConfig.reverb.key,
    wsHost: window.location.hostname,
    wsPort: window.BariDeliveryConfig.reverb.wsPort,
    wssPort: window.BariDeliveryConfig.reverb.wssPort,
    forceTLS: window.BariDeliveryConfig.reverb.forceTLS,
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    }
});

// Suscribirse al canal de soporte de manera global al cargar la página
(function() {
    const userId = window.BariDeliveryConfig.userId;
    window.Echo.channel(`support.${userId}`)
        .listen('.support-message.sent', (data) => {
            console.log('Support message received:', data);
            const currentUserId = window.BariDeliveryConfig.userId;
            if (data.senderId !== currentUserId) {
                if (isSupportViewActive && document.getElementById('supportView').classList.contains('active')) {
                    fetchSupportMessages();
                    playAlertSound();
                } else {
                    document.getElementById('supportBadge').style.display = 'block';
                    document.getElementById('supportDrawerBadge').style.display = 'block';
                    playAlertSound();
                }
            }
        });
})();

// --- DOM Elements ---
const mapDiv = document.getElementById('map');
const topStatusDot = document.getElementById('topStatusDot');
const topStatusText = document.getElementById('topStatusText');
const infoTitle = document.getElementById('infoTitle');
const infoDesc = document.getElementById('infoDesc');
const disconnectedActions = document.getElementById('disconnectedActions');
const connectedActions = document.getElementById('connectedActions');
const startBtn = document.getElementById('startBtn');
const stopBtn = document.getElementById('stopBtn');
const installBanner = document.getElementById('installBanner');
const installAppBtn = document.getElementById('installAppBtn');
const orderAlert = document.getElementById('orderAlert');
const orderAlertDesc = document.getElementById('orderAlertDesc');

// --- Initialize Map ---
const map = L.map('map', { zoomControl: false }).setView([-41.1335, -71.3103], 13); // Bariloche coords approx
L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; OpenStreetMap &copy; CARTO',
    subdomains: 'abcd',
    maxZoom: 20
}).addTo(map);

const bottomSheet = document.getElementById('bottomSheet');
let dragStartY = 0;
let initialSheetTranslate = 0;
let sheetContent = document.querySelector('.bottom-sheet-content');

map.on('click', () => {
    if (bottomSheet && (!bottomSheet.classList.contains('collapsed'))) {
        bottomSheet.style.transition = 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        bottomSheet.classList.remove('expanded', 'half');
        bottomSheet.classList.add('collapsed');
        bottomSheet.style.transform = '';
    }
});

if (bottomSheet) {
    let isDragging = false;

    bottomSheet.addEventListener('touchstart', (e) => {
        dragStartY = e.touches[0].clientY;
        isDragging = true;
        
        bottomSheet.style.transition = 'none';
        
        const style = window.getComputedStyle(bottomSheet);
        const transform = style.transform;
        if (transform !== 'none') {
            const matrix = new DOMMatrix(transform);
            initialSheetTranslate = matrix.m42;
        } else {
            initialSheetTranslate = 0;
        }
    }, { passive: true });

    bottomSheet.addEventListener('touchmove', (e) => {
        if (!isDragging) return;
        const currentY = e.touches[0].clientY;
        const deltaY = currentY - dragStartY;
        
        // Permitir scroll interno si está expandido y hacemos scroll hacia abajo en el contenido
        const content = document.querySelector('.bottom-sheet-content');
        if (bottomSheet.classList.contains('expanded') && content && content.scrollTop > 0 && deltaY < 0) {
            return; // let native scroll happen
        }
        
        if (e.cancelable) {
            e.preventDefault();
        }
        
        let newTranslate = initialSheetTranslate + deltaY;
        if (newTranslate < 0) newTranslate = 0;
        bottomSheet.style.transform = `translateY(${newTranslate}px)`;
    }, { passive: false });

    bottomSheet.addEventListener('touchend', (e) => {
        if (!isDragging) return;
        isDragging = false;
        const currentY = e.changedTouches[0].clientY;
        const deltaY = currentY - dragStartY;
        
        bottomSheet.style.transition = 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        bottomSheet.style.transform = ''; 
        
        const currentTranslate = initialSheetTranslate + deltaY;
        const maxTranslate = bottomSheet.offsetHeight;
        
        bottomSheet.classList.remove('expanded', 'half', 'collapsed');
        
        const snapExpanded = 0;
        const snapHalf = maxTranslate - 380;
        const snapCollapsed = maxTranslate - 130;
        
        const distExpanded = Math.abs(currentTranslate - snapExpanded);
        const distHalf = Math.abs(currentTranslate - snapHalf);
        const distCollapsed = Math.abs(currentTranslate - snapCollapsed);
        
        // Momentum logic
        if (Math.abs(deltaY) > 20) {
            if (deltaY < 0) {
                // Hacia arriba
                if (currentTranslate > snapHalf) {
                    bottomSheet.classList.add('half');
                } else {
                    bottomSheet.classList.add('expanded');
                }
            } else {
                // Hacia abajo
                if (currentTranslate < snapHalf) {
                    bottomSheet.classList.add('half');
                } else {
                    bottomSheet.classList.add('collapsed');
                }
            }
            return;
        }
        
        if (distExpanded < distHalf && distExpanded < distCollapsed) {
            bottomSheet.classList.add('expanded');
        } else if (distHalf < distExpanded && distHalf < distCollapsed) {
            bottomSheet.classList.add('half');
        } else {
            bottomSheet.classList.add('collapsed');
        }
    });
}

// Marker for rider location and custom icons
const riderIcon = L.divIcon({
    html: `
        <div style="position: relative; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
            <div style="position: absolute; width: 100%; height: 100%; background-color: #8b5cf6; border-radius: 50%; opacity: 0.5; animation: pulse-rider 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;"></div>
            <div style="position: relative; width: 12px; height: 12px; background-color: #7c3aed; border: 2px solid white; border-radius: 50%; box-shadow: 0 1px 4px rgba(0,0,0,0.3); z-index: 2;"></div>
        </div>
    `,
    className: 'rider-location-icon',
    iconSize: [24, 24],
    iconAnchor: [12, 12]
});

let riderMarker = L.marker([-41.1335, -71.3103], { icon: riderIcon, zIndexOffset: 1000 }).addTo(map);
let vendorMarker = null;
let customerMarker = null;
let routeLine = null;

const vendorIcon = L.divIcon({
    html: `<div style="position: relative; width: 36px; height: 46px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
        <div style="background-color: #111827; color: white; padding: 6px; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; font-size: 16px; border: 2.5px solid white; box-shadow: 0 3px 6px rgba(0,0,0,0.35); z-index: 2;">🏪</div>
        <div style="width: 3px; height: 10px; background-color: #111827; margin-top: -3px; z-index: 1; box-shadow: 1px 1px 3px rgba(0,0,0,0.25);"></div>
    </div>`,
    className: 'custom-div-icon',
    iconSize: [36, 46],
    iconAnchor: [18, 46]
});

const customerIcon = L.divIcon({
    html: `<div style="position: relative; width: 36px; height: 46px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
        <div style="background-color: #ff3366; color: white; padding: 6px; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; font-size: 16px; border: 2.5px solid white; box-shadow: 0 3px 6px rgba(0,0,0,0.35); z-index: 2;">🏠</div>
        <div style="width: 3px; height: 10px; background-color: #ff3366; margin-top: -3px; z-index: 1; box-shadow: 1px 1px 3px rgba(0,0,0,0.25);"></div>
    </div>`,
    className: 'custom-div-icon',
    iconSize: [36, 46],
    iconAnchor: [18, 46]
});

// Try to get actual location
if ("geolocation" in navigator) {
    navigator.geolocation.getCurrentPosition(position => {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        map.setView([lat, lng], 15);
        riderMarker.setLatLng([lat, lng]);
    });
}

// --- PWA Installation ---
function updateInstallUI() {
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    if (isStandalone) {
        if (installBanner) installBanner.classList.remove('visible');
    } else if (deferredPrompt) {
        if (installBanner) installBanner.classList.add('visible');
    }
}

window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    deferredPrompt = event;
    updateInstallUI();
});

if (installAppBtn) {
    installAppBtn.addEventListener('click', async () => {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        const result = await deferredPrompt.userChoice;
        if (result.outcome === 'accepted') {
            installBanner.classList.remove('visible');
        }
        deferredPrompt = null;
    });
}

// --- Audio Alert ---
function playAlertSound() {
    try {
        const audio = new Audio('/sounds/rider-alert.mp3');
        audio.play().catch(e => console.warn('Audio play failed', e));
    } catch (error) {
        console.warn('Audio play failed', error);
    }
}

function calculateDistance(lat1, lon1, lat2, lon2) {
    if (!lat1 || !lon1 || !lat2 || !lon2) return null;
    const R = 6371; // Radius of the earth in km
    const dLat = deg2rad(lat2 - lat1);
    const dLon = deg2rad(lon2 - lon1);
    const a =
        Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
        Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    const d = R * c; // Distance in km
    return d;
}

function deg2rad(deg) {
    return deg * (Math.PI/180);
}

// --- App Logic ---
function getSavedOrderId() {
    const value = localStorage.getItem('delivery_last_order_id');
    return value ? parseInt(value, 10) : null;
}

function saveOrderId(orderId) {
    localStorage.setItem('delivery_last_order_id', String(orderId));
}

async function showOrderNotification(order) {
    playAlertSound();
    
    if (navigator.vibrate) navigator.vibrate([200, 100, 200]);

    if (Notification.permission === 'granted') {
        new Notification(`¡Nuevo Pedido #${order.id}!`, {
            body: `Retiro: ${order.vendor_name || 'Comercio'}\nEntrega: ${order.customer_name}\nGanancia: $${Number(order.rider_earnings || 0).toFixed(0)}`,
            icon: window.BariDeliveryConfig.assets.ogImage
        });
    }

    // Siempre mostrar el modal visual en pantalla por si las notificaciones nativas fallan
    showCustomModal('¡Nuevo Pedido Asignado!', `Tienes un pedido de ${order.vendor_name || 'Comercio'} para entregar a ${order.customer_name}. Revisa los detalles.`, false);
}

async function fetchOrders() {
    if (!isConnected) return;
    
    try {
        const urlWithTimestamp = latestUrl + (latestUrl.includes('?') ? '&' : '?') + 't=' + new Date().getTime();
        const response = await fetch(urlWithTimestamp, { headers: { 'Accept': 'application/json', 'Cache-Control': 'no-cache' }});
        if (!response.ok) throw new Error('HTTP Error');
        const data = await response.json();

        if (data.has_order) {
            const sheetEl = document.querySelector('.bottom-sheet');
            if (sheetEl && sheetEl.classList.contains('collapsed')) {
                sheetEl.classList.remove('collapsed');
                sheetEl.style.transform = 'translateY(0)';
            }
        }

        if (!data.has_order) {
            currentActiveOrderData = null;
            updateHelpCenterUI();
            infoTitle.textContent = "Sin pedidos asignados";
            infoDesc.innerHTML = "Esperando que se te asigne un pedido. Mantente en línea.";
            localStorage.removeItem('delivery_last_order_id');
            
            if (vendorMarker) { map.removeLayer(vendorMarker); vendorMarker = null; }
            if (customerMarker) { map.removeLayer(customerMarker); customerMarker = null; }
            if (routeLine) { map.removeLayer(routeLine); routeLine = null; }

            disconnectedActions.style.display = 'none';
            connectedActions.style.display = 'flex';
            return;
        }

        currentActiveOrderData = data;
        updateHelpCenterUI();

        // Remove previous markers/routes
        if (vendorMarker) { map.removeLayer(vendorMarker); vendorMarker = null; }
        if (customerMarker) { map.removeLayer(customerMarker); customerMarker = null; }
        if (routeLine) { map.removeLayer(routeLine); routeLine = null; }

        const riderLatLng = riderMarker.getLatLng();
        const points = [];
        points.push([riderLatLng.lat, riderLatLng.lng]);

        // 1. Comercio
        if (data.vendor_latitude && data.vendor_longitude) {
            vendorMarker = L.marker([data.vendor_latitude, data.vendor_longitude], { icon: vendorIcon })
                .addTo(map)
                .bindPopup(`<strong>Comercio:</strong> ${data.vendor_name}<br>${data.vendor_address}`)
                .bindTooltip(`🏪 Retiro: ${data.vendor_name}`, {
                    permanent: true,
                    direction: 'top',
                    offset: [0, -42],
                    className: 'tooltip-vendor'
                });
            points.push([data.vendor_latitude, data.vendor_longitude]);
        }

        // 2. Cliente
        if (data.latitude && data.longitude) {
            customerMarker = L.marker([data.latitude, data.longitude], { icon: customerIcon })
                .addTo(map)
                .bindPopup(`<strong>Cliente:</strong> ${data.customer_name}<br>${data.address}`)
                .bindTooltip(`🏠 Entrega: ${data.customer_name}`, {
                    permanent: true,
                    direction: 'top',
                    offset: [0, -42],
                    className: 'tooltip-customer'
                });
            points.push([data.latitude, data.longitude]);
        }

        // 3. Zoom bounds
        if (points.length >= 2) {
            const bounds = L.latLngBounds(points);
            map.fitBounds(bounds, { padding: [50, 50] });
        }

        saveOrderId(data.id);
        const distToVendor = calculateDistance(riderLatLng.lat, riderLatLng.lng, data.vendor_latitude, data.vendor_longitude);
        const distToCust = calculateDistance(data.vendor_latitude, data.vendor_longitude, data.latitude, data.longitude);

        const distToVendorStr = distToVendor ? `${distToVendor.toFixed(2)} km` : '— km';
        const distToCustStr = distToCust ? `${distToCust.toFixed(2)} km` : '— km';

        const isCash = data.payment_method === 'efectivo';
        const total = Number(data.total) || 0;
        const shipping = Number(data.shipping_cost) || 0;
        const earnings = Number(data.rider_earnings) || 0;
        const payToStore = Math.max(0, total - shipping);

        let paymentBadgeHtml = '';
        if (isCash) {
            paymentBadgeHtml = `<span style="background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;">Efectivo necesario</span>`;
        } else {
            paymentBadgeHtml = `<span style="background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;">Pago Online (No cobrar)</span>`;
        }

        // Header
        infoTitle.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                ${paymentBadgeHtml}
                ${!data.is_accepted ? `
                <span style="font-size: 0.85rem; color: #64748b; font-weight: 600;">Nuevo Pedido</span>
                ` : `
                <span style="font-size: 0.85rem; color: #64748b; font-weight: 600;">Pedido #${data.id}</span>
                `}
            </div>
        `;

        infoDesc.innerHTML = `
            <!-- Timeline de Entrega -->
            <div style="position: relative; margin-top: 16px; padding-left: 28px;">
                <!-- Línea conectora -->
                <div style="position: absolute; left: 12px; top: 24px; bottom: 24px; width: 2px; border-left: 2px solid #e2e8f0;"></div>
                
                <!-- Comercio -->
                <div style="position: relative; margin-bottom: 24px;">
                    <div style="position: absolute; left: -28px; top: 2px; width: 24px; height: 24px; background: white; border: 2px solid #111827; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 11px;">🏪</div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <h4 style="margin: 0; font-size: 1rem; font-weight: 700; color: #1e293b;">${data.vendor_name || 'Comercio'}</h4>
                            <p style="margin: 2px 0 0 0; font-size: 0.85rem; color: #64748b; line-height: 1.4;">${data.vendor_address || 'Dirección local'}</p>
                        </div>
                        <span style="font-size: 0.85rem; font-weight: 600; color: #475569;">${distToVendorStr}</span>
                    </div>
                    <div style="margin-top: 8px;">
                        <span style="display: inline-block; border: 1px solid #e2e8f0; padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; color: #64748b;">
                            Pagar al local $${isCash ? payToStore.toFixed(0) : '0'}
                        </span>
                    </div>
                </div>

                <!-- Cliente -->
                <div style="position: relative;">
                    <div style="position: absolute; left: -28px; top: 2px; width: 24px; height: 24px; background: white; border: 2px solid #111827; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px;">👤</div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <h4 style="margin: 0; font-size: 1rem; font-weight: 700; color: #1e293b;">Entrega a ${data.customer_name}</h4>
                            <p style="margin: 2px 0 0 0; font-size: 0.85rem; color: #64748b; line-height: 1.4;">${data.address || 'Sin dirección'}</p>
                        </div>
                        <span style="font-size: 0.85rem; font-weight: 600; color: #475569;">${distToCustStr}</span>
                    </div>
                    <div style="margin-top: 8px;">
                        <span style="display: inline-block; border: 1px solid #e2e8f0; padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; color: #64748b;">
                            Cobrar al usuario $${isCash ? total.toFixed(0) : '0'}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Resumen Financiero -->
            <div style="margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 16px;">
                ${window.queuedDisconnect ? `
                    <div style="background: #fffbeb; border: 1px solid #fcd34d; padding: 12px; border-radius: 8px; margin-bottom: 16px;">
                        <span style="color: #d97706; font-size: 0.9rem; font-weight: 700; display: block; text-align: center;">⏸️ Pausa Programada: Te desconectarás al entregar.</span>
                        <button onclick="window.queuedDisconnect = false; fetchOrders();" style="background: none; border: none; color: #d97706; font-size: 0.8rem; font-weight: 600; width: 100%; text-decoration: underline; margin-top: 5px; cursor: pointer;">Cancelar pausa</button>
                    </div>
                ` : ''}
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-size: 0.9rem; font-weight: 700; color: #475569;">Pagar por el pedido</span>
                    <span style="font-size: 1rem; font-weight: 800; color: #1e293b;">$${isCash ? payToStore.toFixed(0) : '0'}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <span style="font-size: 1.05rem; font-weight: 800; color: #ff5722;">🔥 Tu ganancia</span>
                    <span style="font-size: 1.25rem; font-weight: 900; color: #ff5722;">$${earnings.toFixed(0)}</span>
                </div>

                ${!data.is_accepted ? `
                    <div style="display: flex; gap: 10px;">
                        <button onclick="rejectCurrentOrder(${data.id})" class="btn" style="flex: 1; height: 50px; background: #ef4444; color: white; border: none; border-radius: 12px; font-size: 1rem; font-weight: 700; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 10px rgba(239,68,68,0.2);">
                            Rechazar
                        </button>
                        <button onclick="acceptCurrentOrder(${data.id})" class="btn" style="flex: 2; height: 50px; background: #111827; color: white; border: none; border-radius: 12px; font-size: 1.05rem; font-weight: 700; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                            Aceptar pedido
                        </button>
                    </div>
                ` : `
                    ${(data.latitude && data.longitude) ? `
                        <a href="https://www.google.com/maps/dir/?api=1&destination=${data.latitude},${data.longitude}&travelmode=driving" 
                           target="_blank" 
                           style="width: 100%; height: 46px; margin-bottom: 10px; background: #22c55e; color: white; border-radius: 10px; font-size: 0.95rem; font-weight: 700; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 6px; box-shadow: 0 4px 10px rgba(0,0,0,0.12);">
                            📍 Navegar con GPS
                        </a>
                    ` : ''}
                    
                    ${['assigned', 'processing'].includes(data.status) ? `
                        <button id="btn-mark-picked-up" onclick="markPickedUp(${data.id}, ${data.vendor_latitude || 'null'}, ${data.vendor_longitude || 'null'})" class="btn" style="width: 100%; height: 50px; background: ${distToVendor !== null && distToVendor > 0.15 ? '#94a3b8' : '#f59e0b'}; color: white; border: none; border-radius: 12px; font-size: 1.05rem; font-weight: 700; cursor: ${distToVendor !== null && distToVendor > 0.15 ? 'not-allowed' : 'pointer'}; transition: all 0.2s; box-shadow: 0 4px 10px rgba(0,0,0,0.15);" ${distToVendor !== null && distToVendor > 0.15 ? 'disabled' : ''}>
                            ${distToVendor !== null && distToVendor > 0.15 ? '📍 Acércate al local para retirar' : '🛍️ Confirmar Retiro del Local'}
                        </button>
                    ` : ''}

                    ${data.status === 'shipped' ? `
                        <button onclick="markDelivered(${data.id}, ${data.latitude || 'null'}, ${data.longitude || 'null'})" class="btn" style="width: 100%; height: 50px; background: #3b82f6; color: white; border: none; border-radius: 12px; font-size: 1.05rem; font-weight: 700; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                            ✅ Marcar como Entregado
                        </button>
                    ` : ''}
                `}
            </div>
        `;

        disconnectedActions.style.display = 'none';
        connectedActions.style.display = 'flex';
    } catch (error) {
        console.error('Error fetching orders:', error);
        infoDesc.textContent = "Problemas de conexión al servidor.";
    }
}

function showCustomModal(title, message, showCancel = true) {
    return new Promise((resolve) => {
        const modal = document.getElementById('customModal');
        document.getElementById('modalTitle').textContent = title;
        document.getElementById('modalMessage').textContent = message;
        
        const btnCancel = document.getElementById('modalBtnCancel');
        const btnConfirm = document.getElementById('modalBtnConfirm');
        
        btnCancel.style.display = showCancel ? 'block' : 'none';
        
        const cleanup = () => {
            modal.classList.remove('show');
            btnCancel.onclick = null;
            btnConfirm.onclick = null;
        };

        btnCancel.onclick = () => { cleanup(); resolve(false); };
        btnConfirm.onclick = () => { cleanup(); resolve(true); };
        
        // Trigger reflow
        void modal.offsetWidth;
        modal.classList.add('show');
    });
}

window.acceptCurrentOrder = async function(orderId) {
    try {
        const response = await fetch(`/repartidor/pedidos/${orderId}/aceptar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        if (!response.ok) throw new Error('Error al aceptar');
        const data = await response.json();
        if (data.success) {
            fetchOrders();
        } else {
            await showCustomModal('Atención', data.error || 'No se pudo aceptar el pedido.', false);
        }
    } catch (error) {
        console.error(error);
        await showCustomModal('Error', 'Ocurrió un error al procesar la aceptación.', false);
    }
};

window.rejectCurrentOrder = async function(orderId) {
    const confirmed = await showCustomModal('Rechazar Pedido', '¿Estás seguro de que deseas rechazar este pedido?');
    if (!confirmed) return;
    try {
        const response = await fetch(`/repartidor/pedidos/${orderId}/rechazar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        if (!response.ok) throw new Error('Error al rechazar');
        const data = await response.json();
        if (data.success) {
            fetchOrders();
        } else {
            await showCustomModal('Atención', data.error || 'No se pudo rechazar el pedido.', false);
        }
    } catch (error) {
        console.error(error);
        await showCustomModal('Error', 'Ocurrió un error al procesar el rechazo.', false);
    }
};

window.markPickedUp = async function(orderId, vendorLat, vendorLng) {
    if (vendorLat && vendorLng && riderMarker) {
        const riderLatLng = riderMarker.getLatLng();
        const dist = calculateDistance(riderLatLng.lat, riderLatLng.lng, vendorLat, vendorLng);
        if (dist > 0.15) { // 150 metros
            await showCustomModal('Demasiado lejos', `Estás a ${(dist * 1000).toFixed(0)} metros del local. Debes estar a menos de 150m para poder confirmar el retiro.`, false);
            return;
        }
    }

    const confirmed = await showCustomModal('Retiro del Local', '¿Confirmas que has retirado este pedido del local?');
    if (!confirmed) return;
    try {
        const response = await fetch(`/repartidor/pedidos/${orderId}/retirado`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        if (!response.ok) throw new Error('Error al confirmar retiro');
        const data = await response.json();
        if (data.success) {
            fetchOrders();
        } else {
            await showCustomModal('Atención', data.error || 'No se pudo confirmar el retiro.', false);
        }
    } catch (error) {
        console.error(error);
        await showCustomModal('Error', 'Ocurrió un error al confirmar el retiro.', false);
    }
};

window.markDelivered = async function(orderId, custLat, custLng) {
    if (custLat && custLng && riderMarker) {
        const riderLatLng = riderMarker.getLatLng();
        const dist = calculateDistance(riderLatLng.lat, riderLatLng.lng, custLat, custLng);
        if (dist > 0.25) { // 250 metros
            await showCustomModal('Demasiado lejos', `Estás a ${(dist * 1000).toFixed(0)} metros del cliente. Debes estar a menos de 250m para poder marcar el pedido como entregado.`, false);
            return;
        }
    }

    const confirmed = await showCustomModal('Entregar Pedido', '¿Confirmas que has entregado este pedido al cliente?');
    if (!confirmed) return;
    try {
        const response = await fetch(`/repartidor/pedidos/${orderId}/entregado`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        if (!response.ok) throw new Error('Error al marcar entregado');
        const data = await response.json();
        if (data.success) {
            if (window.queuedDisconnect) {
                window.queuedDisconnect = false;
                toggleConnection(false);
            } else {
                fetchOrders();
            }
        } else {
            await showCustomModal('Atención', data.error || 'No se pudo marcar como entregado.', false);
        }
    } catch (error) {
        console.error(error);
        await showCustomModal('Error', 'Ocurrió un error al marcar como entregado.', false);
    }
};

async function toggleConnection(connect) {
    if (connect) {
        if ('Notification' in window) {
            try {
                await Notification.requestPermission();
            } catch (e) {
                console.warn('Permiso de notificación denegado o no soportado en contexto no seguro.', e);
            }
        }

        isConnected = true;
        localStorage.setItem('delivery_is_connected', 'true');
        topStatusDot.className = 'dot active';
        topStatusText.textContent = 'Conectado';
        infoTitle.textContent = 'Escuchando pedidos';
        infoDesc.textContent = 'Conectando con el servidor...';
        
        disconnectedActions.style.display = 'none';
        connectedActions.style.display = 'flex';

        baselineInitialized = false; // reset baseline
        fetchOrders();

        // Subscribe to rider private channel
        const userId = window.BariDeliveryConfig.userId;
        window.Echo.private(`App.Models.User.${userId}`)
            .listen('.order-updated-for-rider', (data) => {
                console.log('Order updated/assigned received:', data);
                
                if (!isConnected) {
                    isConnected = true;
                    topStatusDot.className = 'dot active';
                    topStatusText.textContent = 'Conectado (v2)';
                    disconnectedActions.style.display = 'none';
                    connectedActions.style.display = 'flex';
                }

                if (data.is_new_assignment) {
                    showOrderNotification({
                        id: data.order_id,
                        customer_name: data.customer_name,
                        total: data.total,
                        shipping_cost: data.shipping_cost,
                        payment_method: data.payment_method,
                        vendor_name: data.vendor_name,
                        vendor_address: data.vendor_address,
                        customer_address: data.customer_address
                    });
                } else if (data.status === 'cancelled') {
                    showCustomModal('Pedido Cancelado', `El pedido #${data.order_id} ha sido cancelado.`, false);
                    playAlertSound();
                }
                
                fetchOrders();
            })
            .listen('.order-unassigned-from-rider', (data) => {
                console.log('Order unassigned received:', data);
                showCustomModal('Pedido Quitado', `El pedido #${data.order_id} ya no está asignado a ti.`, false);
                playAlertSound();
                fetchOrders();
            });

    } else {
        isConnected = false;
        localStorage.setItem('delivery_is_connected', 'false');
        
        // Unsubscribe from channel
        const userId = window.BariDeliveryConfig.userId;
        window.Echo.leave(`App.Models.User.${userId}`);
        
        topStatusDot.className = 'dot';
        topStatusText.textContent = 'Desconectado';
        infoTitle.textContent = 'Estás desconectado';
        infoDesc.textContent = 'Presiona Comenzar para recibir notificaciones de nuevos pedidos.';
        
        disconnectedActions.style.display = 'flex';
        connectedActions.style.display = 'none';
    }
}

window.queuedDisconnect = false;

window.updateHelpCenterUI = function() {
    const btnCancel = document.getElementById('btnCancelOrder');
    const noOptions = document.getElementById('noCancelOptionsMsg');

    if (currentActiveOrderData && (currentActiveOrderData.status === 'pending' || currentActiveOrderData.status === 'assigned')) {
        if (btnCancel) btnCancel.style.display = 'flex';
        if (noOptions) noOptions.style.display = 'none';
    } else {
        if (btnCancel) btnCancel.style.display = 'none';
        if (noOptions) noOptions.style.display = 'block';
    }
};

window.promptCancelOrder = async function() {
    if (!currentActiveOrderData) return;
    
    const confirmed = await showCustomModal('Cancelar Pedido', '¿Estás seguro que deseas liberar/cancelar este pedido? Volverá a la bolsa de pedidos para otros repartidores.');
    if (!confirmed) return;

    try {
        const response = await fetch(`/repartidor/pedidos/${currentActiveOrderData.id}/rechazar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!response.ok) throw new Error('Error al cancelar pedido');
        const data = await response.json();
        
        if (data.success) {
            closeView('helpCenterIssuesView');
            closeView('helpCenterView');
            await showCustomModal('Pedido Cancelado', 'Has liberado el pedido exitosamente.', false);
            fetchOrders();
        } else {
            await showCustomModal('Atención', data.error || 'No se pudo cancelar el pedido.', false);
        }
    } catch (error) {
        console.error(error);
        await showCustomModal('Error', 'Ocurrió un error al intentar cancelar el pedido.', false);
    }
};

window.requestDisconnect = async function() {
    if (currentActiveOrderData) {
        if (currentActiveOrderData.status === 'pending' || currentActiveOrderData.status === 'assigned') {
            await showCustomModal('No puedes pausar', 'Tienes un pedido pendiente de retiro. Por favor, libera el pedido o solicita soporte para cancelarlo antes de desconectarte.', false);
            return;
        } else if (currentActiveOrderData.status === 'processing' || currentActiveOrderData.status === 'shipped') {
            const confirmed = await showCustomModal('Pausa Programada', 'Tienes un pedido en curso. Tu cuenta pasará a "Pausa" automáticamente una vez que entregues este pedido. ¿Deseas programar la pausa?');
            if (confirmed) {
                window.queuedDisconnect = true;
                fetchOrders();
            }
            return;
        }
    }
    toggleConnection(false);
};

// --- Event Listeners ---
if (startBtn) startBtn.addEventListener('click', () => { window.queuedDisconnect = false; toggleConnection(true); });
if (stopBtn) stopBtn.addEventListener('click', window.requestDisconnect);

// --- GPS Tracking: enviar ubicación del rider cada 10 segundos ---
let gpsTrackingInterval = null;
const locationUpdateUrl = window.BariDeliveryConfig.routes.locationUpdate;

function startGpsTracking() {
    if (gpsTrackingInterval) return; // ya corriendo
    if (!('geolocation' in navigator)) {
        console.warn('Geolocation no disponible');
        return;
    }

    gpsTrackingInterval = setInterval(() => {
        if (!isConnected) return;
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                // Actualizar marcador del rider en el mapa local
                riderMarker.setLatLng([lat, lng]);

                // Actualizar estado del botón de retiro si está activo
                if (currentActiveOrderData && currentActiveOrderData.vendor_latitude && currentActiveOrderData.vendor_longitude) {
                    const btn = document.getElementById('btn-mark-picked-up');
                    if (btn) {
                        const dist = calculateDistance(lat, lng, currentActiveOrderData.vendor_latitude, currentActiveOrderData.vendor_longitude);
                        if (dist !== null && dist <= 0.15) {
                            btn.disabled = false;
                            btn.style.background = '#f59e0b';
                            btn.style.cursor = 'pointer';
                            btn.innerHTML = '🛍️ Confirmar Retiro del Local';
                        } else if (dist !== null && dist > 0.15) {
                            btn.disabled = true;
                            btn.style.background = '#94a3b8';
                            btn.style.cursor = 'not-allowed';
                            btn.innerHTML = '📍 Acércate al local para retirar';
                        }
                    }
                }

                // Enviar al servidor
                fetch(locationUpdateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({ latitude: lat, longitude: lng })
                }).catch(err => console.warn('Error enviando ubicación:', err));
            },
            (error) => {
                console.warn('Error obteniendo GPS:', error.message);
            },
            { enableHighAccuracy: true, timeout: 8000, maximumAge: 5000 }
        );
    }, 10000); // cada 10 segundos

    // Enviar inmediatamente la primera vez
    navigator.geolocation.getCurrentPosition(
        (position) => {
            fetch(locationUpdateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({ latitude: position.coords.latitude, longitude: position.coords.longitude })
            }).catch(err => console.warn('Error enviando ubicación inicial:', err));
        },
        () => {},
        { enableHighAccuracy: true }
    );
}

function stopGpsTracking() {
    if (gpsTrackingInterval) {
        clearInterval(gpsTrackingInterval);
        gpsTrackingInterval = null;
    }
}

function recenterMap() {
    if (riderMarker) {
        map.setView(riderMarker.getLatLng(), 15, { animate: true, duration: 0.5 });
    } else {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                map.setView([position.coords.latitude, position.coords.longitude], 15, { animate: true });
            },
            (error) => {
                console.warn('Error centrando el mapa:', error.message);
            },
            { enableHighAccuracy: true }
        );
    }
}

// Heartbeat y estado online del repartidor
let heartbeatInterval = null;
const statusUpdateUrl = window.BariDeliveryConfig.routes.statusUpdate;

function sendOnlineStatus(online) {
    fetch(statusUpdateUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify({ is_online: online })
    }).catch(err => console.warn('Error enviando estado:', err));
}

function startHeartbeat() {
    if (heartbeatInterval) clearInterval(heartbeatInterval);
    sendOnlineStatus(true);
    heartbeatInterval = setInterval(() => {
        if (isConnected) sendOnlineStatus(true);
    }, 30000); // cada 30 segundos
}

function stopHeartbeat() {
    if (heartbeatInterval) {
        clearInterval(heartbeatInterval);
        heartbeatInterval = null;
    }
    sendOnlineStatus(false);
}

// Iniciar/detener GPS tracking y heartbeat según conexión
const originalToggle = toggleConnection;
toggleConnection = async function(connect) {
    await originalToggle(connect);
    if (connect) {
        startGpsTracking();
        startHeartbeat();
    } else {
        stopGpsTracking();
        stopHeartbeat();
    }
};

// Reconectar y avisar al servidor instantáneamente al desbloquear el celular o volver a la pestaña
document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible' && isConnected) {
        sendOnlineStatus(true);
        startGpsTracking();
        if (!heartbeatInterval) startHeartbeat();
    }
});

// Drag/Collapse Logic for Bottom Sheet
(function() {
    const sheet = document.querySelector('.bottom-sheet');
    const handle = document.querySelector('.sheet-handle');
    if (!sheet || !handle) return;

    let isDragging = false;
    let startY = 0;
    let startTranslateY = 0;

    function getTranslateY() {
        const style = window.getComputedStyle(sheet);
        const matrix = new WebKitCSSMatrix(style.transform);
        return matrix.m42 || 0;
    }

    handle.addEventListener('pointerdown', (e) => {
        isDragging = true;
        startY = e.clientY;
        startTranslateY = getTranslateY();
        sheet.style.transition = 'none';
        handle.setPointerCapture(e.pointerId);
    });

    handle.addEventListener('pointermove', (e) => {
        if (!isDragging) return;
        const deltaY = e.clientY - startY;
        let newTranslateY = startTranslateY + deltaY;

        const maxTranslateY = sheet.offsetHeight - 60;
        newTranslateY = Math.max(0, Math.min(newTranslateY, maxTranslateY));

        sheet.style.transform = `translateY(${newTranslateY}px)`;
    });

    handle.addEventListener('pointerup', (e) => {
        if (!isDragging) return;
        isDragging = false;
        handle.releasePointerCapture(e.pointerId);
        
        sheet.style.transition = 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        
        const finalTranslateY = getTranslateY();
        const maxTranslateY = sheet.offsetHeight - 60;
        
        if (finalTranslateY > maxTranslateY * 0.4) {
            sheet.classList.add('collapsed');
            sheet.style.transform = `translateY(${maxTranslateY}px)`;
        } else {
            sheet.classList.remove('collapsed');
            sheet.style.transform = 'translateY(0)';
        }
    });

    handle.addEventListener('click', (e) => {
        if (Math.abs(e.clientY - startY) > 5) return;
        
        sheet.style.transition = 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        const maxTranslateY = sheet.offsetHeight - 60;
        
        if (sheet.classList.contains('collapsed')) {
            sheet.classList.remove('collapsed');
            sheet.style.transform = 'translateY(0)';
        } else {
            sheet.classList.add('collapsed');
            sheet.style.transform = `translateY(${maxTranslateY}px)`;
        }
    });
})();

// --- Support Chat ---
const supportMessagesUrl = window.BariDeliveryConfig.routes.supportMessages;
const sendSupportMessageUrl = window.BariDeliveryConfig.routes.supportSend;

async function openSupportChat() {
    isSupportViewActive = true;
    document.getElementById('supportBadge').style.display = 'none';
    document.getElementById('supportDrawerBadge').style.display = 'none';
    // Removemos await para que la UI no se trabe al abrir la vista
    fetchSupportMessages();
}

async function fetchSupportMessages() {
    try {
        const response = await fetch(supportMessagesUrl, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        if (!response.ok) throw new Error('Network error');
        const messages = await response.json();
        renderSupportMessages(messages);
    } catch (error) {
        console.error('Error fetching support messages:', error);
    }
}

function renderSupportMessages(messages) {
    const container = document.getElementById('supportChatMessages');
    if (!container) return;
    container.innerHTML = '';

    if (messages.length === 0) {
        container.innerHTML = `
            <div style="text-align: center; color: var(--text-muted); font-size: 0.9rem; margin-top: 40px; padding: 0 20px;">
                <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 12px auto; opacity: 0.5;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                <p style="margin: 0 0 4px 0; font-weight: 500;">Soporte de Bari Tienda</p>
                <p style="margin: 0; font-size: 0.8rem;">Escribe tu consulta y un administrador te responderá a la brevedad.</p>
            </div>
        `;
        return;
    }

    const currentUserId = window.BariDeliveryConfig.userId;
    messages.forEach(msg => {
        const isMe = msg.sender_id === currentUserId;
        const bubble = document.createElement('div');
        bubble.style.display = 'flex';
        bubble.style.justifyContent = isMe ? 'flex-end' : 'flex-start';
        
        const time = msg.created_at ? new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';
        
        bubble.innerHTML = `
            <div style="max-w: 75%; background: ${isMe ? 'var(--primary)' : 'white'}; color: ${isMe ? 'white' : 'var(--text-main)'}; padding: 10px 14px; border-radius: 16px; border-bottom-${isMe ? 'right' : 'left'}-radius: 4px; box-shadow: 0 2px 6px rgba(0,0,0,0.05); border: ${isMe ? 'none' : '1px solid #f0f0f0'};">
                <p style="margin: 0; font-size: 0.95rem; line-height: 1.4; word-break: break-word;">${escapeHtml(msg.message)}</p>
                <span style="display: block; font-size: 0.75rem; text-align: right; opacity: 0.7; margin-top: 4px;">${time}</span>
            </div>
        `;
        container.appendChild(bubble);
    });

    container.scrollTop = container.scrollHeight;
}

function escapeHtml(text) {
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

async function sendSupportChat() {
    const input = document.getElementById('supportChatInput');
    if (!input) return;
    const message = input.value.trim();
    if (!message) return;

    input.value = '';

    try {
        const response = await fetch(sendSupportMessageUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ message })
        });

        if (!response.ok) throw new Error('Send message failed');
        
        // Fetch and re-render
        await fetchSupportMessages();
    } catch (error) {
        console.error('Error sending message:', error);
        alert('No se pudo enviar el mensaje.');
    }
}

// Init
updateInstallUI();

if (localStorage.getItem('delivery_is_connected') === 'true') {
    toggleConnection(true);
}

// Register SW
if ('serviceWorker' in navigator && window.BariDeliveryConfig.assets.serviceWorker) {
    navigator.serviceWorker.register(window.BariDeliveryConfig.assets.serviceWorker).catch(err => console.warn(err));
}
