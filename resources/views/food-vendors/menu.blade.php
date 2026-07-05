<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $vendor->name }} - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] antialiased">
    @include('partials.header')

    <main class="pb-[calc(8rem+env(safe-area-inset-bottom))] md:pb-10">
        
        <!-- HEADER DEL COMERCIO -->
        <div class="bg-white dark:bg-[#161615] border-b border-gray-200 dark:border-[#2a2a2a] sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-6 py-4">
                <div class="flex items-center justify-between mb-4">
                    <a href="{{ route('food-vendors.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-purple-600 transition">
                        ← Volver
                    </a>
                    <div class="text-center flex-1">
                        <h1 class="text-2xl font-black text-[#1b1b18] dark:text-white">{{ $vendor->name }}</h1>
                    </div>
                    <div class="w-12"></div>
                </div>


            </div>
        </div>

        <!-- GRID DE PRODUCTOS TIPO PEDIDOSYA -->
        <div class="max-w-7xl mx-auto px-6 py-8">

            <!-- Grid 2 columnas en mobile/tablet, 3 en desktop -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8" id="products-grid">
                @forelse ($products as $product)
                    <a href="{{ route('products.show', $product->slug) }}" 
                        class="product-card group bg-white dark:bg-[#161615] rounded-2xl shadow-md hover:shadow-xl overflow-hidden transition-all duration-300 hover:border-purple-500 border border-gray-200 dark:border-[#2a2a2a]">
                        
                        <!-- Contenedor de imagen con badges -->
                        <div class="relative aspect-video bg-gradient-to-br from-gray-200 to-gray-300 dark:from-[#2a2a2a] dark:to-[#1a1a1a] overflow-hidden">
                            @if ($product->image)
                                <img src="{{ $product->image_src }}" alt="{{ $product->name }}"
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif

                            <!-- Badges de promo/nuevo -->
                            @if (rand(0, 2) === 0)
                                <div class="absolute top-3 left-3 bg-red-500 text-white px-3 py-1 rounded-full text-xs font-bold">
                                    🔥 OFERTA
                                </div>
                            @endif

                            @if ($product->created_at->diffInDays(now()) < 3)
                                <div class="absolute top-3 right-3 bg-green-500 text-white px-3 py-1 rounded-full text-xs font-bold">
                                    ✨ NUEVO
                                </div>
                            @endif
                        </div>

                        <!-- Contenido -->
                        <div class="p-4 md:p-5">
                            <!-- Nombre y descripción -->
                            <h3 class="font-black text-lg md:text-xl text-[#1b1b18] dark:text-white mb-2 line-clamp-2 group-hover:text-purple-600 transition">
                                {{ $product->name }}
                            </h3>

                            @if ($product->description)
                                <p class="text-gray-600 dark:text-gray-400 text-sm mb-3 line-clamp-2">
                                    {{ $product->description }}
                                </p>
                            @endif

                            <!-- Precio prominente + Botón -->
                            <div class="flex items-end justify-between gap-3 pt-3 border-t border-gray-200 dark:border-[#2a2a2a]">
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Desde</div>
                                    <div class="text-3xl font-black text-purple-600">
                                        ${{ number_format($product->price, 0, ',', '.') }}
                                    </div>
                                </div>
                                <button onclick="addToCart(event, '{{ route('cart.add', $product->id) }}')" 
                                    class="bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white px-4 md:px-6 py-3 md:py-4 rounded-xl text-sm md:text-base font-black cursor-pointer select-none transition-all shadow-lg hover:shadow-purple-500/50 transform hover:scale-105 whitespace-nowrap">
                                    + PEDIR
                                </button>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full text-center py-16">
                        <svg class="w-20 h-20 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-gray-500 text-lg font-semibold mb-6">No hay productos disponibles</p>
                        <a href="{{ route('food-vendors.index') }}"
                            class="inline-block bg-purple-600 hover:bg-purple-700 text-white px-8 py-3 rounded-xl font-bold transition">
                            Volver a comercios
                        </a>
                    </div>
                @endforelse
            </div>

            <!-- Paginación -->
            @if ($products->hasPages())
                <div class="flex justify-center mt-12 mb-8">
                    {{ $products->links() }}
                </div>
            @endif
        </div>

    </main>

    <script>
        // Agregar al carrito (sin recargar)
        function addToCart(event, url) {
            event.preventDefault();
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({quantity: 1})
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.href = '{{ route('checkout.index') }}';
                }
            });
        }
    </script>

    <style>
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</body>

</html>
