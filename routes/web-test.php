<?php
// Test endpoint
Route::get('/debug/assign-order-test', function() {
    $order = \App\Models\Order::where('status', '!=', 'assigned')->first();
    if (!$order) {
        return response()->json(['error' => 'No order found'], 404);
    }
    
    echo "Assigning order " . $order->id . "...\n";
    
    $order->status = 'assigned';
    $order->save();
    
    return response()->json([
        'success' => true,
        'order_id' => $order->id,
        'message' => 'Order assigned - check logs'
    ]);
});
