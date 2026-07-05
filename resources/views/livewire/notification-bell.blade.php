<div class="relative inline-block" data-custom-bell="true" wire:ignore>
    <!-- Botón de campana - Estilo similar a Filament topbar -->
    <button wire:click="toggleOpen" 
            class="flex items-center justify-center w-9 h-9 rounded-md text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors duration-200"
            aria-label="Notificaciones">
        <!-- SVG de campana -->
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        
        <!-- Badge rojo con contador -->
        @if($unreadCount > 0)
            <span class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full min-w-[20px]">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    <!-- Panel de notificaciones - Menú desplegable -->
    @if($isOpen)
        <div class="absolute top-full right-0 mt-2 w-96 bg-white dark:bg-gray-900 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-50 overflow-hidden">
            <!-- Header -->
            <div class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-4 py-3 flex items-center justify-between">
                <h3 class="font-semibold text-sm text-gray-950 dark:text-white">🔔 Notificaciones</h3>
                <button wire:click="toggleOpen" 
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Contenido -->
            <div class="max-h-80 overflow-y-auto">
                @if($unreadCount > 0)
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border-b border-blue-200 dark:border-blue-700">
                        <div class="flex gap-3">
                            <div class="text-2xl">🔔</div>
                            <div class="flex-1">
                                <p class="font-semibold text-sm text-blue-900 dark:text-blue-100">
                                    {{ $unreadCount }} {{ $unreadCount === 1 ? 'notificación nueva' : 'notificaciones nuevas' }}
                                </p>
                                <p class="text-xs text-blue-700 dark:text-blue-300 mt-1">
                                    Se han asignado nuevos pedidos a tu cuenta
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                        <div class="text-3xl mb-2">🔕</div>
                        <p class="text-sm">No hay notificaciones nuevas</p>
                    </div>
                @endif

                <!-- Total de pedidos activos -->
                <div class="border-t border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-800/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Pedidos activos</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $totalOrders }}</p>
                        </div>
                        <div class="text-4xl">📦</div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    // Cerrar panel cuando se hace click fuera
    document.addEventListener('click', (e) => {
        const bell = e.target.closest('[data-custom-bell="true"]');
        
        if (!bell && @json($isOpen)) {
            @this.toggleOpen();
        }
    });

    // Prevenir que el panel se cierre al hacer click dentro del panel
    document.addEventListener('click', (e) => {
        const panel = e.target.closest('.z-50');
        if (panel) {
            e.stopPropagation();
        }
    });
</script>
