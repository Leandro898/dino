<div>
<div class="space-y-4">

    {{-- Audio para notificaciones --}}
    <audio id="vendorOrderSound" preload="auto" style="display:none;">
        <source src="{{ asset('sounds/order.mp3') }}" type="audio/mpeg">
    </audio>

    {{-- Banner de activación de sonido --}}
    <div id="vendorSoundBanner" wire:ignore class="mb-4 flex items-center justify-between gap-3 rounded-lg bg-indigo-50 px-4 py-3 text-sm ring-1 ring-indigo-200 dark:bg-indigo-950 dark:ring-indigo-900">
        <div class="flex items-center gap-2">
            <span class="text-xl">🔔</span>
            <div>
                <p class="font-semibold text-indigo-900 dark:text-indigo-100">Activar alertas de nuevos pedidos</p>
                <p class="text-xs text-indigo-700 dark:text-indigo-300">Hacé click aquí para recibir alertas sonoras cuando el admin te asigne un pedido</p>
            </div>
        </div>
        <button id="vendorActivateSoundBtn"
                onclick="vendorActivateSound()"
                class="shrink-0 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-95 transition-all">
            🔊 Activar Sonido
        </button>
    </div>

    @if($orders->count())
        {{-- Tabla de pedidos --}}
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/50">
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-950 dark:text-white">Nro de Pedido</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-950 dark:text-white">Total</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-950 dark:text-white">Cliente</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-950 dark:text-white">Productos</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-950 dark:text-white">Estado</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-950 dark:text-white">Fecha</th>
                            <th class="px-6 py-3 text-center text-sm font-semibold text-gray-950 dark:text-white">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700" id="vendorOrdersTableBody">
                        @foreach($orders as $order)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition" data-order-id="{{ $order->id }}">
                                <td class="px-6 py-4 font-medium text-gray-950 dark:text-white">#{{ $order->id }}</td>
                                <td class="px-6 py-4 font-medium text-green-600 dark:text-green-400">{{ number_format($order->total, 2) }} ARS</td>
                                <td class="px-6 py-4">
                                    <div class="text-gray-900 dark:text-white font-medium">{{ $order->user?->name ?? $order->name ?? '-' }}</div>
                                    @if($order->deliveryRider)
                                        <div class="text-xs text-indigo-600 dark:text-indigo-400 mt-1 flex items-center gap-1 font-semibold" title="Repartidor asignado">
                                            <span>🛵</span> {{ $order->deliveryRider->name }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-2">
                                        @forelse($order->items as $item)
                                            <div class="text-sm text-gray-700 dark:text-gray-300 border-l-2 border-indigo-500 pl-2">
                                                <span class="font-medium">{{ $item->product?->name ?? 'Producto desconocido' }}</span>
                                                <span class="text-gray-500 dark:text-gray-400"> x{{ $item->quantity }}</span>
                                            </div>
                                        @empty
                                            @if($order->order_details || $order->beverage_details)
                                                @if($order->order_details)
                                                    <div class="text-sm text-gray-700 dark:text-gray-300 border-l-2 border-indigo-500 pl-2">
                                                        <span class="font-medium">{{ $order->order_details }}</span>
                                                    </div>
                                                @endif
                                                @if($order->beverage_details)
                                                    <div class="text-sm text-gray-700 dark:text-gray-300 border-l-2 border-indigo-500 pl-2 mt-2">
                                                        <span class="font-bold text-xs uppercase text-gray-500 block mb-0.5">Bebidas:</span>
                                                        <span class="font-medium">{{ $order->beverage_details }}</span>
                                                    </div>
                                                @endif
                                            @else
                                                <span class="text-sm text-gray-500 dark:text-gray-400">Sin productos</span>
                                            @endif
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <select wire:change="updateOrderStatus({{ $order->id }}, $event.target.value)"
                                            class="px-3 py-1 text-xs font-medium rounded-md border transition-colors cursor-pointer bg-white text-gray-900 dark:bg-gray-800 dark:text-white">
                                        @if($order->status === 'assigned')
                                            <option value="assigned" selected disabled>Asignado</option>
                                        @endif
                                        <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>En preparación</option>
                                        <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Listo para retirar/enviado</option>
                                    </select>
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-6 py-4 text-center">
                                    <button onclick="printOrder({{ $order->id }})" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition active:scale-95">
                                        🖨️ Imprimir
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{-- Paginación --}}
            <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                {{ $orders->links() }}
            </div>
        </div>
    @else
        <div id="noOrdersPlaceholder" class="rounded-lg border border-gray-200 bg-white p-12 text-center shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <p class="text-sm text-gray-500 dark:text-gray-400">No tienes pedidos asignados aún</p>
        </div>
    @endif


<script>
(function() {
    'use strict';

    // ─── Estado ────────────────────────────────────────────────
    let vendorSoundEnabled = false;
    let wsInitialized = false;
    const VENDOR_USER_ID = {{ auth()->id() ?? 'null' }};

    window.printOrder = function(orderId) {
        const width = 450;
        const height = 650;
        const left = (screen.width - width) / 2;
        const top = (screen.height - height) / 2;
        const printWindow = window.open(`/orders/${orderId}/print`, `print_order_${orderId}`, `width=${width},height=${height},left=${left},top=${top},status=no,toolbar=no,menubar=no,location=no`);
        if (printWindow) {
            printWindow.focus();
        }
    };

    // ─── Auto-ocultar banner y desbloquear en 1er click ────────
    if (localStorage.getItem('vendorSoundAcknowledged') === 'true') {
        const unlockAudio = function() {
            const audio = document.getElementById('vendorOrderSound');
            if (audio && !vendorSoundEnabled) {
                audio.volume = 0;
                audio.play().then(() => {
                    audio.pause();
                    audio.currentTime = 0;
                    audio.volume = 1.0;
                    vendorSoundEnabled = true;
                }).catch(e => {});
            }
            document.removeEventListener('click', unlockAudio);
        };
        document.addEventListener('click', unlockAudio);
        
        // Ocultar banner si el DOM ya está listo o cuando lo esté
        document.addEventListener('DOMContentLoaded', () => {
            const banner = document.getElementById('vendorSoundBanner');
            if (banner) banner.style.display = 'none';
        });
    }

    // ─── Activación de sonido (requiere interacción del usuario) ─
    window.vendorActivateSound = function() {
        const audio = document.getElementById('vendorOrderSound');
        if (!audio) return;

        audio.volume = 0;
        audio.play()
            .then(() => {
                audio.pause();
                audio.currentTime = 0;
                audio.volume = 1.0;
                vendorSoundEnabled = true;
                localStorage.setItem('vendorSoundAcknowledged', 'true');

                const btn = document.getElementById('vendorActivateSoundBtn');
                const banner = document.getElementById('vendorSoundBanner');
                if (btn) {
                    btn.textContent = '✅ Sonido activado';
                    btn.disabled = true;
                    btn.className = 'shrink-0 rounded-md bg-gray-400 px-4 py-2 text-sm font-semibold text-white cursor-default';
                }
                if (banner) setTimeout(() => { banner.style.display = 'none'; }, 2000);

                /* console.log */('%c✅ [Vendor] Sonido activado', 'color: green; font-weight: bold');
            })
            .catch(err => {
                console.warn('%c⚠️ [Vendor] No se pudo activar sonido:', 'color: orange', err.message);
            });
    };

    function playVendorSound() {
        const audio = document.getElementById('vendorOrderSound');
        if (!audio) return;
        if (!vendorSoundEnabled) {
            // Parpadear el banner si no está activado
            const banner = document.getElementById('vendorSoundBanner');
            if (banner) {
                let count = 0;
                const blink = setInterval(() => {
                    banner.style.opacity = banner.style.opacity === '0.3' ? '1' : '0.3';
                    if (++count >= 6) { clearInterval(blink); banner.style.opacity = '1'; }
                }, 200);
            }
            return;
        }
        audio.currentTime = 0;
        audio.volume = 1.0;
        audio.play()
            .then(() => /* console.log */('%c🔊 [Vendor] Sonido reproducido', 'color: green'))
            .catch(err => console.error('%c❌ [Vendor] Error de sonido:', 'color: red', err.message));
    }

    // ─── Toast visual ──────────────────────────────────────────
    function showVendorToast(order) {
        document.getElementById('vendorOrderToast')?.remove();
        const toast = document.createElement('div');
        toast.id = 'vendorOrderToast';
        toast.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#4f46e5;color:white;padding:16px 20px;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,0.2);z-index:9999;animation:slideInRight 0.3s ease-out;max-width:320px;';
        toast.innerHTML = `
            <div style="font-weight:700;font-size:15px;margin-bottom:6px;">📦 Nuevo Pedido Asignado</div>
            <div style="font-size:13px;opacity:0.9;">Pedido #${order.order_id}</div>
            <div style="font-size:13px;opacity:0.9;">Cliente: ${order.customer_name ?? '-'}</div>
            <div style="font-size:14px;font-weight:600;margin-top:4px;">$${parseFloat(order.total).toFixed(2)} ARS</div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.transition = 'opacity 0.5s, transform 0.5s';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(110%)';
            setTimeout(() => toast.remove(), 500);
        }, 8000);
    }

    // ─── Agregar fila a la tabla ───────────────────────────────
    function addOrderRowToTable(order) {
        // Ocultar placeholder si existe
        const placeholder = document.getElementById('noOrdersPlaceholder');
        if (placeholder) placeholder.style.display = 'none';

        const tbody = document.getElementById('vendorOrdersTableBody');
        if (!tbody) {
            // Si no existe la tabla, recargar el componente Livewire
            /* console.log */('%c🔄 [Vendor] Tabla no existe, recargando componente Livewire', 'color: blue');
            Livewire.dispatch('order-updated');
            return;
        }

        // Evitar duplicados
        if (tbody.querySelector(`tr[data-order-id="${order.order_id}"]`)) {
            /* console.log */('%c⚠️ [Vendor] Fila duplicada, ignorando #' + order.order_id, 'color: orange');
            return;
        }

        const now = new Date().toLocaleString('es-AR', {day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit'});
        const row = document.createElement('tr');
        row.setAttribute('data-order-id', order.order_id);
        row.style.cssText = 'animation: newRowHighlight 3s ease forwards;';
        row.innerHTML = `
            <td class="px-6 py-4 font-medium text-gray-950 dark:text-white">#${order.order_id}</td>
            <td class="px-6 py-4 font-medium text-green-600 dark:text-green-400">${parseFloat(order.total).toFixed(2)} ARS</td>
            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">${order.customer_name ?? '-'}</td>
            <td class="px-6 py-4"><span class="text-sm text-gray-500">Ver detalle</span></td>
            <td class="px-6 py-4">
                <select wire:change="updateOrderStatus(${order.order_id}, $event.target.value)"
                        class="px-3 py-1 text-xs font-medium rounded-md border transition-colors cursor-pointer bg-white text-gray-900 dark:bg-gray-800 dark:text-white">
                    <option value="assigned" selected disabled>Asignado</option>
                    <option value="processing">En preparación</option>
                    <option value="shipped">Listo para retirar/enviado</option>
                </select>
            </td>
            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">${now}</td>
            <td class="px-6 py-4 text-center">
                <button onclick="printOrder(${order.order_id})" 
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition active:scale-95">
                    🖨️ Imprimir
                </button>
            </td>
        `;
        tbody.insertAdjacentElement('afterbegin', row);
        /* console.log */('%c✅ [Vendor] Fila #' + order.order_id + ' agregada a la tabla', 'color: green');
    }

    // ─── Notificación del navegador ───────────────────────────
    function showBrowserNotification(order) {
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification('📦 Nuevo Pedido Asignado', {
                body: `Pedido #${order.order_id} - $${parseFloat(order.total).toFixed(2)} ARS`,
                tag: `vendor-order-${order.order_id}`,
            });
        }
    }

    // ─── Campanita: notificar a la página padre ───────────────
    function notifyBell(order) {
        window.dispatchEvent(new CustomEvent('vendor-new-order-assigned', { detail: order }));
    }

    // ─── Inicializar WebSocket via Pusher ───────────────────────
    function initVendorPusher(userId) {
        if (wsInitialized) return;

        if (!window.Pusher) {
            setTimeout(() => initVendorPusher(userId), 300);
            return;
        }

        const channelName = `private-vendor.${userId}`;
        /* console.log */(`%c📡 [Vendor] Conectando al canal privado: ${channelName}`, 'color: blue; font-weight: bold');

        try {
            const pusher = new window.Pusher('{{ config('broadcasting.connections.reverb.key') }}', {
                wsHost: '{{ config('broadcasting.connections.reverb.options.host') }}',
                wsPort: {{ config('broadcasting.connections.reverb.options.port', 8080) }},
                wssPort: {{ config('broadcasting.connections.reverb.options.port', 8080) }},
                forceTLS: false,
                enabledTransports: ['ws'],
                cluster: 'mt1',
                authEndpoint: '/broadcasting/auth',
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                }
            });

            pusher.connection.bind('connected', () => {
                /* console.log */('%c✅ [Vendor] Conectado a Reverb via Pusher', 'color: green; font-weight: bold');
            });

            pusher.connection.bind('disconnected', () => {
                /* console.log */('%c❌ [Vendor] Desconectado de Reverb', 'color: red; font-weight: bold');
            });

            const channel = pusher.subscribe(channelName);

            channel.bind('pusher:subscription_succeeded', () => {
                /* console.log */(`%c✅ [Vendor] Suscripción al canal ${channelName} exitosa`, 'color: green; font-weight: bold');
            });

            channel.bind('pusher:subscription_error', (status) => {
                console.error(`%c❌ [Vendor] Error en suscripción al canal ${channelName}:`, 'color: red; font-weight: bold', status);
            });

            channel.bind('order-status-updated', (data) => {
                /* console.log */('%c🎉 [Vendor] EVENTO RECIBIDO: order-status-updated', 'color: green; font-size: 14px; font-weight: bold', data);

                if (data.new_status === 'assigned') {
                    /* console.log */('%c📦 [Vendor] Estado = assigned → disparando notificaciones', 'color: green');
                    playVendorSound();
                    showVendorToast(data);
                    showBrowserNotification(data);
                    
                    const existingRow = document.querySelector(`tr[data-order-id="${data.order_id}"]`);
                    if (existingRow) {
                        const statusSelect = existingRow.querySelector('select');
                        if (statusSelect) {
                            statusSelect.value = data.new_status;
                        }
                        existingRow.style.backgroundColor = '#fef3c7';
                        setTimeout(() => { existingRow.style.backgroundColor = ''; }, 3000);
                    } else {
                        addOrderRowToTable(data);
                    }
                    
                    notifyBell(data);
                } else {
                    /* console.log */(`%c🔄 [Vendor] Estado cambiado a: ${data.new_status}`, 'color: purple');
                    const row = document.querySelector(`tr[data-order-id="${data.order_id}"]`);
                    if (row) {
                        if (!['assigned', 'processing', 'completed'].includes(data.new_status)) {
                            row.remove();
                            const tbody = document.getElementById('vendorOrdersTableBody');
                            if (tbody && tbody.children.length === 0) {
                                Livewire.dispatch('order-updated');
                            }
                        } else {
                            const statusSelect = row.querySelector('select');
                            if (statusSelect) {
                                statusSelect.value = data.new_status;
                            }
                            row.style.backgroundColor = '#fef3c7';
                            setTimeout(() => { row.style.backgroundColor = ''; }, 3000);
                        }
                    }
                }
            });

            wsInitialized = true;
        } catch (e) {
            console.error('%c❌ [Vendor] Excepción al suscribirse:', 'color: red', e);
        }
    }

    // ─── Solicitar permisos de notificación ───────────────────
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    // ─── Inicialización robusta: múltiples puntos de entrada ──
    // para asegurar que se ejecuta sin importar el orden de carga
    if (!VENDOR_USER_ID) {
        console.error('%c❌ [Vendor] No se pudo obtener userId - verificar auth()', 'color: red');
    } else {
        // 1. Intentar inmediatamente (si Pusher ya está listo)
        initVendorPusher(VENDOR_USER_ID);

        // 2. También cuando Livewire esté completamente inicializado
        document.addEventListener('livewire:initialized', () => {
            initVendorPusher(VENDOR_USER_ID);
        });

        // 3. Fallback: intentar a los 1.5s para cubrir casos edge
        setTimeout(() => initVendorPusher(VENDOR_USER_ID), 1500);
    }
})();
</script>
</div>
</div>
