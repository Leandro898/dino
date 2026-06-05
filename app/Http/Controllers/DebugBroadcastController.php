<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DebugBroadcastController extends Controller
{
    /**
     * Show current broadcast config
     */
    public function showConfig()
    {
        return response()->json([
            'broadcast_connection' => config('broadcasting.default'),
            'reverb_config' => config('broadcasting.connections.reverb'),
            'env_values' => [
                'REVERB_APP_KEY' => env('REVERB_APP_KEY'),
                'REVERB_APP_SECRET' => env('REVERB_APP_SECRET'),
                'REVERB_HOST' => env('REVERB_HOST'),
                'REVERB_PORT' => env('REVERB_PORT'),
                'REVERB_SCHEME' => env('REVERB_SCHEME'),
            ],
            'current_user' => [
                'id' => auth()->id(),
                'role' => auth()->user()->role ?? null,
            ],
            'broadcasted_variables' => [
                'vendorNotificationBroadcastKey_would_be' => config("broadcasting.connections.reverb.key"),
                'vendorNotificationBroadcastPort_would_be' => config("broadcasting.connections.reverb.options.port"),
            ]
        ]);
    }

    /**
     * Test endpoint to debug broadcast auth without needing Echo
     * Usage: POST /debug/broadcast-auth with: channel_name=vendor.7
     */
    public function testAuth(Request $request)
    {
        $channelName = $request->get('channel_name') ?? 'vendor.7';
        
        Log::info('DEBUG: Testing broadcast auth', ['channel' => $channelName]);

        // Test the same logic as BroadcastAuthController
        if (strpos($channelName, 'vendor') === false) {
            Log::error('DEBUG: no vendor in channel', ['channel' => $channelName]);
            return response()->json([
                'error' => 'no vendor in channel',
                'channel' => $channelName,
                'status' => 'FAILED'
            ], 403);
        }

        // Extract vendor ID
        preg_match('/vendor\.(\d+)/', $channelName, $matches);
        $vendorId = $matches[1] ?? null;

        if (!$vendorId) {
            Log::error('DEBUG: could not extract vendor ID', ['channel' => $channelName]);
            return response()->json([
                'error' => 'could not extract vendor ID',
                'channel' => $channelName,
                'status' => 'FAILED'
            ], 403);
        }

        Log::info('DEBUG: auth would succeed', [
            'vendor_id' => $vendorId,
            'channel' => $channelName,
        ]);

        return response()->json([
            'vendor_id' => $vendorId,
            'channel' => $channelName,
            'channel_data' => [
                'user_id' => 7,
                'user_info' => 'test'
            ],
            'status' => 'OK'
        ], 200);
    }

    /**
     * Test if the event is being broadcast
     */
    public function testEvent()
    {
        Log::info('DEBUG: Firing NewOrderForVendor event for vendor 7');
        
        event(new \App\Events\NewOrderForVendor(
            orderData: [
                'id' => 999,
                'order_number' => 'TEST-001',
                'customer' => 'Test Customer',
                'items' => 'Test items',
            ],
            vendorId: 7
        ));

        return response()->json([
            'status' => 'Event fired',
            'vendor_id' => 7,
            'event' => 'NewOrderForVendor',
            'check_logs' => 'tail -50 /var/www/dino/storage/logs/laravel.log | grep -i broadcast'
        ]);
    }
}
