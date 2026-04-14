<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout - Marketplace Bariloche</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] antialiased">

    @include('partials.header')

    <main class="max-w-7xl mx-auto px-6 py-10 lg:py-20">

        <h1 class="text-4xl font-black dark:text-white uppercase mb-10 tracking-tighter">
            Checkout
        </h1>

        @php $total = 0; @endphp

        <!-- mostrar errores -->
        @if ($errors->any())
            <div class="bg-red-500 text-white p-4 rounded-xl mb-6">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-500 text-white p-4 rounded-xl mb-6">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('checkout.process') }}" method="POST">
            @csrf

            <div class="grid lg:grid-cols-3 gap-10">

                <!-- DATOS DEL COMPRADOR -->
                <div class="lg:col-span-2 space-y-8">

                    <div class="bg-white dark:bg-[#161615] p-6 rounded-2xl shadow-sm">

                        <h2 class="text-xl font-bold mb-6 dark:text-white">
                            Datos de contacto
                        </h2>

                        <div class="grid md:grid-cols-2 gap-6">

                            <div>
                                <label class="text-sm font-semibold">Nombre completo</label>
                                <input type="text" name="name" value="{{ old('name') }}" required
                                    class="w-full mt-2 p-3 rounded-xl border border-gray-200 dark:border-[#2a2a2a] dark:bg-[#0f0f0f]">
                            </div>

                            <div>
                                <label class="text-sm font-semibold">Email</label>
                                <input type="email" name="email" value="{{ old('email') }}" required
                                    class="w-full mt-2 p-3 rounded-xl border border-gray-200 dark:border-[#2a2a2a] dark:bg-[#0f0f0f]">
                            </div>

                            <div class="md:col-span-2">
                                <label class="text-sm font-semibold">Dirección</label>
                                <input type="text" name="address" value="{{ old('address') }}" required
                                    class="w-full mt-2 p-3 rounded-xl border border-gray-200 dark:border-[#2a2a2a] dark:bg-[#0f0f0f]">
                            </div>

                            <div>
                                <label class="text-sm font-semibold">Teléfono</label>
                                <input type="text" name="phone" value="{{ old('phone') }}" required
                                    class="w-full mt-2 p-3 rounded-xl border border-gray-200 dark:border-[#2a2a2a] dark:bg-[#0f0f0f]">
                            </div>

                        </div>

                    </div>

                    <div class="bg-white dark:bg-[#161615] p-6 rounded-2xl shadow-sm">

                        <h2 class="text-xl font-bold mb-6 dark:text-white">
                            Método de pago
                        </h2>

                        <div class="space-y-4">
                            <label class="flex items-start gap-4 p-4 rounded-2xl border border-gray-200 dark:border-[#2a2a2a] cursor-pointer">
                                <input type="radio" name="payment_method" value="mercadopago" class="mt-1"
                                    {{ old('payment_method', 'mercadopago') === 'mercadopago' ? 'checked' : '' }}>
                                <span>
                                    <span class="block font-semibold dark:text-white">Mercado Pago</span>
                                    <span class="block text-sm text-gray-500 dark:text-gray-400">Pagás online en el momento y la orden queda confirmada cuando Mercado Pago aprueba el pago.</span>
                                </span>
                            </label>

                            <label class="flex items-start gap-4 p-4 rounded-2xl border border-gray-200 dark:border-[#2a2a2a] cursor-pointer">
                                <input type="radio" name="payment_method" value="efectivo" class="mt-1"
                                    {{ old('payment_method') === 'efectivo' ? 'checked' : '' }}>
                                <span>
                                    <span class="block font-semibold dark:text-white">Efectivo al entregar</span>
                                    <span class="block text-sm text-gray-500 dark:text-gray-400">Reservamos tu pedido y lo abonás en efectivo cuando te lo entreguemos.</span>
                                </span>
                            </label>

                            <label class="flex items-start gap-4 p-4 rounded-2xl border border-gray-200 dark:border-[#2a2a2a] cursor-pointer">
                                <input type="radio" name="payment_method" value="transferencia" class="mt-1"
                                    {{ old('payment_method') === 'transferencia' ? 'checked' : '' }}>
                                <span>
                                    <span class="block font-semibold dark:text-white">Transferencia</span>
                                    <span class="block text-sm text-gray-500 dark:text-gray-400">Generamos el pedido y luego te mostramos los datos para transferir desde cualquier app bancaria o billetera.</span>
                                </span>
                            </label>
                        </div>

                    </div>

                </div>


                <!-- RESUMEN DEL PEDIDO -->
                <div class="bg-white dark:bg-[#161615] rounded-2xl shadow-sm p-6 h-fit">

                    <h2 class="text-xl font-bold mb-6 dark:text-white">
                        Tu pedido
                    </h2>

                    @if (session('cart'))

                        @foreach (session('cart') as $id => $details)
                            @php
                                $subtotal = $details['price'] * $details['quantity'];
                                $total += $subtotal;
                            @endphp

                            <div class="flex justify-between text-sm mb-3">
                                <span>
                                    {{ $details['name'] }} x{{ $details['quantity'] }}
                                </span>
                                <span>
                                    ${{ number_format($subtotal, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach

                        <div class="flex justify-between mt-6 pt-4 border-t text-lg font-bold dark:text-white">
                            <span>Total</span>
                            <span>${{ number_format($total, 0, ',', '.') }}</span>
                        </div>

                        <button type="submit"
                            class="w-full mt-8 bg-black hover:bg-gradient-to-r hover:from-purple-600 hover:to-purple-700 transition-colors text-white py-4 rounded-xl font-bold uppercase text-sm">
                            Confirmar pedido
                        </button>

                    @endif

                </div>

            </div>

        </form>

    </main>

</body>

</html>
