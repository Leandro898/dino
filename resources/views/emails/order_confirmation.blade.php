<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de compra</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; background: #f6f6f6; margin: 0; padding: 0;">
    @php
        $hasRaffle = $order->items->contains(fn ($item) => !empty($item->raffle_number));
    @endphp
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 700px; margin: 0 auto; background: #ffffff; padding: 24px; border-radius: 8px;">
        <tr>
            <td style="text-align: center; padding-bottom: 24px;">
                <h1 style="margin: 0; font-size: 24px; color: #1a202c;">¡Gracias por tu compra!</h1>
            </td>
        </tr>
        <tr>
            <td style="padding-bottom: 16px;">
                <p>Hola <strong>{{ $order->name }}</strong>,</p>
                <p>
                    Tu pedido <strong>#{{ $order->id }}</strong> fue registrado correctamente.
                    @if ($order->payment_method === 'mercadopago')
                        El pago fue confirmado con Mercado Pago.
                    @elseif ($order->payment_method === 'transferencia')
                        Quedó pendiente de acreditación por transferencia.
                    @elseif ($order->payment_method === 'efectivo')
                        Quedó marcado para pagar en efectivo al entregar.
                    @endif
                    A continuación te compartimos los detalles:
                </p>
            </td>
        </tr>
        <tr>
            <td>
                <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse: collapse; margin-bottom: 16px;">
                    <thead>
                        <tr style="background: #f3f4f6;">
                            <th align="left">Producto</th>
                            <th align="center">Cantidad</th>
                            <th align="right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td>
                                    {{ optional($item->product)->name ?? 'Producto #' . $item->product_id }}
                                    @if (!empty($item->raffle_number))
                                        <br>
                                        <small>Numero de sorteo: {{ $item->raffle_number }}</small>
                                    @endif
                                </td>
                                <td align="center">{{ $item->quantity }}</td>
                                <td align="right">$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
        </tr>
        <tr>
            <td style="padding-bottom: 16px;">
                @if (!is_null($order->shipping_cost))
                    @php
                        $shippingZoneLabel = $shippingZones[$order->shipping_zone]['label'] ?? $order->shipping_zone;
                    @endphp
                    <p><strong>Subtotal productos:</strong> $ {{ number_format($order->total - $order->shipping_cost, 2, ',', '.') }}</p>
                    <p><strong>Envío{{ $shippingZoneLabel ? ' (' . $shippingZoneLabel . ')' : '' }}:</strong> $ {{ number_format($order->shipping_cost, 2, ',', '.') }}</p>
                @endif
                <p><strong>Total:</strong> $ {{ number_format($order->total, 2, ',', '.') }}</p>
                <p><strong>Método de pago:</strong>
                    @if ($order->payment_method === 'mercadopago')
                        Mercado Pago
                    @elseif ($order->payment_method === 'transferencia')
                        Transferencia bancaria
                    @elseif ($order->payment_method === 'efectivo')
                        Efectivo al entregar
                    @else
                        {{ $order->payment_method }}
                    @endif
                </p>
                <p><strong>Dirección de envío:</strong> {{ $order->address }}</p>
                <p><strong>Teléfono:</strong> {{ $order->phone }}</p>
            </td>
        </tr>
        @if ($hasRaffle)
            <tr>
                <td style="padding-bottom: 16px; background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 8px;">
                    <p style="margin: 0 0 8px 0;"><strong>Regla de transparencia del sorteo</strong></p>
                    <p style="margin: 0 0 6px 0;">Si no se venden los 100 numeros, el sorteo se realiza igual en la fecha anunciada y participan solo los numeros vendidos.</p>
                    <p style="margin: 0;">Para garantizar ganador, si el primer numero oficial no fue vendido, se toma el siguiente puesto oficial hasta encontrar un numero vendido.</p>
                </td>
            </tr>
        @endif
        @if ($order->payment_method === 'transferencia')
            <tr>
                <td style="padding-bottom: 16px; background: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px;">
                    <p style="margin: 0 0 8px 0;"><strong>Datos para transferir</strong></p>
                    @if (!empty($bankTransfer['account_holder']))
                        <p style="margin: 0 0 6px 0;"><strong>Titular:</strong> {{ $bankTransfer['account_holder'] }}</p>
                    @endif
                    @if (!empty($bankTransfer['bank_name']))
                        <p style="margin: 0 0 6px 0;"><strong>Banco:</strong> {{ $bankTransfer['bank_name'] }}</p>
                    @endif
                    @if (!empty($bankTransfer['alias']))
                        <p style="margin: 0 0 6px 0;"><strong>Alias:</strong> {{ $bankTransfer['alias'] }}</p>
                    @endif
                    @if (!empty($bankTransfer['cbu']))
                        <p style="margin: 0 0 6px 0;"><strong>CBU/CVU:</strong> {{ $bankTransfer['cbu'] }}</p>
                    @endif
                    @if (!empty($bankTransfer['notes']))
                        <p style="margin: 0;">{{ $bankTransfer['notes'] }}</p>
                    @endif
                </td>
            </tr>
        @endif
        <tr>
            <td style="padding-bottom: 16px;">
                <p>Si necesitas ayuda, respondé este email o visitá nuestro sitio web.</p>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 16px; border-top: 1px solid #e2e8f0; color: #718096; font-size: 14px;">
                <p style="margin: 0;">Ventas Baritienda</p>
                <p style="margin: 0;">https://baritienda.online</p>
            </td>
        </tr>
    </table>
</body>
</html>
