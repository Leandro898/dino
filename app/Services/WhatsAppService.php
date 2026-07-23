<?php

namespace App\Services;

use App\Models\Order;

class WhatsAppService
{
    /**
     * Build the WhatsApp manual payment URL for an order.
     *
     * @param Order|array $order
     * @return string|null
     */
    public function buildManualPaymentUrl($order): ?string
    {
        $bankTransfer = config('services.bank_transfer');
        $supportWhatsApp = preg_replace('/\D+/', '', (string) (config('services.support.whatsapp_number') ?: ''));
        $whatsAppNumber = preg_replace('/\D+/', '', (string) ($bankTransfer['whatsapp_number'] ?? '')) ?: $supportWhatsApp;

        if (empty($whatsAppNumber)) {
            return null;
        }

        // Get properties whether it's an Order model or a session payload array
        $orderId = is_array($order) ? ($order['order_id'] ?? '') : $order->id;
        $total = is_array($order) ? ($order['total'] ?? 0) : $order->total;
        $name = is_array($order) ? ($order['name'] ?? '') : $order->name;
        $address = is_array($order) ? ($order['address'] ?? '') : $order->address;

        $totalFormatted = number_format((float) $total, 0, ',', '.');
        
        $message = sprintf(
            "Hola, envio el comprobante de transferencia del pedido #%s por $%s. Nombre: %s. Direccion: %s. Por favor confirmen la recepcion del pago.",
            $orderId,
            $totalFormatted,
            $name,
            $address
        );

        return 'https://wa.me/' . $whatsAppNumber . '?text=' . urlencode($message);
    }
}
