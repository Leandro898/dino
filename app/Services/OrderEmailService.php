<?php

namespace App\Services;

use App\Mail\OrderConfirmation;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderEmailService
{
    public function sendOrderConfirmation(Order $order): void
    {
        if (empty($order->email)) {
            Log::warning('Order does not have an email address for confirmation', [
                'order_id' => $order->id,
            ]);

            return;
        }

        try {
            $order->loadMissing('items.product');

            Mail::to($order->email)
                ->send(new OrderConfirmation($order));
        } catch (\Throwable $exception) {
            Log::error('Failed to send order confirmation email', [
                'order_id' => $order->id,
                'email' => $order->email,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
