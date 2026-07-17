<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\CustomRequest;

class PushNotificationController extends Controller
{
    public function subscribe(Request $request)
    {
        $request->user()->updatePushSubscription(
            $request->endpoint,
            $request->keys['p256dh'] ?? null,
            $request->keys['auth'] ?? null
        );
        return response()->json(['success' => true]);
    }

    public function guestSubscribe(Request $request)
    {
        $sessionId = Session::getId();
        $customRequest = CustomRequest::where('session_id', $sessionId)
                                            ->whereIn('status', ['open', 'quoted'])
                                            ->first();
                                            
        if (!$customRequest) {
            $customRequest = CustomRequest::create([
                'session_id' => $sessionId,
                'status' => 'open',
            ]);
        }
        
        $customRequest->updatePushSubscription(
            $request->endpoint,
            $request->keys['p256dh'] ?? null,
            $request->keys['auth'] ?? null
        );
        
        return response()->json(['success' => true]);
    }
}
