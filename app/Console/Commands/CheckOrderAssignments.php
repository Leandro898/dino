<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CheckOrderAssignments extends Command
{
    protected $signature = 'orders:check-assignments';
    protected $description = 'Check for recently assigned orders and dispatch notifications';

    public function handle(): int
    {
        error_log('🔍 Checking for assigned orders...');
        
        // Get orders assigned in last minute
        $recentlyAssigned = Order::where('status', 'assigned')
            ->where('updated_at', '>=', now()->subMinute())
            ->get();

        foreach ($recentlyAssigned as $order) {
            $cacheKey = "order_notification_sent_{$order->id}";
            
            if (!Cache::has($cacheKey)) {
                error_log("🚀 Processing assignment for order {$order->id}");
                
                try {
                    $vendors = $order->items
                        ->load('product')
                        ->map->product
                        ->pluck('user')
                        ->unique('id');

                    foreach ($vendors as $vendor) {
                        error_log("📤 Dispatching to vendor {$vendor->id}");
                        \App\Events\OrderAssignedBroadcast::dispatch($order, $vendor->id);
                        $vendor->notify(new \App\Notifications\OrderAssignedNotification($order));
                    }
                    
                    // Mark as processed
                    Cache::put($cacheKey, true, now()->addHour());
                    error_log("✅ Order {$order->id} processed");
                } catch (\Throwable $e) {
                    error_log("❌ Error: " . $e->getMessage());
                }
            }
        }

        return 0;
    }
}
