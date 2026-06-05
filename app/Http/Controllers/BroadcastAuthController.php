<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BroadcastAuthController extends Controller
{
    public function auth(Request $request)
    {
        $channelName = $request->get('channel_name');
        
        Log::info('Broadcasting auth', [
            'user_id' => auth()->id(),
            'authenticated' => auth()->check(),
            'channel' => $channelName,
        ]);

        if (!auth()->check()) {
            abort(403);
        }

        // Accept any channel with "vendor" in the name (temporary: debug mode)
        if (strpos($channelName, 'vendor') === false) {
            Log::error('Broadcasting: no vendor in channel', ['channel' => $channelName]);
            abort(403);
        }

        // Extract vendor ID from channel name using simple approach
        // Formats: vendor.7 or private-vendor.7
        preg_match('/vendor\.(\d+)/', $channelName, $matches);
        $vendorId = $matches[1] ?? null;

        if (!$vendorId) {
            Log::error('Broadcasting: could not extract vendor ID', ['channel' => $channelName]);
            abort(403);
        }

        $user = auth()->user();
        $canAccess = $user->role === 'admin' || ($user->role === 'vendor' && (int) $user->id === (int) $vendorId);

        Log::info('Broadcasting auth result', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'vendor_id' => $vendorId,
            'can_access' => $canAccess,
        ]);

        if (!$canAccess) {
            Log::error('Broadcasting auth: access denied', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'vendor_id' => $vendorId,
            ]);
            abort(403);
        }

        $response = response()->json([
            'channel_data' => [
                'user_id' => $user->id,
                'user_info' => $user->name,
            ]
        ], 200);
        
        Log::info('Broadcasting auth: returning 200 response', [
            'status' => 200,
            'user_id' => $user->id,
        ]);
        
        return $response;
    }
}
