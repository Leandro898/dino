<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Cache;

class NotificationStreamController extends Controller
{
    public function stream(Request $request)
    {
        // Must be authenticated
        if (!auth()->check()) {
            abort(403);
        }

        $vendorId = auth()->id();
        $lastEventId = $request->header('Last-Event-ID', 0);

        // Create SSE stream response
        $response = new StreamedResponse(function () use ($vendorId, $lastEventId) {
            // Set headers for SSE
            header('Content-Type: text/event-stream; charset=utf-8');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('Access-Control-Allow-Origin: *');
            header('X-Accel-Buffering: no'); // Disable Nginx buffering

            // Send initial comment to establish connection
            echo ": connection established\n\n";
            flush();

            // Keep connection open for 1 hour
            $startTime = time();
            $timeout = 60 * 60; // 60 minutes
            $eventCounter = intval($lastEventId);

            while (time() - $startTime < $timeout) {
                // Check for pending notifications in cache
                $cacheKey = "vendor_orders_pending:{$vendorId}";
                $pendingOrders = Cache::get($cacheKey, []);

                if (is_array($pendingOrders) && !empty($pendingOrders)) {
                    // Send each pending order as an event
                    foreach ($pendingOrders as $orderData) {
                        $eventCounter++;
                        echo "id: {$eventCounter}\n";
                        echo "event: order-assigned\n";
                        echo "data: " . json_encode($orderData) . "\n\n";
                        flush();
                    }

                    // Clear the pending orders from cache after sending
                    Cache::forget($cacheKey);
                }

                // Check notification count
                $unreadNotifications = auth()->user()->unreadNotifications()->count();
                
                if ($unreadNotifications > 0) {
                    $eventCounter++;
                    echo "id: {$eventCounter}\n";
                    echo "event: notification-count\n";
                    echo "data: " . json_encode([
                        'unread_count' => $unreadNotifications,
                    ]) . "\n\n";
                    flush();
                }

                // Sleep for 0.5 seconds before next check (more responsive)
                usleep(500000); // 500ms

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
        $response->headers->set('Content-Type', 'text/event-stream; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }
}
