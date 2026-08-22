<script>window.disableAlpineStart = true;</script>
@vite(['resources/css/app.css', 'resources/js/app.js'])

<script>
    // Interceptar TODOS los errores y warnings INMEDIATAMENTE
    // Esto se ejecuta ANTES de que se cargue cualquier script externo
    window.originalError = window.onerror;
    window.originalWarn = console.warn;
    window.originalConsoleError = console.error;
    
    // Silenciar onerror
    window.onerror = function(msg, url, lineNo, columnNo, error) {
        if (msg.includes('Alpine') || 
            msg.includes('Splitter') || 
            msg.includes('groupIsCollapsed') ||
            msg.includes('isOpen') ||
            msg.includes('Cannot read properties')) {
            return true; // Prevenir que se muestre el error
        }
        if (window.originalError) {
            return window.originalError(msg, url, lineNo, columnNo, error);
        }
    };
    
    // Silenciar console.warn
    console.warn = function(...args) {
        const msg = args.join(' ');
        if (msg.includes('Alpine') || msg.includes('plugin') || msg.includes('Splitter')) {
            return;
        }
        window.originalWarn.apply(console, args);
    };
    
    // Silenciar console.error
    console.error = function(...args) {
        const msg = args.join(' ');
        if (msg.includes('Alpine') || 
            msg.includes('Splitter') || 
            msg.includes('groupIsCollapsed') ||
            msg.includes('isOpen') ||
            msg.includes('Cannot read properties') ||
            msg.includes('is not a function')) {
            return;
        }
        window.originalConsoleError.apply(console, args);
    };
    
    // Silenciar unhandledrejection
    window.addEventListener('unhandledrejection', function(event) {
        const msg = event.reason?.message || event.reason || '';
        if (msg.includes('Alpine') || msg.includes('Splitter')) {
            event.preventDefault();
        }
    });
</script>

<x-filament-panels::page>
    @livewire('orders-table-realtime')
</x-filament-panels::page>
    <script>
//<![CDATA[
        window.allRidersList = <?php echo json_encode(\App\Models\User::where('role', 'delivery')->where('is_approved', true)->get(['id', 'name'])); ?>;

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

        // 🔊 Función para reproducir sonido de Pedido Especial (con voz y volumen alto)
        function playSpecialOrderSound() {
            const audio = new Audio('{{ asset('sounds/order.mp3') }}');
            audio.volume = 1.0;
            audio.play()
                .then(() => {
                    if ('speechSynthesis' in window) {
                        setTimeout(() => {
                            const utterance = new SpeechSynthesisUtterance('Nuevo pedido especial en el chat');
                            utterance.lang = 'es-ES';
                            utterance.rate = 0.95;
                            window.speechSynthesis.speak(utterance);
                        }, 1200);
                    }
                })
                .catch(err => console.warn('⚠️ Error al reproducir audio de pedido especial:', err));
        }

        // 🔊 Función para reproducir sonido de Soporte de Repartidor
        function playSupportSound(senderName = 'un repartidor') {
            const audio = new Audio('{{ asset('sounds/order.mp3') }}');
            audio.volume = 1.0;
            audio.play()
                .then(() => {
                    if ('speechSynthesis' in window) {
                        setTimeout(() => {
                            const message = 'Nuevo mensaje de soporte de ' + senderName;
                            const utterance = new SpeechSynthesisUtterance(message);
                            utterance.lang = 'es-AR';
                            utterance.rate = 0.95;
                            
                            let voices = window.speechSynthesis.getVoices();
                            
                            // Buscar voz masculina (Diego en Mac/iOS, Pablo en Windows)
                            let voice = voices.find(v => v.name.includes('Diego')); // Mac/iOS Argentina Male
                            if (!voice) voice = voices.find(v => v.name.includes('Pablo')); // Windows Spain Male
                            if (!voice) voice = voices.find(v => v.lang === 'es-AR'); // Cualquier voz de Argentina
                            if (!voice) voice = voices.find(v => v.lang.startsWith('es')); // Fallback
                            
                            if (voice) {
                                utterance.voice = voice;
                                console.log('🗣️ Voz seleccionada:', voice.name);
                            }
                            
                            window.speechSynthesis.speak(utterance);
                        }, 1200);
                    }
                })
                .catch(err => console.warn('⚠️ Error al reproducir audio de soporte:', err));
        }

        // 🔔 Función para mostrar notificación de Pedido Especial
        function showSpecialOrderNotification(requestId) {
            if ('Notification' in window && Notification.permission === 'granted') {
                const notification = new Notification('💬 ¡NUEVO PEDIDO ESPECIAL!', {
                    body: `Hay un nuevo mensaje en el Pedido Especial #${requestId}`,
                    tag: 'custom-request-' + requestId,
                    requireInteraction: true
                });

                notification.onclick = function() {
                    window.open(`/admin/custom-requests/${requestId}/chat`, '_blank');
                    window.focus();
                };
            }
        }

        // 🔔 Función para mostrar notificación de Soporte de Repartidor
        function showSupportNotification(deliveryUserId, senderName) {
            if ('Notification' in window && Notification.permission === 'granted') {
                const notification = new Notification('💬 ¡NUEVO MENSAJE DE SOPORTE!', {
                    body: `Mensaje de soporte de repartidor: ${senderName}`,
                    tag: 'support-' + deliveryUserId,
                    requireInteraction: true
                });

                notification.onclick = function() {
                    window.open(`/admin/delivery-supports/${deliveryUserId}/chat`, '_blank');
                    window.focus();
                };
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
                    const statusSelect = row.querySelector('select[onchange^="updateOrderStatusDirect"]');
                    if (statusSelect) {
                        statusSelect.value = data.new_status;
                    }

                    // Encontrar el select de repartidor y actualizarlo
                    const riderSelect = row.querySelector('select[onchange^="updateOrderRiderDirect"]');
                    if (riderSelect) {
                        riderSelect.value = data.delivery_user_id || '';
                    }

                    row.style.backgroundColor = '#fffacd'; // Highlight amarillo
                    setTimeout(() => row.style.backgroundColor = '', 2000);
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

            // 💬 Escuchar mensajes nuevos de Pedidos Especiales (CustomRequests)
            channel.bind('message.sent', (data) => {
                console.log('🎉 ¡MENSAJE DE PEDIDO ESPECIAL RECIBIDO!', data);
                playSpecialOrderSound();
                showSpecialOrderNotification(data.requestId);
            });

            // 💬 Escuchar mensajes nuevos de Soporte de Repartidores
            channel.bind('support-message.sent', (data) => {
                console.log('🎉 ¡MENSAJE DE SOPORTE DE REPARTIDOR RECIBIDO!', data);
                const currentUserId = {{ auth()->id() }};
                if (data.senderId !== currentUserId) {
                    playSupportSound(data.senderName);
                    showSupportNotification(data.deliveryUserId, data.senderName);
                }
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

            // Generate items HTML
            let itemsHtml = '<span class="text-sm text-gray-500 dark:text-gray-500">Sin productos</span>';
            if (orderData.items && orderData.items.length > 0) {
                itemsHtml = orderData.items.map(item => `
                    <div class="text-sm text-gray-700 dark:text-gray-400">
                        <span class="font-medium">${item.product_name}</span>
                        <span class="text-gray-500 dark:text-gray-500"> x${item.quantity}</span>
                    </div>
                `).join('');
            }

            // Generate riders HTML
            let ridersOptions = '<option value="">Sin asignar</option>';
            if (window.allRidersList && window.allRidersList.length > 0) {
                ridersOptions += window.allRidersList.map(rider => `
                    <option value="${rider.id}" ${orderData.delivery_user_id == rider.id ? 'selected' : ''}>
                        ${rider.name}
                    </option>
                `).join('');
            }

            // Crear HTML de la fila
            const rowHTML = `
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition" data-order-id="${orderData.order_id}">
                    <td class="px-6 py-4">
                        <input type="checkbox" value="${orderData.order_id}" class="rounded border-gray-300 dark:bg-gray-900 dark:border-gray-600">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">#${orderData.order_id}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">${orderData.customer_name || orderData.name}</td>
                    <td class="px-6 py-4">
                        <div class="space-y-1">
                            ${itemsHtml}
                        </div>
                    </td>
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
                    <td class="px-6 py-4 whitespace-nowrap">
                        <select onchange="updateOrderRiderDirect(${orderData.order_id}, this.value)" class="rounded-md border border-gray-300 bg-white px-3 py-1 text-xs text-gray-900 shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            ${ridersOptions}
                        </select>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${timeString}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm flex flex-col gap-2">
                        <button onclick="window.Livewire.find(document.querySelector('[wire\\\\:id]').getAttribute('wire:id')).viewOrder(${orderData.order_id})"
                                class="inline-flex items-center gap-2 rounded-md bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 transition-colors hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50">
                            <span>👁️</span> Ver Detalle
                        </button>
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

        // Función para actualizar repartidor sin recargar
        async function updateOrderRiderDirect(orderId, newRiderId) {
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
                    body: JSON.stringify({ delivery_user_id: newRiderId })
                });

                if (response.ok) {
                    /* console.log */('%c✅ Repartidor actualizado', 'color: green; font-weight: bold');
                } else {
                    const errData = await response.json();
                    alert(errData.error || 'Error al actualizar repartidor');
                    window.location.reload();
                }
            } catch (e) {
                console.error('%c❌ Error:', 'color: red; font-weight: bold', e);
            }
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
//]]>
    </script>
