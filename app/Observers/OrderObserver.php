<?php

namespace App\Observers;

use App\Models\Order;

class OrderObserver
{
    public function saving(Order $order): void
    {
        error_log('👢 OrderObserver::saving() - order: ' . $order->id . ', status: ' . $order->status);
    }

    public function saved(Order $order): void
    {
        error_log('✅ OrderObserver::saved() - order: ' . $order->id . ', status: ' . $order->status);
        
        if ($order->wasChanged('status')) {
            error_log('🔀 Status changed! Original: ' . $order->getOriginal('status') . ', Current: ' . $order->status);
            
            if ($order->status === 'assigned') {
                error_log('🚀 ORDER ASSIGNED via saved(): ' . $order->id);
                $this->handleOrderAssigned($order);
            }
        }
    }

    public function updating(Order $order): void
    {
        error_log('✏️ OrderObserver::updating() - order: ' . $order->id);
    }

    public function updated(Order $order): void
    {
        error_log('📝 OrderObserver::updated() - order: ' . $order->id . ', status: ' . $order->status);
        
        if ($order->wasChanged('status')) {
            error_log('🔀 Status changed in updated! Original: ' . $order->getOriginal('status') . ', Current: ' . $order->status);
            
            if ($order->status === 'assigned') {
                error_log('🚀 ORDER ASSIGNED via updated(): ' . $order->id);
                $this->handleOrderAssigned($order);
            }
        }
    }

    private function handleOrderAssigned(Order $order): void
    {
        try {
            // Get vendors (users who own products in this order)
            $vendors = $order->items
                ->load('product')
                ->map->product
                ->pluck('user')
                ->unique('id');

            error_log('📦 Found ' . count($vendors) . ' vendors');

            foreach ($vendors as $vendor) {
                error_log('📤 Dispatching event to vendor: ' . $vendor->id);
                
                // Dispatch broadcast event (real-time to vendor's browser)
                \App\Events\OrderAssignedBroadcast::dispatch($order, $vendor->id);

                // Send database notification (updates bell icon)
                $vendor->notify(new \App\Notifications\OrderAssignedNotification($order));
                
                error_log('🔔 Notification sent to vendor: ' . $vendor->id);
            }
            
            error_log('✅ All events dispatched successfully');
        } catch (\Throwable $e) {
            error_log('❌ ERROR: ' . $e->getMessage());
            error_log('❌ TRACE: ' . $e->getTraceAsString());
        }
    }
}
