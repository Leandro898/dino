<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Nuevo pedido</title>
</head>
<body>
    <h1>Nuevo pedido recibido</h1>
    <p>Se ha registrado un nuevo pedido con los siguientes datos:</p>
    <ul>
        <li><strong>ID:</strong> {{ $order->id }}</li>
        <li><strong>Cliente:</strong> {{ $order->name }}</li>
        <li><strong>Email:</strong> {{ $order->email }}</li>
        <li><strong>Teléfono:</strong> {{ $order->phone }}</li>
        <li><strong>Total:</strong> ${{ number_format($order->total, 2, ',', '.') }}</li>
        <li><strong>Método de pago:</strong>
            @if ($order->payment_method === 'mercadopago')
                Mercado Pago
            @elseif ($order->payment_method === 'transferencia')
                Transferencia bancaria
            @elseif ($order->payment_method === 'efectivo')
                Efectivo al entregar
            @else
                {{ $order->payment_method }}
            @endif
        </li>
        <li><strong>Estado:</strong> {{ ucfirst($order->status) }}</li>
    </ul>
    <p>Verificar en el panel de administración para procesar el pedido.</p>
</body>
</html>
