<script>window.disableAlpineStart = true;</script>
@vite(['resources/js/app.js'])

<x-filament-panels::page>
    <livewire:vendor-orders-table />
</x-filament-panels::page>

<script>
(function() {
    'use strict';

    let notificationOrders = []; // Pedidos activos en la campanita
    let menuOpen = false;

    // ─── Campanita de notificaciones ──────────────────────────
    function createBellIndicator() {
        const existing = document.getElementById('vendor-bell-indicator');
        if (existing) return; // No recrear si ya existe

        const indicator = document.createElement('div');
        indicator.id = 'vendor-bell-indicator';
        indicator.style.cssText = `
            position: fixed;
            top: 12px;
            right: 80px;
            width: 40px;
            height: 40px;
            background-color: #6366f1;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 50;
            font-size: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        `;
        indicator.innerHTML = '🔔';
        indicator.title = 'Notificaciones de pedidos';
        indicator.addEventListener('click', toggleNotificationMenu);

        const badge = document.createElement('span');
        badge.id = 'vendor-notification-badge';
        badge.style.cssText = `
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #ef4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: bold;
        `;
        badge.textContent = '0';
        indicator.appendChild(badge);
        document.body.appendChild(indicator);
    }

    function updateBadge() {
        const badge = document.getElementById('vendor-notification-badge');
        if (!badge) return;
        const count = notificationOrders.length;
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }

    function toggleNotificationMenu() {
        menuOpen = !menuOpen;
        if (menuOpen) {
            showNotificationMenu();
        } else {
            closeNotificationMenu();
        }
    }

    function showNotificationMenu() {
        document.getElementById('vendor-notification-menu')?.remove();

        const menu = document.createElement('div');
        menu.id = 'vendor-notification-menu';
        menu.style.cssText = `
            position: fixed;
            top: 60px;
            right: 20px;
            width: 380px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 51;
            max-height: 400px;
            overflow-y: auto;
        `;

        menu.innerHTML = `
            <div style="background:#f3f4f6;padding:12px 16px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;font-weight:600;font-size:14px;">
                <span>Pedidos Asignados (${notificationOrders.length})</span>
                <button onclick="closeMenuBtn()" style="background:none;border:none;cursor:pointer;font-size:18px;color:#6b7280;">×</button>
            </div>
        `;

        if (notificationOrders.length > 0) {
            notificationOrders.forEach(order => {
                const item = document.createElement('div');
                item.style.cssText = 'padding:12px 16px;border-bottom:1px solid #f3f4f6;';
                item.innerHTML = `
                    <div style="font-weight:600;color:#1f2937;">Pedido #${order.order_id}</div>
                    <div style="color:#6b7280;font-size:12px;margin-top:2px;">${order.customer_name ?? ''}</div>
                    <div style="color:#10b981;font-weight:600;font-size:13px;margin-top:4px;">$${parseFloat(order.total).toFixed(2)} ARS</div>
                `;
                menu.appendChild(item);
            });
        } else {
            menu.insertAdjacentHTML('beforeend', `
                <div style="padding:32px 16px;text-align:center;color:#9ca3af;font-size:13px;">📭 Sin pedidos asignados</div>
            `);
        }

        document.body.appendChild(menu);

        setTimeout(() => {
            document.addEventListener('click', closeOnClickOutside);
        }, 100);
    }

    function closeNotificationMenu() {
        document.getElementById('vendor-notification-menu')?.remove();
    }

    window.closeMenuBtn = function() {
        menuOpen = false;
        closeNotificationMenu();
        document.removeEventListener('click', closeOnClickOutside);
    };

    function closeOnClickOutside(e) {
        const menu = document.getElementById('vendor-notification-menu');
        const bell = document.getElementById('vendor-bell-indicator');
        if (!menu || !bell) return;
        if (!menu.contains(e.target) && !bell.contains(e.target)) {
            menuOpen = false;
            closeNotificationMenu();
            document.removeEventListener('click', closeOnClickOutside);
        }
    }

    // ─── Recibir notificación desde WebSocket ─────────────────
    // El componente VendorOrdersTable dispara este evento via Echo
    // directamente desde vendor-orders-table.blade.php
    // Aquí solo necesitamos sincronizar la campanita con el evento global
    window.addEventListener('vendor-new-order-assigned', (e) => {
        const order = e.detail;
        const exists = notificationOrders.some(o => o.order_id === order.order_id);
        if (!exists) {
            notificationOrders.push(order);
            updateBadge();
            if (menuOpen) {
                closeNotificationMenu();
                showNotificationMenu();
            }
        }
    });

    // ─── Inicialización ────────────────────────────────────────
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', createBellIndicator);
    } else {
        createBellIndicator();
    }
})();
</script>
