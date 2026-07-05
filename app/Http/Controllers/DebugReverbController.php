<?php

namespace App\Http\Controllers;

use App\Events\NewOrderForVendor;
use App\Events\NotifyVendorBroadcast;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DebugReverbController extends Controller
{
    public function testBroadcast(Request $request)
    {
        Log::info('=== DEBUG: Test Broadcast Endpoint Called ===');
        
        $orderId = $request->get('order_id', 71);
        $vendorId = $request->get('vendor_id', 7);
        
        Log::info('Getting order', ['order_id' => $orderId]);
        $order = Order::find($orderId);
        
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }
        
        Log::info('Order found, dispatching events', [
            'order_id' => $order->id,
            'vendor_id' => $vendorId,
        ]);
        
        // Dispatch first event
        Log::info('Dispatching NewOrderForVendor...');
        event(new NewOrderForVendor($order, $vendorId));
        Log::info('NewOrderForVendor dispatched');
        
        // Dispatch second event
        Log::info('Dispatching NotifyVendorBroadcast...');
        event(new NotifyVendorBroadcast($order, $vendorId));
        Log::info('NotifyVendorBroadcast dispatched');
        
        return response()->json([
            'message' => 'Events dispatched successfully',
            'order_id' => $order->id,
            'vendor_id' => $vendorId,
            'check_logs' => 'tail -f /var/www/dino/storage/logs/laravel.log | grep DEBUG',
        ]);
    }
}
