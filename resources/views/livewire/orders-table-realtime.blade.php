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
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                <input type="checkbox" id="selectAllCheckbox" class="rounded border-gray-300 dark:bg-gray-900 dark:border-gray-600">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Productos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Método</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Repartidor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900" id="ordersTableBody">
                        @forelse($orders as $order)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition" data-order-id="{{ $order->id }}">
                                <td class="px-6 py-4">
                                    <input type="checkbox" class="order-checkbox rounded border-gray-300 dark:bg-gray-900 dark:border-gray-600" value="{{ $order->id }}" data-order-id="{{ $order->id }}">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">#{{ $order->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">{{ $order->name }}</td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        @forelse($order->items as $item)
                                            <div class="text-sm text-gray-700 dark:text-gray-400">
                                                <span class="font-medium">{{ $item->product?->name ?? 'Producto desconocido' }}</span>
                                                <span class="text-gray-500 dark:text-gray-500"> x{{ $item->quantity }}</span>
                                            </div>
                                        @empty
                                            <span class="text-sm text-gray-500 dark:text-gray-500">Sin productos</span>
                                        @endforelse
                                    </div>
                                </td>
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
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <select onchange="updateOrderRiderDirect({{ $order->id }}, this.value)" class="rounded-md border border-gray-300 bg-white px-3 py-1 text-xs text-gray-900 shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                        <option value="">Sin asignar</option>
                                        @foreach($riders as $rider)
                                            <option value="{{ $rider->id }}" {{ $order->delivery_user_id == $rider->id ? 'selected' : '' }}>
                                                {{ $rider->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $order->created_at->format('H:i:s') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm flex flex-col gap-2">
                                    <button wire:click="viewOrder({{ $order->id }})"
                                            class="inline-flex items-center gap-2 rounded-md bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 transition-colors hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50">
                                        <span>👁️</span>
                                        <span>Ver Detalle</span>
                                    </button>
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

    <!-- Modal Detalle del Pedido -->
    @if($viewingOrder)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm transition-opacity" wire:ignore.self>
            <div class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-xl bg-white shadow-2xl dark:bg-gray-800">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Detalle del Pedido #{{ $viewingOrder->id }}</h3>
                    <button wire:click="closeViewOrder" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <!-- Contenido -->
                <div class="px-6 py-4 space-y-6">
                    <!-- Cliente y Envío -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50">
                            <h4 class="mb-2 text-sm font-medium text-gray-500 dark:text-gray-400">Datos del Cliente</h4>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $viewingOrder->name }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $viewingOrder->email }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $viewingOrder->phone }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50">
                            <h4 class="mb-2 text-sm font-medium text-gray-500 dark:text-gray-400">Ubicación</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $viewingOrder->address }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $viewingOrder->city }}, {{ $viewingOrder->state }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300">CP: {{ $viewingOrder->zip_code }}</p>
                        </div>
                    </div>

                    <!-- Detalles del Pago -->
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Método de Pago</p>
                                <p class="mt-1 font-medium text-gray-900 dark:text-white">{{ $viewingOrder->payment_method === 'mercadopago' ? 'Mercado Pago' : 'Transferencia Bancaria' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Estado</p>
                                <p class="mt-1 font-medium text-gray-900 dark:text-white">
                                    {{ match($viewingOrder->status) {
                                        'pending' => 'Pendiente',
                                        'assigned' => 'Asignado a Repartidor',
                                        'processing' => 'En Preparación',
                                        'completed' => 'Completado',
                                        'shipped' => 'Enviado',
                                        'cancelled' => 'Cancelado',
                                        default => ucfirst($viewingOrder->status)
                                    } }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Productos -->
                    <div>
                        <h4 class="mb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Productos</h4>
                        <div class="divide-y divide-gray-200 rounded-lg border border-gray-200 dark:divide-gray-700 dark:border-gray-700">
                            @foreach($viewingOrder->items as $item)
                                <div class="flex items-center justify-between p-4">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $item->product?->name ?? 'Producto no disponible' }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Cantidad: {{ $item->quantity }}</p>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">${{ number_format($item->price * $item->quantity, 2) }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Totales -->
                    <div class="flex justify-end pt-4">
                        <div class="w-full max-w-sm space-y-2 text-sm">
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>Subtotal</span>
                                <span>${{ number_format($viewingOrder->total, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>Envío</span>
                                <span>Gratis</span>
                            </div>
                            <div class="flex justify-between border-t border-gray-200 pt-2 text-base font-bold text-gray-900 dark:border-gray-700 dark:text-white">
                                <span>Total</span>
                                <span>${{ number_format($viewingOrder->total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-800/50 flex justify-end">
                    <button wire:click="closeViewOrder"
                            class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-700 dark:text-white dark:ring-gray-600 dark:hover:bg-gray-600">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
