<?php

namespace App\Services;

use App\Models\Order;
use App\Models\SupportMessage;
use App\Models\User;
use App\Events\OrderStatusUpdated;
use App\Events\RiderLocationUpdated;
use App\Events\SupportMessageSent;
use Illuminate\Support\Facades\Log;

class DeliveryService
{
    /**
     * Get the latest active order for a delivery user.
     */
    public function getLatestOrderForRider(User $rider): ?Order
    {
        return Order::query()
            ->with('vendor')
            ->where('delivery_user_id', $rider->id)
            ->whereIn('status', ['pending', 'assigned', 'processing', 'shipped'])
            ->latest('id')
            ->first();
    }

    /**
     * Accept an order assigned to the rider.
     */
    public function acceptOrder(Order $order, User $rider): bool
    {
        if ($order->delivery_user_id !== $rider->id) {
            return false;
        }

        return $order->update([
            'is_accepted_by_rider' => true,
        ]);
    }

    /**
     * Reject an order assigned to the rider.
     */
    public function rejectOrder(Order $order, User $rider): bool
    {
        if ($order->delivery_user_id !== $rider->id) {
            return false;
        }

        return $order->update([
            'delivery_user_id' => null,
            'is_accepted_by_rider' => false,
        ]);
    }

    /**
     * Get and mark as read all support messages for a rider.
     */
    public function getMessagesForRider(User $rider)
    {
        SupportMessage::where('delivery_user_id', $rider->id)
            ->where('sender_id', '!=', $rider->id)
            ->where('is_read_by_delivery', false)
            ->update(['is_read_by_delivery' => true]);

        return SupportMessage::with('sender')
            ->where('delivery_user_id', $rider->id)
            ->oldest()
            ->get();
    }

    /**
     * Send a new support message from the rider.
     */
    public function sendSupportMessage(User $rider, string $messageText): SupportMessage
    {
        $message = SupportMessage::create([
            'delivery_user_id' => $rider->id,
            'sender_id' => $rider->id,
            'message' => $messageText,
            'is_read_by_admin' => false,
            'is_read_by_delivery' => true,
        ]);

        $message->load('sender');

        try {
            broadcast(new SupportMessageSent($message))->toOthers();
        } catch (\Exception $e) {
            Log::error('Error broadcasting support message:', ['error' => $e->getMessage()]);
        }

        return $message;
    }

    /**
     * Update the rider's location and broadcast it to the current order if active.
     */
    public function updateLocationAndBroadcast(User $rider, float $latitude, float $longitude): void
    {
        $rider->update([
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);

        $activeOrder = Order::query()
            ->where('delivery_user_id', $rider->id)
            ->whereIn('status', ['shipped', 'processing', 'assigned'])
            ->orderByDesc('id')
            ->first();

        if ($activeOrder) {
            try {
                broadcast(new RiderLocationUpdated(
                    $activeOrder->id,
                    $latitude,
                    $longitude,
                    $rider->id
                ));
            } catch (\Exception $e) {
                Log::error('Error broadcasting rider location:', ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Update order status and broadcast the change.
     */
    public function updateOrderStatus(Order $order, User $rider, string $status): bool
    {
        if ($order->delivery_user_id !== $rider->id) {
            return false;
        }

        $order->update(['status' => $status]);

        try {
            broadcast(new OrderStatusUpdated($order, $status));
        } catch (\Exception $e) {
            Log::error('Error broadcasting order status:', ['error' => $e->getMessage()]);
        }

        return true;
    }
}
