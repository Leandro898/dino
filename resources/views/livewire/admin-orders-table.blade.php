<div>
    <!-- Audio para notificaciones -->
    <audio id="pickupReadySound" preload="auto">
        <source src="{{ asset('sounds/admin.mp3') }}" type="audio/mpeg">
    </audio>

    <!-- Audio para nueva orden -->
    <audio id="newOrderSound" preload="auto">
        <source src="{{ asset('sounds/order.mp3') }}" type="audio/mpeg">
    </audio>

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

        @if($orders->count())
            <!-- Botón de eliminación masiva -->
            @if(!empty($selectedOrders))
                <div class="flex items-center justify-between rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/30">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-700 dark:bg-blue-800 dark:text-blue-200">
                            {{ count($selectedOrders) }} seleccionada(s)
                        </span>
                        <button wire:click="deselectAll" class="text-sm text-blue-600 hover:text-blue-800 underline dark:text-blue-300 dark:hover:text-blue-100">
                            Deseleccionar
                        </button>
                    </div>
                    <button wire:click="deleteSelectedOrders" 
                            onclick="return confirm('¿Estás seguro de eliminar estas órdenes? Esta acción no se puede deshacer.')"
                            class="inline-flex items-center gap-2 rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-600">
                        <span>🗑️</span>
                        <span>Eliminar {{ count($selectedOrders) }}</span>
                    </button>
                </div>
            @endif
            
            <!-- Tabla -->
            <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/50">
                                <th class="px-4 py-3 w-10">
                                    <input type="checkbox" 
                                           wire:model.live="selectAll"
                                           wire:change="toggleSelectAll"
                                           class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600">
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-950 dark:text-white">Pedido</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-950 dark:text-white">Total</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-950 dark:text-white">Cliente</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-950 dark:text-white">Productos</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-950 dark:text-white">Pago</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-950 dark:text-white">Estado</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-950 dark:text-white">Fecha</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-950 dark:text-white">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($orders as $order)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition {{ in_array($order->id, $selectedOrders) ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                                    <td class="px-4 py-4 w-10">
                                        <input type="checkbox" 
                                               wire:model="selectedOrders"
                                               value="{{ $order->id }}"
                                               class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600">
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-950 dark:text-white">#{{ $order->id }}</td>
                                    <td class="px-6 py-4 font-medium text-green-600 dark:text-green-400">{{ number_format($order->total, 2) }} ARS</td>
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $order->user?->name ?? '-' }}</td>
                                    <td class="px-6 py-4">
                                        <div class="space-y-1">
                                            @forelse($order->items as $item)
                                                <div class="text-sm text-gray-700 dark:text-gray-300">
                                                    <span class="font-medium">{{ $item->product?->name ?? 'Producto desconocido' }}</span>
                                                    <span class="text-gray-500 dark:text-gray-400"> x{{ $item->quantity }}</span>
                                                </div>
                                            @empty
                                                <span class="text-sm text-gray-500 dark:text-gray-400">Sin productos</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium
                                            @switch($order->payment_method)
                                                @case('mercadopago')
                                                    bg-green-50 text-green-700 ring-1 ring-green-600/20 dark:bg-green-900/30 dark:text-green-400 dark:ring-green-600/50
                                                @break
                                                @case('transferencia')
                                                    bg-yellow-50 text-yellow-700 ring-1 ring-yellow-600/20 dark:bg-yellow-900/30 dark:text-yellow-400 dark:ring-yellow-600/50
                                                @break
                                                @case('efectivo')
                                                    bg-blue-50 text-blue-700 ring-1 ring-blue-600/20 dark:bg-blue-900/30 dark:text-blue-400 dark:ring-blue-600/50
                                                @break
                                                @default
                                                    bg-gray-50 text-gray-700 ring-1 ring-gray-600/20 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-600/50
                                            @endswitch">
                                            @switch($order->payment_method)
                                                @case('mercadopago')
                                                    💳 Mercado Pago
                                                @break
                                                @case('transferencia')
                                                    🏦 Transferencia
                                                @break
                                                @case('efectivo')
                                                    💵 Efectivo
                                                @break
                                                @default
                                                    {{ ucfirst($order->payment_method) }}
                                            @endswitch
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <select wire:change="updateOrderStatus({{ $order->id }}, $event.target.value)"
                                                data-order-id="{{ $order->id }}"
                                                class="px-3 py-1 text-xs font-medium rounded-md border transition-colors cursor-pointer bg-white text-gray-900 dark:bg-gray-800 dark:text-white">
                                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pendiente</option>
                                            <option value="assigned" {{ $order->status === 'assigned' ? 'selected' : '' }}>Asignado</option>
                                            <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>En preparación</option>
                                            <option value="paid_confirmed" {{ $order->status === 'paid_confirmed' ? 'selected' : '' }}>Pago confirmado</option>
                                            <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completado</option>
                                            <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Enviado</option>
                                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                                        </select>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4">
                                        <button onclick="if(confirm('¿Estás seguro de que quieres eliminar esta orden? Esta acción no se puede deshacer.')) { @this.call('deleteOrder', {{ $order->id }}) }"
                                                class="inline-flex items-center gap-2 rounded-md bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 transition-colors hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50">
                                            <span>🗑️</span>
                                            <span>Eliminar</span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- Paginación -->
                <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                    {{ $orders->links() }}
                </div>
            </div>
        @else
            <div class="rounded-lg border border-gray-200 bg-white p-12 text-center shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">No hay pedidos con los filtros aplicados</p>
            </div>
        @endif
    </div>

    <script>
        // 🔌 SISTEMA DE NOTIFICACIÓN EN TIEMPO REAL CON WEBSOCKETS (Reverb)
        console.log('%c🚀 WebSocket listener inicializado', 'color: green; font-size: 14px; font-weight: bold');
        
        let audioContext = null;

        // Inicializar AudioContext
        function initAudioContext() {
            if (!audioContext) {
                try {
                    audioContext = new (window.AudioContext || window.webkitAudioContext)();
                } catch (e) {
                    console.warn('⚠️ AudioContext error');
                }
            }
            if (audioContext && audioContext.state === 'suspended') {
                audioContext.resume();
            }
        }

        // Reproducir sonido de nueva orden
        function playNewOrderSound() {
            const audio = document.getElementById('newOrderSound');
            
            if (!audio) {
                console.error('❌ Audio no encontrado');
                return;
            }

            initAudioContext();
            
            // Crear clon para permitir reproducción múltiple rápida
            const audioClone = audio.cloneNode();
            audioClone.volume = 1;
            
            const playPromise = audioClone.play();
            
            if (playPromise !== undefined) {
                playPromise
                    .then(() => {
                        console.log('✅ SONIDO REPRODUCIDO!');
                    })
                    .catch(err => {
                        console.error('❌ Error:', err.name);
                        audio.currentTime = 0;
                        audio.play().catch(e => console.warn('❌ Error:', e.name));
                    });
            }
        }

        // Mostrar notificación de nueva orden
        function showNewOrderNotification(orderId, customerName, total) {
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification('🎉 ¡NUEVA ORDEN!', {
                    body: `#${orderId} - ${customerName} - $${total}`,
                    tag: 'new-order-' + orderId,
                    requireInteraction: true
                });
            }
        }

        // Pedir permisos de notificación al inicio
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }

        // Inicializar AudioContext con el primer click del usuario
        document.addEventListener('click', initAudioContext, { once: true });
    </script>
</div>
