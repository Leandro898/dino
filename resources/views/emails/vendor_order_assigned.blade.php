<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Nuevo pedido asignado</title>
</head>
<body>
    <h1>¡Tienes un nuevo pedido asignado!</h1>
    <p>Se te ha asignado el siguiente pedido para procesar:</p>
    <ul>
        <li><strong>ID:</strong> {{ $order->id }}</li>
        <li><strong>Cliente:</strong> {{ $order->name }}</li>
        <li><strong>Email:</strong> {{ $order->email }}</li>
        <li><strong>Teléfono:</strong> {{ $order->phone }}</li>
        <li><strong>Total:</strong> ${{ number_format($order->total, 2, ',', '.') }}</li>
        <li><strong>Método de pago:</strong> {{ $order->payment_method }}</li>
        <li><strong>Dirección:</strong> {{ $order->address }}</li>
    </ul>
    <p>Por favor, ingresa al panel para ver los detalles y comenzar a preparar el pedido.</p>
</body>
</html>
