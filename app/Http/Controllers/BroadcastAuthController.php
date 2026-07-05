<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BroadcastAuthController extends Controller
{
    public function auth(Request $request)
    {
        $channelName = $request->get('channel_name');
        
        Log::info('Broadcasting auth request', [
            'user_id' => auth()->id(),
            'authenticated' => auth()->check(),
            'channel' => $channelName,
            'request_path' => $request->path(),
        ]);

        // Accept public orders channel - no authentication required
        if ($channelName === 'orders') {
            Log::info('Broadcasting auth orders channel', [
                'user_id' => auth()->id(),
                'channel' => $channelName,
            ]);

            return response()->json([
                'channel_data' => [
                    'user_id' => auth()->id() ?? 'guest',
                    'user_info' => auth()->user()?->name ?? 'Guest',
                ]
            ], 200);
        }

        // Handle Filament presence channels: private-App.Models.User.{userId}
        if (strpos($channelName, 'private-App.Models.User') !== false) {
            // Extract user ID from channel name
            preg_match('/private-App\.Models\.User\.(\d+)/', $channelName, $matches);
            $userId = $matches[1] ?? null;

            if (!$userId) {
                Log::error('Broadcasting: could not extract user ID', ['channel' => $channelName]);
                return response()->json(['error' => 'Invalid channel'], 403);
            }

            // Check if authenticated user is the same as the channel user ID
            $currentUserId = auth()->id();
            $canAccess = $currentUserId && (int) $currentUserId === (int) $userId;

            Log::info('Broadcasting auth Filament presence channel', [
                'current_user_id' => $currentUserId,
                'channel_user_id' => $userId,
                'can_access' => $canAccess,
            ]);

            if (!$canAccess) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            return response()->json([
                'channel_data' => [
                    'user_id' => $currentUserId,
                    'user_info' => auth()->user()->name,
                ]
            ], 200);
        }

        // Accept vendor channels: vendor.{vendorId} or private-vendor.{vendorId}
        if (strpos($channelName, 'vendor') !== false) {
            preg_match('/vendor\.(\d+)|private-vendor\.(\d+)/', $channelName, $matches);
            $vendorId = $matches[1] ?? $matches[2] ?? null;

            if (!$vendorId) {
                Log::error('Broadcasting: could not extract vendor ID', ['channel' => $channelName]);
                return response()->json(['error' => 'Invalid channel'], 403);
            }

            if (!auth()->check()) {
                return response()->json(['error' => 'Unauthenticated'], 403);
            }

            $user = auth()->user();
            $canAccess = $user->role === 'admin' || ($user->role === 'vendor' && (int) $user->id === (int) $vendorId);

            Log::info('Broadcasting auth vendor channel', [
                'user_id' => $user->id,
                'user_role' => $user->role ?? 'unknown',
                'vendor_id' => $vendorId,
                'can_access' => $canAccess,
            ]);

            if (!$canAccess) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            return response()->json([
                'channel_data' => [
                    'user_id' => $user->id,
                    'user_info' => $user->name,
                ]
            ], 200);
        }

        // Unknown channel type
        Log::error('Broadcasting: unknown channel type', [
            'channel' => $channelName,
            'user_id' => auth()->id(),
        ]);
        return response()->json(['error' => 'Unknown channel'], 403);
    }
}
