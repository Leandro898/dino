<?php

namespace App\Console\Commands;

use App\Events\OrderStatusUpdated;
use App\Models\Order;
use Illuminate\Console\Command;

class SimulateOrderAssigned extends Command
{
    protected $signature = 'order:assign
                            {order_id? : ID del pedido (por defecto: el último pedido pending)}
                            {--vendor_id= : ID del vendor para verificar}';

    protected $description = 'Simula que el admin asigna un pedido a un vendor y dispara el WebSocket';

    public function handle(): int
    {
        $orderId = $this->argument('order_id');

        if ($orderId) {
            $order = Order::with('items.product')->find($orderId);
        } else {
            $order = Order::with('items.product')->where('status', 'pending')->latest()->first();
        }

        if (!$order) {
            $this->error('❌ No se encontró el pedido' . ($orderId ? " #{$orderId}" : ' (ninguno en estado pending)'));
            return self::FAILURE;
        }

        $this->newLine();
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('  📦  SIMULAR ASIGNACIÓN DE PEDIDO');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line("  Pedido     : <fg=cyan>#" . $order->id . "</>");
        $this->line("  Cliente    : <fg=white>" . ($order->name ?? '-') . "</>");
        $this->line("  Estado act : <fg=yellow>" . $order->status . "</>");
        $this->line("  Total      : <fg=green>$" . number_format($order->total, 2) . " ARS</>");

        // Calcular vendor_ids
        $vendorIds = $order->items
            ->pluck('product.user_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $this->newLine();
        if (empty($vendorIds)) {
            $this->warn('  ⚠️  PROBLEMA: El pedido no tiene productos con vendor asignado!');
            $this->line('  El evento se disparará pero no llegará a ningún canal de vendor.');
        } else {
            $this->line("  Vendors afectados: <fg=green>" . implode(', ', $vendorIds) . "</>");
            $this->line("  Canal WebSocket  : <fg=green>" . implode(', ', array_map(fn($id) => "vendor.{$id}", $vendorIds)) . "</>");
        }

        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        // Cambiar estado a assigned
        $oldStatus = $order->status;
        $order->update(['status' => 'assigned']);

        $this->line('  <fg=yellow>📡 Disparando broadcast OrderStatusUpdated...</>');

        // Disparar broadcast (sin toOthers para que llegue a todos)
        broadcast(new OrderStatusUpdated($order->fresh(), $oldStatus));

        $this->newLine();
        $this->line('  <fg=green;options=bold>✅ Evento disparado!</>');
        $this->line('  <fg=gray>El pedido ahora está en estado: </>assigned');
        $this->newLine();
        $this->line('  <fg=gray>Verificar en el panel vendor:</>');
        $this->line('  <fg=cyan>  http://aprod.test:8080/admin/vendor-orders</>');
        $this->newLine();
        $this->line('  <fg=gray>Verificar en los logs Laravel (debe aparecer "Broadcast auth attempt"):</>');
        $this->line('  <fg=gray>  tail -f storage/logs/laravel.log | grep -i "broadcast"</>');
        $this->newLine();
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        return self::SUCCESS;
    }
}
