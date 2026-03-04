<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tu Carrito - Marketplace Bariloche</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] antialiased">

    @include('partials.header')

    <main class="max-w-7xl mx-auto px-6 py-10 lg:py-20">

        <h1 class="text-4xl font-black dark:text-white uppercase mb-10 tracking-tighter">
            Tu Carrito
        </h1>

        @if (session('cart') && count(session('cart')) > 0)

            @php $total = 0; @endphp

            <div class="grid lg:grid-cols-3 gap-10">

                <!-- TABLA PRODUCTOS -->
                <div class="lg:col-span-2 bg-white dark:bg-[#161615] rounded-2xl shadow-sm p-6">

                    <div
                        class="hidden md:grid grid-cols-6 font-bold text-sm uppercase tracking-widest text-gray-400 pb-4 border-b">
                        <div class="col-span-3">Producto</div>
                        <div>Precio</div>
                        <div>Cantidad</div>
                        <div>Subtotal</div>
                    </div>

                    @foreach (session('cart') as $id => $details)
                        @php
                            $subtotal = $details['price'] * $details['quantity'];
                            $total += $subtotal;
                        @endphp

                        <div class="grid grid-cols-1 md:grid-cols-6 items-center gap-4 py-6 border-b last:border-0">

                            <!-- PRODUCTO -->
                            <div class="md:col-span-3 flex items-center gap-4">

                                @if ($details['image'])
                                    <img src="{{ asset('storage/' . $details['image']) }}"
                                        class="w-20 h-20 object-cover rounded-xl">
                                @endif

                                <div>
                                    <h3 class="font-bold dark:text-white">
                                        {{ $details['name'] }}
                                    </h3>

                                    <form action="{{ route('cart.remove', $id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs text-gray-400 hover:text-red-500 mt-2">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- PRECIO -->
                            <div class="font-semibold">
                                ${{ number_format($details['price'], 0, ',', '.') }}
                            </div>

                            <!-- CANTIDAD -->
                            <div>
                                <span class="px-3 py-1 bg-gray-100 dark:bg-[#0f0f0f] rounded-lg text-sm">
                                    {{ $details['quantity'] }}
                                </span>
                            </div>

                            <!-- SUBTOTAL -->
                            <div class="font-bold">
                                ${{ number_format($subtotal, 0, ',', '.') }}
                            </div>

                        </div>
                    @endforeach

                </div>


                <!-- RESUMEN DEL PEDIDO -->
                <div class="bg-white dark:bg-[#161615] rounded-2xl shadow-sm p-6 h-fit">

                    <h2 class="text-xl font-bold mb-6 dark:text-white">
                        Resumen del pedido
                    </h2>

                    <div class="flex justify-between mb-4 text-sm">
                        <span>Subtotal</span>
                        <span>${{ number_format($total, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between mb-6 text-lg font-bold border-t pt-4 dark:text-white">
                        <span>Total</span>
                        <span>${{ number_format($total, 0, ',', '.') }}</span>
                    </div>

                    <a href="{{ route('checkout.index') }}"
                        class="block w-full text-center bg-black hover:bg-[#f53003] transition-colors text-white py-4 rounded-xl font-bold uppercase text-sm">
                        Finalizar Compra
                    </a>

                </div>

            </div>
        @else
            <div class="text-center py-20 border-2 border-dashed border-gray-200 rounded-3xl">
                <p class="text-gray-400 font-bold uppercase tracking-widest mb-6">
                    El carrito está vacío
                </p>

                <a href="{{ route('home') }}"
                    class="inline-block bg-black text-white px-8 py-4 rounded-xl font-bold uppercase text-sm">
                    Explorar productos
                </a>
            </div>

        @endif

    </main>

</body>

</html>
