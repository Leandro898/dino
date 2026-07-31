<x-print-layout :orderId="$order->id">
    <div class="ticket-container" style="font-family: sans-serif; color: black; line-height: 1.3;">
        
        <!-- Recuadro principal (abierto por abajo) -->
        <div style="border-left: 1px solid black; border-right: 1px solid black; border-top: 1px solid black; padding-bottom: 10px;">
            <!-- Encabezado COMANDA -->
            <div style="text-align: center; border-bottom: 1px solid black; margin-bottom: 10px; padding: 4px 0;">
                <span style="font-size: 1.35rem; font-weight: normal; letter-spacing: 0.05em;">COMANDA</span>
            </div>

            <!-- Contenido interno con padding lateral -->
            <div style="padding: 0 10px;">
                <!-- Nombre -->
                <div style="font-size: 1.4rem; font-weight: bold; margin-bottom: 2px;">
                    NOMBRE
                </div>
                <div style="font-size: 1.4rem; font-weight: normal; margin-bottom: 8px;">
                    {{ $order->name }}
                </div>

                <!-- Hora -->
                <div style="font-size: 1.4rem; font-weight: bold; margin-bottom: 15px;">
                    HORA: {{ $order->created_at->format('H:i') }}
                </div>

                <!-- Pedido -->
                <div style="font-size: 1.1rem; text-decoration: underline; margin-bottom: 8px;">
                    pedido:
                </div>

                <div style="font-size: 1.4rem; margin-bottom: 40px;">
                    @forelse($order->items as $item)
                        <div style="margin-bottom: 8px;">
                            {{ $item->quantity }} {{ $item->product?->name ?? 'Producto' }}
                        </div>
                    @empty
                        @if($order->order_details || $order->beverage_details)
                            @if($order->order_details)
                                <div style="margin-bottom: 15px;">
                                    {{ $order->order_details }}
                                </div>
                            @endif
                            @if($order->beverage_details)
                                <div style="font-size: 1.1rem; text-decoration: underline; margin-bottom: 8px;">
                                    bebidas:
                                </div>
                                <div style="margin-bottom: 8px;">
                                    {{ $order->beverage_details }}
                                </div>
                            @endif
                        @else
                            <div>Sin productos</div>
                        @endif
                    @endforelse
                </div>

                <!-- Forma de retiro -->
                <div style="font-size: 1rem; font-weight: bold; text-decoration: underline; margin-bottom: 6px;">
                    FORMA DE RETIRO
                </div>
                <div style="font-size: 1.4rem; font-weight: bold; margin-bottom: 10px;">
                    {{ $order->shipping_zone ? 'envio' : 'retira' }}
                </div>
            </div>
        </div>

        <!-- Controles fuera de impresión -->
        <div class="no-print mt-6 flex flex-col gap-2 justify-center">
            <button onclick="window.print()" class="w-full bg-indigo-600 text-white font-bold py-2 px-4 rounded shadow hover:bg-indigo-700 active:scale-95 transition-all text-xs">
                🖨️ Re-imprimir Ticket
            </button>
            <button onclick="window.close()" class="w-full bg-gray-200 text-gray-800 font-semibold py-1.5 px-4 rounded hover:bg-gray-300 active:scale-95 transition-all text-xs">
                Cerrar Ventana
            </button>
        </div>
    </div>

    <!-- Auto-disparar Impresión -->
    <script>
        window.addEventListener('load', () => {
            setTimeout(() => {
                window.print();
                window.onafterprint = () => {
                    window.close();
                };
            }, 300);
        });
    </script>
</x-print-layout>
