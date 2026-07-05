<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Events\NewOrderCreated;
use Illuminate\Support\Facades\Log;

class TestOrder extends Command
{
    protected $signature = 'test:order';
    protected $description = 'Create a test order and broadcast notification';

    public function handle()
    {
        // Get or create test customer
        $user = User::where('role', 'customer')->first();
        if (!$user) {
            $user = User::create([
                'name' => 'Test Customer',
                'email' => 'test@example.com',
                'password' => bcrypt('password123'),
                'role' => 'customer'
            ]);
            $this->info('✅ Usuario creado');
        }

        // Get first product
        $product = Product::first();
        if ($product) {
            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'name' => 'Test Customer',
                'email' => 'test@example.com',
                'address' => '123 Test St',
                'phone' => '1234567890',
                'total' => 150,
                'payment_method' => 'efectivo',
                'status' => 'pending',
                'shipping_cost' => 0
            ]);
            
            // Create order item
            $order->items()->create([
                'product_id' => $product->id,
                'quantity' => 2,
                'price' => $product->price ?? 75,
                'subtotal' => ($product->price ?? 75) * 2
            ]);
            
            // Broadcast event
            Log::info('🚀 About to dispatch broadcast event', ['order_id' => $order->id]);
            broadcast(new NewOrderCreated($order))->toOthers();
            Log::info('🎉 Broadcast event dispatched successfully', ['order_id' => $order->id]);
            
            $this->info('✅ Orden #' . $order->id . ' creada y broadcast enviado');
        } else {
            $this->error('❌ No products found');
        }
    }
}
