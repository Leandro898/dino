<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Events\NewOrderForVendor;
use App\Events\NotifyVendorBroadcast;
use App\Notifications\VendorOrderAssignedNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdminOrderNotification;
use App\Mail\VendorOrderAssigned;
use Illuminate\Support\Collection;

class OrderNotificationService
{
    /**
     * Notifica al vendedor cuando el admin confirma el pedido.
     */
    public function notifyVendorOrderAssigned(Order $order): void
    {
        $vendors = $this->getVendorRecipients($order);

        if ($vendors->isEmpty()) {
            Log::info('No se encontraron vendedores para notificar el pedido asignado.', [
                'order_id' => $order->id,
            ]);

            return;
        }

        foreach ($vendors as $vendor) {
            if ($this->vendorAlreadyNotified($vendor, $order)) {
                continue;
            }

            Log::info('Starting vendor notification', [
                'order_id' => $order->id,
                'vendor_id' => $vendor->id,
            ]);

            $vendor->notify(new VendorOrderAssignedNotification($order));
            Log::info('Database notification sent');

            Log::info('Broadcasting NewOrderForVendor event', [
                'order_id' => $order->id,
                'vendor_id' => $vendor->id,
                'broadcast_connection' => config('broadcasting.default'),
            ]);

            event(new NewOrderForVendor($order, $vendor->id));
            Log::info('NewOrderForVendor event dispatched');

            event(new NotifyVendorBroadcast($order, $vendor->id));
            Log::info('NotifyVendorBroadcast event dispatched');

            if (!empty($vendor->email)) {
                try {
                    Mail::to($vendor->email)->send(new VendorOrderAssigned($order));
                } catch (\Throwable $e) {
                    Log::error('Error enviando notificación de pedido asignado al vendedor.', [
                        'order_id' => $order->id,
                        'vendor_id' => $vendor->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    protected function getVendorRecipients(Order $order): Collection
    {
        $order->loadMissing('items.product.user');

        return $order->items
            ->pluck('product.user')
            ->filter(fn (?User $vendor): bool => $vendor instanceof User && $vendor->role === 'vendor')
            ->unique('id')
            ->values();
    }

    protected function vendorAlreadyNotified(User $vendor, Order $order): bool
    {
        return $vendor->notifications()
            ->where('type', VendorOrderAssignedNotification::class)
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.order_id')) = ?", [$order->id])
            ->exists();
    }

    public function notifyNewOrder(Order $order): void
    {
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

    public function notifyMercadoPagoApprovedPayment(Order $order): void
    {
        if (!$this->canSendTelegram()) {
            Log::info('Mercado Pago payment approved, but Telegram is not configured.', [
                'order_id' => $order->id,
            ]);

            return;
        }

        $this->sendTelegramMercadoPagoApproved($order);
    }

    protected function canSendTelegram(): bool
    {
        return !empty(config('services.telegram.bot_token'))
            && !empty(config('services.telegram.chat_id'));
    }

    protected function sendTelegramMercadoPagoApproved(Order $order): void
    {
        try {
            $url = sprintf(
                'https://api.telegram.org/bot%s/sendMessage',
                config('services.telegram.bot_token')
            );

            $body = sprintf(
                "✅ Pago aprobado en Mercado Pago\nPedido: #%s\nCliente: %s\nEmail: %s\nTel: %s\nTotal: $%s",
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
            Log::error('Error sending Telegram notification for approved Mercado Pago payment.', [
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

