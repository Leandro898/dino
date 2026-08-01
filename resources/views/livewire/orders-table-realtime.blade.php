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
                                                {{ $rider->isOnline() ? '🟢' : '⚪' }} {{ $rider->name }}
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
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md transition-opacity animate-[fadeIn_0.2s_ease-out]" wire:ignore.self>
            <div class="relative w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden rounded-2xl bg-white shadow-2xl border border-slate-300 dark:border-slate-700 dark:bg-slate-900">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4.5 dark:border-slate-700 bg-white dark:bg-slate-900 shrink-0">
                    <div class="flex items-center gap-2.5">
                        <svg class="h-6 w-6 text-slate-600 dark:text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Detalle del Pedido #{{ $viewingOrder->id }}</h3>
                    </div>
                    <button wire:click="closeViewOrder" class="rounded-lg p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:text-slate-300 dark:hover:bg-slate-800 transition-all">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <!-- Contenido (Scrollable) -->
                <div class="px-6 py-5 space-y-6 overflow-y-auto flex-1 max-h-[calc(90vh-140px)] bg-white dark:bg-slate-900">
                    <!-- Cliente y Envío -->
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <!-- Tarjeta de Datos del Cliente -->
                        <div class="rounded-xl border border-slate-300/80 bg-slate-50/80 p-5 dark:border-slate-700 dark:bg-slate-800/30">
                            <div class="flex items-center gap-1.5 mb-3.5">
                                <svg class="h-5 w-5 text-slate-500 dark:text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="text-xs font-bold tracking-wider text-slate-600 dark:text-slate-400 uppercase">Datos del Cliente</span>
                            </div>
                            <div class="space-y-2">
                                <p class="text-sm font-bold text-slate-900 dark:text-slate-50">{{ $viewingOrder->name }}</p>
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300 break-all">{{ $viewingOrder->email }}</p>
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $viewingOrder->phone }}</p>
                            </div>
                        </div>
                        
                        <!-- Tarjeta de Ubicación -->
                        <div class="rounded-xl border border-slate-300/80 bg-slate-50/80 p-5 dark:border-slate-700 dark:bg-slate-800/30">
                            <div class="flex items-center gap-1.5 mb-3.5">
                                <svg class="h-5 w-5 text-slate-500 dark:text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="text-xs font-bold tracking-wider text-slate-600 dark:text-slate-400 uppercase">Ubicación de Envío</span>
                            </div>
                            <div class="space-y-1.5">
                                <p class="text-sm font-bold text-slate-900 dark:text-slate-50">{{ $viewingOrder->address }}</p>
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $viewingOrder->city }}, {{ $viewingOrder->state }}</p>
                                <p class="text-xs font-bold text-slate-500 dark:text-slate-500 mt-1">Código Postal: {{ $viewingOrder->zip_code }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Detalles de Pago y Estado -->
                    <div class="rounded-xl border border-slate-300/80 bg-slate-50/80 p-5 dark:border-slate-700 dark:bg-slate-800/30">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <span class="text-xs font-bold tracking-wider text-slate-500 dark:text-slate-400 uppercase block mb-1.5">Método de Pago</span>
                                <div>
                                    @if($viewingOrder->payment_method === 'mercadopago')
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-800 ring-1 ring-inset ring-blue-700/20 dark:bg-blue-900/40 dark:text-blue-300">💳 Mercado Pago</span>
                                    @elseif($viewingOrder->payment_method === 'transferencia')
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-800 ring-1 ring-inset ring-green-700/20 dark:bg-green-900/40 dark:text-green-300">🏦 Transferencia Bancaria</span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800 ring-1 ring-inset ring-amber-700/20 dark:bg-amber-900/40 dark:text-amber-300">💵 {{ ucfirst($viewingOrder->payment_method) }}</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div>
                                <span class="text-xs font-bold tracking-wider text-slate-500 dark:text-slate-400 uppercase block mb-1.5">Estado del Pedido</span>
                                <div>
                                    @php
                                        $statusLabel = match($viewingOrder->status) {
                                            'pending' => 'Pendiente',
                                            'assigned' => 'Asignado a Repartidor',
                                            'processing' => 'En Preparación',
                                            'completed' => 'Completado',
                                            'shipped' => 'Enviado',
                                            'cancelled' => 'Cancelado',
                                            default => ucfirst($viewingOrder->status)
                                        };
                                        
                                        $statusClass = match($viewingOrder->status) {
                                            'pending' => 'bg-amber-100 text-amber-900 ring-amber-600/30 dark:bg-amber-900/40 dark:text-amber-300',
                                            'assigned' => 'bg-sky-100 text-sky-900 ring-sky-600/30 dark:bg-sky-900/40 dark:text-sky-300',
                                            'processing' => 'bg-purple-100 text-purple-900 ring-purple-600/30 dark:bg-purple-900/40 dark:text-purple-300',
                                            'completed' => 'bg-emerald-100 text-emerald-900 ring-emerald-600/30 dark:bg-emerald-900/40 dark:text-emerald-300',
                                            'shipped' => 'bg-indigo-100 text-indigo-900 ring-indigo-600/30 dark:bg-indigo-900/40 dark:text-indigo-300',
                                            'cancelled' => 'bg-rose-100 text-rose-900 ring-rose-600/30 dark:bg-rose-900/40 dark:text-rose-300',
                                            default => 'bg-slate-100 text-slate-900 ring-slate-600/30 dark:bg-slate-900/40 dark:text-slate-300'
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Productos -->
                    <div>
                        <span class="text-xs font-bold tracking-wider text-slate-500 dark:text-slate-400 uppercase mb-3 block">Productos en la Orden</span>
                        <div class="overflow-hidden rounded-xl border border-slate-300 dark:border-slate-700 shadow-sm">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-slate-100 text-xs font-bold uppercase text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-b border-slate-300 dark:border-slate-700">
                                    <tr>
                                        <th class="px-4 py-3">Producto</th>
                                        <th class="px-4 py-3 text-center">Cantidad</th>
                                        <th class="px-4 py-3 text-right">Precio Unit.</th>
                                        <th class="px-4 py-3 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-700 dark:bg-slate-900">
                                    @foreach($viewingOrder->items as $item)
                                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                                            <td class="px-4 py-3 font-semibold text-slate-900 dark:text-slate-100">
                                                {{ $item->product?->name ?? 'Producto no disponible' }}
                                            </td>
                                            <td class="px-4 py-3 text-center text-slate-700 dark:text-slate-300">
                                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-800 dark:bg-slate-800 dark:text-slate-200 border border-slate-300 dark:border-slate-600">
                                                    {{ $item->quantity }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-right font-bold text-slate-700 dark:text-slate-300">
                                                ${{ number_format($item->price, 2) }}
                                            </td>
                                            <td class="px-4 py-3 text-right font-bold text-slate-900 dark:text-slate-100">
                                                ${{ number_format($item->price * $item->quantity, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Totales -->
                    <div class="flex justify-end pt-2">
                        <div class="w-full max-w-xs rounded-xl border border-slate-300 bg-slate-50/80 p-4 dark:border-slate-700 dark:bg-slate-800/30 space-y-2.5 text-sm">
                            <div class="flex justify-between text-slate-600 dark:text-slate-400">
                                <span class="font-bold">Subtotal</span>
                                <span class="font-bold text-slate-900 dark:text-slate-200">${{ number_format($viewingOrder->total - ($viewingOrder->shipping_cost ?? 0), 2) }}</span>
                            </div>
                            <div class="flex justify-between text-slate-600 dark:text-slate-400">
                                <span class="font-bold">Envío</span>
                                <span class="font-bold text-slate-900 dark:text-slate-200">${{ number_format($viewingOrder->shipping_cost ?? 0, 2) }}</span>
                            </div>
                            <div class="flex justify-between border-t border-slate-300 pt-2.5 dark:border-slate-700">
                                <span class="text-base font-extrabold text-slate-900 dark:text-slate-100">Total</span>
                                <span class="text-lg font-extrabold text-slate-900 dark:text-slate-50">${{ number_format($viewingOrder->total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="border-t border-slate-200 bg-slate-50 px-6 py-4 dark:border-slate-700 dark:bg-slate-800/50 flex justify-end shrink-0">
                    <button wire:click="closeViewOrder"
                            class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-950 focus:ring-offset-2 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white transition-all">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
