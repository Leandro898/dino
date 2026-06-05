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

        // Accept vendor channels: vendor.7 or private-vendor.7
        if (strpos($channelName, 'vendor') !== false) {
            preg_match('/vendor\.(\d+)/', $channelName, $matches);
            $vendorId = $matches[1] ?? null;

            if (!$vendorId) {
                Log::error('Broadcasting: could not extract vendor ID', ['channel' => $channelName]);
                abort(403);
            }

            $user = auth()->user();
            $canAccess = $user->role === 'admin' || ($user->role === 'vendor' && (int) $user->id === (int) $vendorId);

            Log::info('Broadcasting auth vendor channel', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'vendor_id' => $vendorId,
                'can_access' => $canAccess,
            ]);

            if (!$canAccess) {
                abort(403);
            }

            return response()->json([
                'channel_data' => [
                    'user_id' => $user->id,
                    'user_info' => $user->name,
                ]
            ], 200);
        }

        // Accept user presence channels: private-App.Models.User.7
        if (strpos($channelName, 'App.Models.User') !== false || strpos($channelName, 'presence-') !== false) {
            // This is a presence or user channel - allow if authenticated
            // These are used by Filament/Laravel for real-time features
            Log::info('Broadcasting auth user channel', [
                'user_id' => auth()->id(),
                'channel' => $channelName,
            ]);

            return response()->json([
                'channel_data' => [
                    'user_id' => auth()->id(),
                    'user_info' => auth()->user()->name,
                ]
            ], 200);
        }

        // Unknown channel type
        Log::error('Broadcasting: unknown channel type', ['channel' => $channelName]);
        abort(403);
    }
}
