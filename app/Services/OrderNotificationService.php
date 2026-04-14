<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdminOrderNotification;

class OrderNotificationService
{
    public function notifyNewOrder(Order $order): void
    {
        if ($this->canSendTelegram()) {
            $this->sendTelegram($order);
            return;
        }

        if ($this->canSendSms()) {
            $this->sendSms($order);
            return;
        }

        if ($this->canSendEmail()) {
            $this->sendEmail($order);
            return;
        }

        Log::info('New order received, but no admin notification configuration was found.', [
            'order_id' => $order->id,
        ]);
    }

    protected function canSendTelegram(): bool
    {
        return !empty(config('services.telegram.bot_token'))
            && !empty(config('services.telegram.chat_id'));
    }

    protected function sendTelegram(Order $order): void
    {
        try {
            $url = sprintf(
                'https://api.telegram.org/bot%s/sendMessage',
                config('services.telegram.bot_token')
            );

            $body = sprintf(
                "✅ Nuevo pedido # %s confirmado\nCliente: %s\nEmail: %s\nTel: %s\nTotal: $%s",
                $order->id,
                $order->name,
                $order->email,
                $order->phone,
                number_format($order->total, 2, ',', '.')
            );

            Http::post($url, [
                'chat_id' => config('services.telegram.chat_id'),
                'text' => $body,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error sending Telegram notification for new order.', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function canSendSms(): bool
    {
        return !empty(config('services.twilio.sid'))
            && !empty(config('services.twilio.auth_token'))
            && !empty(config('services.twilio.from'))
            && !empty(config('services.twilio.admin_to'));
    }

    protected function sendSms(Order $order): void
    {
        try {
            $url = sprintf(
                'https://api.twilio.com/2010-04-01/Accounts/%s/Messages.json',
                config('services.twilio.sid')
            );

            $body = sprintf(
                "Nuevo pedido #%s: %s (%s) por $%s. Ver admin.",
                $order->id,
                $order->name,
                $order->phone,
                number_format($order->total, 2, ',', '.')
            );

            Http::withBasicAuth(config('services.twilio.sid'), config('services.twilio.auth_token'))
                ->asForm()
                ->post($url, [
                    'From' => config('services.twilio.from'),
                    'To' => config('services.twilio.admin_to'),
                    'Body' => $body,
                ]);
        } catch (\Throwable $e) {
            Log::error('Error sending Twilio notification for new order.', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function canSendEmail(): bool
    {
        return !empty(config('services.admin_notifications.email'));
    }

    protected function sendEmail(Order $order): void
    {
        try {
            Mail::to(config('services.admin_notifications.email'))
                ->send(new AdminOrderNotification($order));
        } catch (\Throwable $e) {
            Log::error('Error sending admin email notification for new order.', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
