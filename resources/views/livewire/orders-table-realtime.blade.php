<div>
    <!-- Audio para notificaciones -->
    <audio id="newOrderSound" preload="auto" style="display:none;">
        <source src="{{ asset('sounds/admin.mp3') }}" type="audio/mpeg">
    </audio>
    <audio id="vendorOrderSound" preload="auto" style="display:none;">
        <source src="{{ asset('sounds/order.mp3') }}" type="audio/mpeg">
    </audio>

    <!-- Banner de activación de sonido -->
    <div id="adminSoundBanner" wire:ignore class="mb-4 flex items-center justify-between gap-3 rounded-lg bg-green-50 px-4 py-3 text-sm ring-1 ring-green-200 dark:bg-green-950 dark:ring-green-900">
        <div class="flex items-center gap-2">
            <span class="text-xl">🔔</span>
            <div>
                <p class="font-semibold text-green-900 dark:text-green-100">Activar notificaciones de sonido</p>
                <p class="text-xs text-green-700 dark:text-green-300">Hacé click aquí para recibir alertas sonoras cuando llegue un nuevo pedido</p>
            </div>
        </div>
        <button id="activateSoundBtn"
                onclick="adminActivateSound()"
                class="shrink-0 rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-700 active:scale-95 transition-all">
            🔊 Activar Sonido
        </button>
    </div>

    <!-- Estilos para animaciones -->
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes highlightFlash {
            0% {
                background-color: #fff3cd;
            }
            100% {
                background-color: transparent;
            }
        }
    </style>

    <div class="space-y-4">
        <!-- Filtros -->
        <div class="flex flex-col gap-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="flex flex-col gap-3 sm:flex-row">
                <input 
                    wire:model.live="search"
                    type="text" 
                    placeholder="Buscar por ID, nombre o email..." 
                    class="flex-1 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-500 shadow-sm transition focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                
                <select 
                    wire:model.live="filterStatus"
                    class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    <option value="">Todos los estados</option>
                    <option value="pending">Pendiente</option>
                    <option value="assigned">Asignado</option>
                    <option value="processing">En preparación</option>
                    <option value="paid_confirmed">Pago confirmado</option>
                    <option value="completed">Completado</option>
                    <option value="shipped">Enviado</option>
                    <option value="cancelled">Cancelado</option>
                </select>
            </div>
        </div>

        <!-- Botón de eliminación en masa -->
        <div class="flex justify-end">
            <button id="bulkDeleteBtn" style="display: none;" onclick="handleBulkDelete()"
                    class="inline-flex items-center gap-2 rounded-md bg-red-500 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-600">
                <span>🗑️</span>
                <span id="bulkDeleteText">Eliminar seleccionadas</span>
            </button>
        </div>

        @if($orders->count())
            <!-- Tabla de órdenes -->
            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm dark:border-gray-700">
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                <input type="checkbox" id="selectAllCheckbox" class="rounded border-gray-300">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Método</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900" id="ordersTableBody">
                        @forelse($orders as $order)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition" data-order-id="{{ $order->id }}">
                                <td class="px-6 py-4">
                                    <input type="checkbox" class="order-checkbox rounded border-gray-300" value="{{ $order->id }}" data-order-id="{{ $order->id }}">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">#{{ $order->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{{ $order->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">${{ number_format($order->total, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($order->payment_method === 'mercadopago')
                                        <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">💳 Mercado Pago</span>
                                    @elseif($order->payment_method === 'transferencia')
                                        <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-300">🏦 Transferencia</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300">💵 {{ ucfirst($order->payment_method) }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <select onchange="updateOrderStatusDirect({{ $order->id }}, this.value)" class="rounded-md border border-gray-300 bg-white px-3 py-1 text-xs text-gray-900 shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                        <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pendiente</option>
                                        <option value="assigned" {{ $order->status === 'assigned' ? 'selected' : '' }}>Asignado</option>
                                        <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>En preparación</option>
                                        <option value="paid_confirmed" {{ $order->status === 'paid_confirmed' ? 'selected' : '' }}>Pago confirmado</option>
                                        <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completado</option>
                                        <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Enviado</option>
                                        <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                                    </select>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $order->created_at->format('H:i:s') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <button onclick="deleteOrderDirect({{ $order->id }})"
                                            class="inline-flex items-center gap-2 rounded-md bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 transition-colors hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50">
                                        <span>🗑️</span>
                                        <span>Eliminar</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">No hay pedidos con los filtros aplicados</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                {{ $orders->links() }}
            </div>
        @else
            <div class="rounded-lg border border-gray-200 bg-white p-12 text-center shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">No hay pedidos con los filtros aplicados</p>
            </div>
        @endif
    </div>

    <script>
        // ─── Estado ───────────────────────────────────────────────
        let receivedOrderIds = new Set();
        let adminSoundEnabled = false;

        // ─── Auto-ocultar banner y desbloquear en 1er click ────────
        if (localStorage.getItem('adminSoundAcknowledged') === 'true') {
            const unlockAudioAdmin = function() {
                const audio = document.getElementById('newOrderSound');
                if (audio && !adminSoundEnabled) {
                    audio.volume = 0;
                    audio.play().then(() => {
                        audio.pause();
                        audio.currentTime = 0;
                        audio.volume = 1.0;
                        adminSoundEnabled = true;
                    }).catch(e => {});
                }
                document.removeEventListener('click', unlockAudioAdmin);
            };
            document.addEventListener('click', unlockAudioAdmin);
            
            // Ocultar banner
            document.addEventListener('DOMContentLoaded', () => {
                const banner = document.getElementById('adminSoundBanner');
                if (banner) banner.style.display = 'none';
            });
        }

        // ─── Activación de sonido ─────────────────────────────────
        // El navegador requiere interacción del usuario antes de reproducir audio.
        // El botón del banner activa el contexto de audio.
        window.adminActivateSound = function() {
            const audio = document.getElementById('newOrderSound');
            if (!audio) return;

            audio.volume = 0;
            audio.play()
                .then(() => {
                    audio.pause();
                    audio.currentTime = 0;
                    audio.volume = 1.0;
                    adminSoundEnabled = true;
                    localStorage.setItem('adminSoundAcknowledged', 'true');

                    // Ocultar el banner y mostrar confirmación
                    const banner = document.getElementById('adminSoundBanner');
                    const btn = document.getElementById('activateSoundBtn');
                    if (btn) {
                        btn.textContent = '✅ Sonido activado';
                        btn.disabled = true;
                        btn.className = 'shrink-0 rounded-md bg-gray-400 px-4 py-2 text-sm font-semibold text-white cursor-default';
                    }
                    if (banner) {
                        setTimeout(() => { banner.style.display = 'none'; }, 2000);
                    }
                    /* console.log */('✅ [Admin] Sonido activado correctamente');
                })
                .catch(err => {
                    console.warn('⚠️ [Admin] No se pudo activar sonido:', err.message);
                });
        };

        // Reproducir sonido de nueva orden
        function playNewOrderSound() {
            const audio = document.getElementById('newOrderSound');
            if (!audio) return;

            audio.currentTime = 0;
            audio.volume = 1.0;
            audio.play()
                .then(() => {
                    adminSoundEnabled = true;
                    /* console.log */('✅ [Admin] Sonido reproducido');
                })
                .catch(err => {
                    console.warn('⚠️ [Admin] Sonido no activado aún - el usuario debe hacer click en el botón');
                    // Hacer parpadear el banner para llamar la atención
                    const banner = document.getElementById('adminSoundBanner');
                    if (banner) {
                        banner.style.display = 'flex';
                        banner.style.transition = 'opacity 0.2s';
                        let count = 0;
                        const blink = setInterval(() => {
                            banner.style.opacity = banner.style.opacity === '0.3' ? '1' : '0.3';
                            if (++count >= 6) { clearInterval(blink); banner.style.opacity = '1'; }
                        }, 200);
                    }
                });
        }

        // 🔊 Función para reproducir sonido de "Listo para retirar"
        function playReadyToPickupSound() {
            /* console.log */('%c📦 Reproduciendo sonido retirar.mp3 (Listo para retirar)...', 'color: green; font-weight: bold; font-size: 14px');
            const audio = new Audio('{{ asset('sounds/retirar.mp3') }}');
            audio.volume = 1.0;
            const playPromise = audio.play();
            if (playPromise !== undefined) {
                playPromise.catch(error => console.error('❌ Error al reproducir sonido:', error.message));
            }
        }

        // 🔊 Función para reproducir sonido de "Asignado"
        function playAssignedSound() {
            if (!adminSoundEnabled) return;
            const audio = document.getElementById('vendorOrderSound');
            if (!audio) return;
            audio.currentTime = 0;
            audio.volume = 1.0;
            audio.play().catch(err => console.error('❌ Error reproduciendo sonido asignado:', err.message));
        }

        // Función para mostrar notificación
        function showOrderNotification(orderId, customerName, total) {
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification('🎉 ¡NUEVA ORDEN!', {
                    body: `#${orderId} - ${customerName} - $${total}`,
                    tag: 'order-' + orderId,
                    requireInteraction: true
                });
            }
        }

        // Solicitar permisos de notificación
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }

        // Función para actualizar visibilidad del botón de eliminación en masa
        function updateBulkDeleteButton() {
            const checkboxes = Array.from(document.querySelectorAll('.order-checkbox'));
            const checkedBoxes = checkboxes.filter(cb => cb.checked);
            const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
            const bulkDeleteText = document.getElementById('bulkDeleteText');
            
            if (checkedBoxes.length > 0) {
                bulkDeleteBtn.style.display = 'inline-flex';
                bulkDeleteText.textContent = `Eliminar ${checkedBoxes.length} seleccionadas`;
            } else {
                bulkDeleteBtn.style.display = 'none';
            }
        }

        // Función para manejar el clic del botón de eliminación en masa
        function handleBulkDelete() {
            const checkboxes = Array.from(document.querySelectorAll('.order-checkbox'));
            const selectedIds = checkboxes
                .filter(cb => cb.checked)
                .map(cb => parseInt(cb.value));
            
            if (selectedIds.length === 0) {
                alert('Por favor selecciona al menos una orden');
                return;
            }
            
            deleteBulkOrders(selectedIds);
        }

        // Monitorear cambios en los checkboxes
        document.addEventListener('change', (e) => {
            // Si es el checkbox de "Seleccionar todos"
            if (e.target.id === 'selectAllCheckbox') {
                const isChecked = e.target.checked;
                const allCheckboxes = document.querySelectorAll('.order-checkbox');
                allCheckboxes.forEach(cb => cb.checked = isChecked);
                updateBulkDeleteButton();
            }
            // Si es un checkbox de orden individual
            else if (e.target.classList.contains('order-checkbox')) {
                updateBulkDeleteButton();
            }
        });

        // Actualizar checkbox de "Seleccionar todos" cuando se deselecciona alguno
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('order-checkbox')) {
                const allCheckboxes = document.querySelectorAll('.order-checkbox');
                const selectAllCheckbox = document.getElementById('selectAllCheckbox');
                const allChecked = Array.from(allCheckboxes).every(cb => cb.checked);
                selectAllCheckbox.checked = allChecked;
            }
        });

        // Conectar a Pusher
        function initWebSocketListener() {
            /* console.log */('%c📡 Conectando a Pusher...', 'color: blue; font-weight: bold');
            
            if (!window.Pusher) {
                console.error('❌ Pusher no disponible');
                return;
            }

            const pusher = new window.Pusher('{{ config('broadcasting.connections.reverb.key') }}', {
                wsHost: '{{ config('broadcasting.connections.reverb.options.host') }}',
                wsPort: {{ config('broadcasting.connections.reverb.options.port', 8080) }},
                wssPort: {{ config('broadcasting.connections.reverb.options.port', 8080) }},
                forceTLS: false,
                enabledTransports: ['ws'],
                cluster: 'mt1'
            });

            // Log de estado de conexión
            pusher.connection.bind('connected', () => {
                /* console.log */('%c✅ Conectado a Reverb', 'color: green; font-weight: bold');
            });

            pusher.connection.bind('disconnected', () => {
                /* console.log */('%c❌ Desconectado de Reverb', 'color: red; font-weight: bold');
            });

            // Suscribirse al canal
            /* console.log */('%c📨 Suscribiéndose al canal "orders"...', 'color: blue; font-weight: bold');
            const channel = pusher.subscribe('orders');

            // Log cuando se suscribe exitosamente
            channel.bind('pusher:subscription_succeeded', () => {
                /* console.log */('%c✅ Suscripción al canal "orders" exitosa', 'color: green; font-weight: bold');
            });

            // Log cuando hay error en la suscripción
            channel.bind('pusher:subscription_error', (err) => {
                console.error('%c❌ Error en suscripción al canal "orders":', 'color: red; font-weight: bold', err);
            });

            channel.bind('new-order-created', (data) => {
                /* console.log */('%c🎉 ¡NUEVA ORDEN RECIBIDA!', 'color: green; font-size: 14px; font-weight: bold', data);
                
                // Evitar duplicados
                if (receivedOrderIds.has(data.order_id)) {
                    /* console.log */('%c⚠️ Orden duplicada, ignorando', 'color: orange; font-weight: bold');
                    return;
                }
                
                receivedOrderIds.add(data.order_id);

                // Reproducir sonido
                playNewOrderSound();

                // Mostrar notificación
                /* console.log */('%c🔔 Mostrando notificación...', 'color: yellow; font-weight: bold');
                showOrderNotification(data.order_id, data.customer_name || data.name, data.total);

                // 🔄 Agregar la fila a la tabla dinámicamente (SIN RECARGAR)
                /* console.log */('%c📤 Agregando fila a la tabla...', 'color: cyan; font-weight: bold');
                addOrderRowToTable(data);
            });

            // 🔄 Escuchar cambios de estado de órdenes (cuando el vendor actualiza)
            channel.bind('order-status-updated', (data) => {
                /* console.log */('%c🔄 ESTADO DE ORDEN ACTUALIZADO!', 'color: purple; font-size: 14px; font-weight: bold', data);
                
                // Actualizar la fila en la tabla
                const row = document.querySelector(`tr[data-order-id="${data.order_id}"]`);
                if (row) {
                    // Encontrar el select de estado y actualizarlo
                    const statusSelect = row.querySelector('select');
                    if (statusSelect) {
                        statusSelect.value = data.new_status;
                        row.style.backgroundColor = '#fffacd'; // Highlight amarillo
                        setTimeout(() => row.style.backgroundColor = '', 2000);
                    }
                }

                // 🔊 Reproducir sonidos según el estado
                if (data.new_status === 'completed' || data.new_status === 'ready_to_pickup' || data.new_status === 'listo_para_retirar') {
                    /* console.log */('%c📦 ¡PEDIDO LISTO PARA RETIRAR!', 'color: green; font-weight: bold');
                    playReadyToPickupSound();
                } else if (data.new_status === 'assigned') {
                    /* console.log */('%c✅ ¡PEDIDO ASIGNADO! Reproduciendo sonido', 'color: green; font-weight: bold');
                    playAssignedSound();
                } else {
                    /* console.log */('%c📝 Estado actualizado:', 'color: blue; font-weight: bold', data.new_status);
                }

                /* console.log */('%c✅ Orden #' + data.order_id + ' actualizada', 'color: green; font-weight: bold');
            });
        }

        // Función para eliminar orden directamente
        function deleteOrderDirect(orderId) {
            if (!confirm('¿Eliminar esta orden?')) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            fetch('/api/custom/orders/' + orderId, {
                method: 'DELETE',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (response.ok) {
                    /* console.log */('%c✅ Orden eliminada correctamente', 'color: green; font-weight: bold');
                    // Remover la fila de la tabla usando el atributo data-order-id
                    const row = document.querySelector(`tr[data-order-id="${orderId}"]`);
                    if (row) {
                        row.style.opacity = '0';
                        row.style.transition = 'opacity 0.3s ease';
                        setTimeout(() => row.remove(), 300);
                    }
                    alert('✅ Orden eliminada correctamente');
                } else {
                    console.error('❌ Error al eliminar:', response.statusText);
                    alert('❌ Error al eliminar la orden: ' + response.statusText);
                }
            })
            .catch(e => {
                console.error('❌ Error:', e);
                alert('❌ Error al eliminar la orden: ' + e.message);
            });
        }

        // Función para agregar una fila a la tabla dinámicamente
        function addOrderRowToTable(orderData) {
            const tbody = document.getElementById('ordersTableBody');
            if (!tbody) {
                console.error('%c❌ No se encontró tbody de la tabla', 'color: red; font-weight: bold');
                return;
            }

            // Función helper para obtener el badge del método de pago
            function getPaymentMethodBadge(method) {
                if (method === 'mercadopago') {
                    return '<span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">💳 Mercado Pago</span>';
                } else if (method === 'transferencia') {
                    return '<span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-300">🏦 Transferencia</span>';
                } else {
                    return `<span class="inline-flex items-center rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300">💵 ${method.charAt(0).toUpperCase() + method.slice(1)}</span>`;
                }
            }

            // Obtener la fecha actual en formato HH:mm:ss
            const now = new Date();
            const timeString = now.toLocaleTimeString('es-ES', { 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit'
            });

            // Crear HTML de la fila
            const rowHTML = `
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition" data-order-id="${orderData.order_id}">
                    <td class="px-6 py-4">
                        <input type="checkbox" value="${orderData.order_id}" class="rounded border-gray-300">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">#${orderData.order_id}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">${orderData.customer_name || orderData.name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">$${parseFloat(orderData.total).toFixed(2)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        ${getPaymentMethodBadge(orderData.payment_method)}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <select onchange="updateOrderStatusDirect(${orderData.order_id}, this.value)" class="rounded-md border border-gray-300 bg-white px-3 py-1 text-xs text-gray-900 shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            <option value="pending" selected>Pendiente</option>
                            <option value="assigned">Asignado</option>
                            <option value="processing">En preparación</option>
                            <option value="paid_confirmed">Pago confirmado</option>
                            <option value="completed">Completado</option>
                            <option value="shipped">Enviado</option>
                            <option value="cancelled">Cancelado</option>
                        </select>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${timeString}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <button onclick="deleteOrderDirect(${orderData.order_id})"
                                class="inline-flex items-center gap-2 rounded-md bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 transition-colors hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50">
                            <span>🗑️</span> Eliminar
                        </button>
                    </td>
                </tr>
            `;

            // Insertar la fila al inicio de la tabla
            tbody.insertAdjacentHTML('afterbegin', rowHTML);

            // Animar la entrada de la fila
            const newRow = tbody.querySelector(`tr[data-order-id="${orderData.order_id}"]`);
            if (newRow) {
                // Aplicar animación de entrada
                newRow.style.animation = 'fadeIn 0.5s ease-in-out';
                // Aplicar destacado amarillo que desaparece
                newRow.style.backgroundColor = '#fff3cd';
                newRow.style.animation = 'highlightFlash 1.5s ease-in-out forwards';
            }

            /* console.log */('%c✅ Fila agregada a la tabla', 'color: green; font-weight: bold', orderData);
        }

        // Función para actualizar estado sin recargar
        async function updateOrderStatusDirect(orderId, newStatus) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            try {
                const response = await fetch(`/api/custom/orders/${orderId}`, {
                    method: 'PATCH',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: newStatus })
                });

                if (response.ok) {
                    /* console.log */('%c✅ Estado actualizado', 'color: green; font-weight: bold');
                } else {
                    console.error('%c❌ Error al actualizar estado', 'color: red; font-weight: bold');
                }
            } catch (e) {
                console.error('%c❌ Error:', 'color: red; font-weight: bold', e);
            }
        }

        // Función para eliminar múltiples órdenes
        async function deleteBulkOrders(orderIds) {
            if (!Array.isArray(orderIds) || orderIds.length === 0) {
                alert('Por favor selecciona al menos una orden');
                return;
            }

            if (!confirm(`¿Eliminar ${orderIds.length} órdenes seleccionadas?`)) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            let successCount = 0;
            let errorCount = 0;

            for (const orderId of orderIds) {
                try {
                    const response = await fetch('/api/custom/orders/' + orderId, {
                        method: 'DELETE',
                        credentials: 'include',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        successCount++;
                        // Remover la fila de la tabla
                        const row = document.querySelector(`tr[data-order-id="${orderId}"]`);
                        if (row) {
                            row.style.opacity = '0';
                            row.style.transition = 'opacity 0.3s ease';
                            setTimeout(() => row.remove(), 300);
                        }
                        /* console.log */(`✅ Orden #${orderId} eliminada`);
                    } else {
                        errorCount++;
                        console.error(`❌ Error al eliminar orden #${orderId}:`, response.statusText);
                    }
                } catch (e) {
                    errorCount++;
                    console.error(`❌ Error en orden #${orderId}:`, e);
                }
            }

            // Mostrar resumen
            if (successCount > 0 && errorCount === 0) {
                alert(`✅ ${successCount} órdenes eliminadas correctamente`);
            } else if (successCount > 0) {
                alert(`⚠️ ${successCount} eliminadas, ${errorCount} con error`);
            } else {
                alert(`❌ Error: No se pudo eliminar ninguna orden`);
            }
        }

        // Inicializar cuando Pusher esté disponible
        if (typeof window.Pusher !== 'undefined') {
            initWebSocketListener();
        } else {
            // Pusher no disponible todavía - reintentar via setTimeout (no polling)
            function waitForPusher(attempts) {
                if (typeof window.Pusher !== 'undefined') {
                    initWebSocketListener();
                } else if (attempts > 0) {
                    setTimeout(() => waitForPusher(attempts - 1), 200);
                } else {
                    console.error('❌ Pusher no disponible después de múltiples intentos');
                }
            }
            waitForPusher(25); // máximo 5 segundos
        }
    </script>
</div>
