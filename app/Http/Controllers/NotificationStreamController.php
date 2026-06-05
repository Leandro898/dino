<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NotificationStreamController extends Controller
{
    public function stream(Request $request)
    {
        // Must be authenticated
        if (!auth()->check()) {
            abort(403);
        }

        $userId = auth()->id();
        $vendorId = auth()->user()->id; // Assuming vendor user ID

        // Create SSE stream response
        $response = new StreamedResponse(function () use ($userId, $vendorId) {
            // Set headers for SSE
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('Access-Control-Allow-Origin: *');

            // Send initial comment to establish connection
            echo ": connection established\n\n";
            flush();

            // Keep connection open for 30 minutes max
            $startTime = time();
            $timeout = 30 * 60; // 30 minutes

            while (time() - $startTime < $timeout) {
                // Check for new notifications every second
                $unreadNotifications = auth()->user()->unreadNotifications()->count();
                
                if ($unreadNotifications > 0) {
                    // Send notification count
                    echo "event: notification-count\n";
                    echo "data: " . json_encode([
                        'unread_count' => $unreadNotifications,
                    ]) . "\n\n";
                    flush();
                }

                // Check if there are new orders assigned to this vendor
                $newOrders = \App\Models\Order::where('status', 'assigned')
                    ->whereHas('items', function ($query) use ($vendorId) {
                        $query->whereHas('product', function ($q) use ($vendorId) {
                            $q->where('user_id', $vendorId);
                        });
                    })
                    ->where('updated_at', '>=', now()->subSecond(2))
                    ->get();

                if ($newOrders->isNotEmpty()) {
                    foreach ($newOrders as $order) {
                        echo "event: order-assigned\n";
                        echo "data: " . json_encode([
                            'order_id' => $order->id,
                            'total' => $order->total,
                            'status' => $order->status,
                        ]) . "\n\n";
                        flush();
                    }
                }

                // Sleep for 1 second before next check
                sleep(1);

                // Send heartbeat to keep connection alive
                echo ": heartbeat\n\n";
                flush();
            }

            // Connection timeout
            echo "event: connection-closed\n";
            echo "data: Connection timeout\n\n";
            flush();
        });

        // Set response headers for streaming
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no'); // Disable Nginx buffering

        return $response;
    }
}
