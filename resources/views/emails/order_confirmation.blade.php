<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de compra</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; background: #f6f6f6; margin: 0; padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 700px; margin: 0 auto; background: #ffffff; padding: 24px; border-radius: 8px;">
        <tr>
            <td style="text-align: center; padding-bottom: 24px;">
                <h1 style="margin: 0; font-size: 24px; color: #1a202c;">¡Gracias por tu compra!</h1>
            </td>
        </tr>
        <tr>
            <td style="padding-bottom: 16px;">
                <p>Hola <strong>{{ $order->name }}</strong>,</p>
                <p>Tu orden <strong>#{{ $order->id }}</strong> fue confirmada correctamente. A continuación te compartimos los detalles:</p>
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
                                <td>{{ optional($item->product)->name ?? 'Producto #' . $item->product_id }}</td>
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
                <p><strong>Total:</strong> $ {{ number_format($order->total, 2, ',', '.') }}</p>
                <p><strong>Dirección de envío:</strong> {{ $order->address }}</p>
                <p><strong>Teléfono:</strong> {{ $order->phone }}</p>
            </td>
        </tr>
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
