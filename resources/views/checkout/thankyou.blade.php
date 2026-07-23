<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>¡Gracias por tu compra! - Marketplace Bariloche</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon-arg.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#FDFDFC] antialiased flex flex-col min-h-screen">

    @include('partials.header')

    @php
        $paymentMethod = $checkout['payment_method'] ?? null;
        $paymentMethodLabel = $checkout['payment_method_label'] ?? 'tu método de pago';
        $orderId = $checkout['order_id'] ?? null;
        $total = $checkout['total'] ?? null;
        $shippingCost = $checkout['shipping_cost'] ?? null;
        $shippingZoneLabel = $checkout['shipping_zone_label'] ?? null;
        $subtotalProducts = $checkout['subtotal_products'] ?? null;
        $customerAddress = $checkout['address'] ?? null;
        $hasRaffle = (bool) ($checkout['has_raffle'] ?? false);
        $bankTransferConfigured =
            !empty($bankTransfer['account_holder']) ||
            !empty($bankTransfer['bank_name']) ||
            !empty($bankTransfer['alias']) ||
            !empty($bankTransfer['cbu']);
    @endphp

    <main class="flex-grow flex items-center justify-center px-6">
        <div class="max-w-2xl w-full bg-white p-10 rounded-3xl shadow-sm text-center">
            <div
                class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h1 class="text-3xl font-black uppercase tracking-tighter mb-4">¡Pedido Recibido!</h1>
            <p class="text-gray-600 mb-8">
                Gracias por tu compra. Registramos tu pedido correctamente y el método de pago elegido fue
                <strong>{{ $paymentMethodLabel }}</strong>.
            </p>

            @if ($orderId)
                <div class="mb-6 rounded-2xl border border-gray-200 p-5 text-left">
                    <p class="text-sm text-gray-500 uppercase tracking-wide mb-2">Detalle del pedido
                        #{{ $orderId }}</p>
                    @if (!empty($orderItems))
                        <div class="space-y-2 text-sm text-gray-700">
                            @foreach ($orderItems as $item)
                                <div class="rounded-xl border border-gray-100 px-3 py-2">
                                    <p class="font-semibold text-gray-900">{{ $item['name'] }}</p>
                                    <p class="mt-1">{{ $item['quantity'] }} x
                                        ${{ number_format($item['price'], 0, ',', '.') }} =
                                        ${{ number_format($item['subtotal'], 0, ',', '.') }}</p>
                                    @if (isset($item['raffle_number']) && !is_null($item['raffle_number']))
                                        <p class="mt-1 text-xs text-gray-500">Numero: {{ $item['raffle_number'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <div class="mt-3 space-y-1 text-sm text-gray-700">
                        @if (!is_null($subtotalProducts))
                            <p><strong>Subtotal productos:</strong> ${{ number_format($subtotalProducts, 0, ',', '.') }}
                            </p>
                        @endif
                        @if (!empty($customerAddress))
                            <p>
                                <strong>Dirección:</strong> {{ $customerAddress }}
                            </p>
                        @endif
                        @if ($total)
                            <p><strong>Total:</strong> ${{ number_format($total, 0, ',', '.') }}</p>
                        @endif
                    </div>
                </div>
            @endif

            @if ($paymentMethod === 'transferencia')
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 mb-8 text-left text-sm text-amber-950">
                    <h2 class="font-bold text-base mb-3">Datos para transferir</h2>
                    @if ($orderId || !is_null($total))
                        <div class="mb-4 rounded-2xl border border-amber-200 bg-white px-4 py-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-700">Paso 1</p>
                            <p class="mt-2 text-base font-semibold text-amber-950">Transferi el importe exacto de tu
                                pedido.</p>
                            @if (!is_null($total))
                                <p class="mt-2 text-3xl font-black tracking-tight text-amber-950">
                                    ${{ number_format($total, 0, ',', '.') }}</p>
                            @endif
                            @if ($orderId)
                                <p class="mt-2 text-sm text-amber-900">Referencia para identificar el pago:
                                    <strong>Pedido #{{ $orderId }}</strong>.</p>
                            @endif
                        </div>
                    @endif

                    @if ($bankTransferConfigured)
                        <p class="mb-3 text-sm text-amber-900">Usa cualquiera de estos datos bancarios para realizar la
                            transferencia:</p>
                    @endif
                    @if (!empty($bankTransfer['account_holder']))
                        <p class="mb-2"><strong>Titular:</strong> {{ $bankTransfer['account_holder'] }}</p>
                    @endif
                    @if (!empty($bankTransfer['bank_name']))
                        <p class="mb-2"><strong>Banco:</strong> {{ $bankTransfer['bank_name'] }}</p>
                    @endif
                    @if (!empty($bankTransfer['alias']))
                        <p class="mb-2"><strong>Alias:</strong> {{ $bankTransfer['alias'] }}</p>
                    @endif
                    @if (!empty($bankTransfer['cbu']))
                        <p class="mb-2"><strong>CBU:</strong> {{ $bankTransfer['cbu'] }}</p>
                    @endif
                    @if (!empty($bankTransfer['notes']))
                        <p class="mb-2">{{ $bankTransfer['notes'] }}</p>
                    @endif
                    @if (!$bankTransferConfigured && empty($bankTransfer['notes']))
                        <p>Tu pedido quedó registrado, pero faltan cargar los datos bancarios para mostrar acá las
                            instrucciones completas de transferencia.</p>
                    @endif

                    @if (!empty($bankTransfer['alias']) || !empty($bankTransfer['cbu']))
                        <div class="mt-4 flex flex-wrap gap-2">
                            @if (!empty($bankTransfer['alias']))
                                <button type="button"
                                    class="inline-flex items-center rounded-lg border border-amber-300 bg-white px-3 py-2 text-xs font-semibold text-amber-900 hover:bg-amber-100"
                                    data-copy-text="{{ $bankTransfer['alias'] }}">
                                    Copiar alias
                                </button>
                            @endif
                            @if (!empty($bankTransfer['cbu']))
                                <button type="button"
                                    class="inline-flex items-center rounded-lg border border-amber-300 bg-white px-3 py-2 text-xs font-semibold text-amber-900 hover:bg-amber-100"
                                    data-copy-text="{{ $bankTransfer['cbu'] }}">
                                    Copiar CBU
                                </button>
                            @endif
                        </div>
                    @endif
                </div>

                @if (!empty($whatsAppUrl) && $orderId)
                    <div class="mt-8 mb-8">
                        <div
                            class="mb-4 rounded-2xl border border-green-100 bg-green-50 px-4 py-4 text-left text-sm text-green-950">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-green-700">Paso 2</p>
                            <p class="mt-2 font-semibold">Despues de transferir, envia el comprobante por WhatsApp.</p>
                            <p class="mt-1 text-green-900">El mensaje ya sale armado con tu pedido #{{ $orderId }}
                                y el total para que solo adjuntes la captura o comprobante.</p>
                        </div>
                        <a href="{{ $whatsAppUrl }}" target="_blank" rel="noopener noreferrer"
                            class="inline-flex w-full justify-center items-center gap-3 rounded-xl bg-green-500 px-5 py-4 text-sm font-bold uppercase tracking-wide text-white hover:bg-green-600 shadow-lg hover:shadow-xl transition-all"
                            id="send-proof-whatsapp-btn">
                            <span
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/15 ring-1 ring-white/20">
                                <svg class="h-5 w-5" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                                    <path
                                        d="M13.601 2.326A7.854 7.854 0 0 0 8.021 0a7.854 7.854 0 0 0-6.687 11.976L0 16l4.169-1.328a7.894 7.894 0 0 0 3.853.995h.003a7.855 7.855 0 0 0 5.576-13.34ZM8.024 14.34h-.002a6.57 6.57 0 0 1-3.35-.92l-.24-.142-2.472.787.804-2.406-.156-.247a6.568 6.568 0 0 1-1.025-3.505c.001-3.626 2.957-6.582 6.588-6.582a6.57 6.57 0 0 1 4.648 1.928 6.56 6.56 0 0 1 1.928 4.648c-.001 3.626-2.957 6.582-6.583 6.582Z" />
                                    <path
                                        d="M6.228 4.951c-.228-.492-.467-.502-.688-.511l-.585-.01c-.2 0-.522.074-.794.372-.272.298-1.04 1.016-1.04 2.479 0 1.463 1.065 2.877 1.213 3.075.149.198 2.096 3.2 5.076 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.718 2.006-1.412.248-.694.248-1.289.174-1.413-.074-.124-.273-.198-.571-.347-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.149-.67.149-.198.297-.767.967-.94 1.164-.173.198-.347.223-.644.074-.297-.148-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.174-.297-.019-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.372-.025-.521-.074-.148-.669-1.611-.917-2.206Z" />
                                </svg>
                            </span>
                            Enviar comprobante por WhatsApp
                        </a>
                        <p class="mx-auto mt-3 max-w-xl text-xs leading-relaxed text-gray-600">La acreditacion del pago
                            se revisa manualmente desde administracion despues de recibir el comprobante.</p>
                    </div>
                @elseif ($orderId)
                    <div
                        class="mt-8 mb-8 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-left text-sm text-amber-950">
                        <p class="font-semibold">Paso 2</p>
                        <p class="mt-2">Cargá un numero en <strong>BANK_TRANSFER_WHATSAPP_NUMBER</strong> o
                            <strong>SUPPORT_WHATSAPP_NUMBER</strong> para mostrar el boton que permite enviar el
                            comprobante por WhatsApp.</p>
                    </div>
                @endif
            @elseif ($paymentMethod === 'efectivo')
                <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6 mb-8 text-left text-sm text-blue-950">
                    <h2 class="font-bold text-base mb-3">Pago en efectivo</h2>
                    <p>Tu pedido quedó reservado para abonarlo en efectivo al momento de la entrega.</p>
                    <p class="mt-2">Si necesitás coordinar algo adicional, te vamos a contactar usando los datos que
                        cargaste.</p>
                </div>
            @elseif ($paymentMethod === 'mercadopago')
                <div class="bg-green-50 border border-green-200 rounded-2xl p-6 mb-8 text-left text-sm text-green-950">
                    <h2 class="font-bold text-base mb-3">Pago aprobado</h2>
                    <p>Recibimos la confirmación de Mercado Pago y tu pedido ya quedó confirmado.</p>
                </div>
            @endif

            @if ($hasRaffle)
                <div
                    class="bg-indigo-50 border border-indigo-200 rounded-2xl p-6 mb-8 text-left text-sm text-indigo-950">
                    <h2 class="font-bold text-base mb-3">Regla de transparencia del sorteo</h2>
                    <p>Si no se venden los 100 numeros, el sorteo se realiza igual en la fecha anunciada y participan
                        solo los numeros vendidos.</p>
                    <p class="mt-2">Para garantizar ganador, si el primer numero oficial no fue vendido, se toma el
                        siguiente puesto oficial hasta encontrar un numero vendido.</p>
                </div>
            @endif

            @if (!empty($trackingUrl))
                <a href="{{ $trackingUrl }}"
                    class="inline-flex w-full justify-center items-center gap-3 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 transition-all text-white py-4 font-bold uppercase text-sm shadow-lg hover:shadow-xl mb-3"
                    id="track-order-btn">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/15 ring-1 ring-white/20 text-lg">
                        📍
                    </span>
                    Seguir mi pedido en tiempo real
                </a>
                <p class="text-xs text-gray-500 mb-6">Podrás ver la ubicación del repartidor en un mapa cuando esté en camino.</p>
            @endif

            <a href="{{ route('home') }}"
                class="inline-block w-full bg-black hover:bg-gradient-to-r hover:from-purple-600 hover:to-purple-700 transition-colors text-white py-4 rounded-xl font-bold uppercase text-sm">
                Volver a la tienda
            </a>
        </div>
    </main>

    <script>
        (() => {
            async function copyText(text, element) {
                try {
                    await navigator.clipboard.writeText(text);
                    const original = element.textContent;
                    element.textContent = 'Copiado';
                    setTimeout(() => {
                        element.textContent = original;
                    }, 1600);
                } catch (_) {
                    alert('No se pudo copiar. Copialo manualmente.');
                }
            }

            document.querySelectorAll('[data-copy-text]').forEach((button) => {
                button.addEventListener('click', () => {
                    copyText(button.getAttribute('data-copy-text') || '', button);
                });
            });
        })();
    </script>

</body>

</html>
