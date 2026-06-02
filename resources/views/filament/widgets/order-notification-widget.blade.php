<div class="bg-white dark:bg-slate-950 border border-gray-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.3em] text-slate-500 dark:text-slate-400">Pedidos recientes</p>
            <h2 class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $pendingOrdersCount }} pedidos nuevos</h2>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Revisa los pedidos pendientes y confirma el siguiente paso.</p>
        </div>
        <a href="{{ $ordersUrl }}" class="inline-flex items-center gap-2 rounded-full border border-indigo-600 bg-indigo-600 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-700">
            Ver pedidos
        </a>
    </div>

    <div class="mt-6 grid gap-3">
        @foreach ($recentOrders as $order)
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 p-4">
                <div class="flex justify-between items-center gap-4">
                    <div>
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Pedido #{{ $order->id }}</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ $order->name }} · ${{ number_format($order->total, 0, ',', '.') }}</p>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase {{ match ($order->status) {
                        'pending' => 'bg-yellow-100 text-yellow-800',
                        'pending_transfer' => 'bg-yellow-100 text-yellow-800',
                        'proof_sent' => 'bg-sky-100 text-sky-800',
                        'processing' => 'bg-sky-100 text-sky-800',
                        'paid_confirmed' => 'bg-emerald-100 text-emerald-800',
                        'completed' => 'bg-emerald-100 text-emerald-800',
                        default => 'bg-slate-100 text-slate-800',
                    } }}">
                        {{ match ($order->status) {
                            'pending_transfer' => 'Pendiente transferencia',
                            'proof_sent' => 'Comprobante enviado',
                            'paid_confirmed' => 'Pago confirmado',
                            default => ucfirst($order->status),
                        } }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
    window.filamentVendorId = @json(auth()->user()->id);
    window.filamentAssignedOrdersCount = @json($recentOrders->count() ?? 0);
</script>
@vite(['resources/js/filament-vendor-order-polling.js'])
