<?php

namespace App\Console\Commands;

use App\Events\NewOrderCreated;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Command;

class SimulateOrder extends Command
{
    protected $signature = 'order:simulate
                            {--nombre= : Nombre del cliente (por defecto: aleatorio)}
                            {--total= : Total del pedido en ARS (por defecto: aleatorio)}
                            {--metodo= : Método de pago: efectivo, transferencia, mercadopago (por defecto: efectivo)}
                            {--solo-evento : Solo disparar el evento WebSocket sin crear un pedido en la base de datos}';

    protected $description = 'Simula la llegada de un nuevo pedido y dispara la notificación WebSocket en el panel admin';

    private array $nombres = [
        'María García', 'Carlos López', 'Ana Martínez', 'Juan Rodríguez',
        'Laura Fernández', 'Diego Pérez', 'Sofia González', 'Lucas Sánchez',
        'Valentina Torres', 'Mateo Ramírez',
    ];

    private array $metodos = ['efectivo', 'transferencia', 'mercadopago'];

    public function handle(): int
    {
        $nombre  = $this->option('nombre')  ?? $this->nombres[array_rand($this->nombres)];
        $total   = (float) ($this->option('total')  ?? rand(500, 15000));
        $metodo  = $this->option('metodo')  ?? $this->metodos[array_rand($this->metodos)];
        $soloEvento = $this->option('solo-evento');

        $this->newLine();
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('  🛒  SIMULAR NUEVO PEDIDO');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line("  Cliente : <fg=cyan>{$nombre}</>");
        $this->line("  Total   : <fg=green>$" . number_format($total, 2) . " ARS</>");
        $this->line("  Método  : <fg=yellow>{$metodo}</>");
        $this->line("  Modo    : " . ($soloEvento ? '<fg=magenta>Solo evento (sin DB)</>' : '<fg=white>Crear pedido + evento</>'));
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        if ($soloEvento) {
            // Crear un Order falso en memoria (sin guardar en DB)
            $fakeOrder = new Order([
                'name'           => $nombre,
                'email'          => strtolower(str_replace(' ', '.', $nombre)) . '@test.com',
                'address'        => 'Calle Falsa 123, Bariloche',
                'phone'          => '2944' . rand(100000, 999999),
                'total'          => $total,
                'payment_method' => $metodo,
                'status'         => 'pending',
                'shipping_cost'  => 0,
            ]);
            $fakeOrder->id = 9999;
            $fakeOrder->created_at = now();

            $this->line('  <fg=yellow>⚡ Disparando evento WebSocket sin crear pedido en DB...</>');
            broadcast(new NewOrderCreated($fakeOrder));
        } else {
            // Buscar o crear cliente de prueba
            $user = User::where('email', 'test.cliente@aprod.test')->first()
                ?? User::create([
                    'name'     => 'Cliente Test',
                    'email'    => 'test.cliente@aprod.test',
                    'password' => bcrypt('password123'),
                    'role'     => 'customer',
                ]);

            // Buscar un producto disponible
            $product = Product::first();

            // Crear el pedido
            $order = Order::create([
                'user_id'        => $user->id,
                'name'           => $nombre,
                'email'          => strtolower(str_replace(' ', '.', $nombre)) . '@test.com',
                'address'        => 'Calle de Prueba 456, Bariloche',
                'phone'          => '2944' . rand(100000, 999999),
                'total'          => $total,
                'payment_method' => $metodo,
                'status'         => 'pending',
                'shipping_cost'  => 0,
            ]);

            // Agregar un item si hay producto disponible
            if ($product) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $product->id,
                    'quantity'   => 1,
                    'price'      => $total,
                    'subtotal'   => $total,
                ]);
            }

            $this->line("  <fg=green>✅ Pedido #{$order->id} creado en la base de datos</>");
            $this->line('  <fg=yellow>📡 Disparando evento WebSocket...</>');

            broadcast(new NewOrderCreated($order));

            $this->newLine();
            $this->line("  <fg=gray>ID del pedido : #{$order->id}</>");
            $this->line("  <fg=gray>Para eliminar : php artisan tinker --execute=\"App\\\\Models\\\\Order::find({$order->id})?->delete()\"</>");
        }

        $this->newLine();
        $this->line('  <fg=green;options=bold>🎉 ¡Evento enviado! Revisá el panel admin en:</>');
        $this->line('  <fg=cyan>  http://aprod.test:8080/admin/orders-realtime</>');
        $this->newLine();
        $this->line('  <fg=gray>Si no suena el sonido, asegurate de haber hecho click en</> <fg=green>"🔊 Activar Sonido"</> <fg=gray>en el panel.</>');
        $this->newLine();
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        return self::SUCCESS;
    }
}
