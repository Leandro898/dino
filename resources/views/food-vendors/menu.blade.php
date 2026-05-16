<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $vendor->name }} - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 text-gray-900">
    @include('partials.header')

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header compacto del lugar -->
        <div class="mb-6">
            <a href="{{ route('food-vendors.index') }}"
                class="text-purple-600 hover:text-purple-700 font-semibold text-sm mb-2 inline-flex items-center gap-1">
                ← Volver a lugares
            </a>
            <h1 class="text-2xl font-bold text-gray-900">{{ $vendor->name }}</h1>
            <p class="text-gray-600 text-sm">{{ $products->total() }} menú disponible{{ $products->total() !== 1 ? 's' : '' }}</p>
        </div>

        <!-- Grid de productos/menú -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($products as $product)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                    <!-- Imagen del producto -->
                    <div class="aspect-square bg-gray-200 flex items-center justify-center overflow-hidden">
                        @if ($product->image)
                            <img src="{{ $product->image }}" alt="{{ $product->name }}"
                                class="w-full h-full object-cover hover:scale-110 transition duration-300">
                        @else
                            <div class="text-gray-400 text-center">
                                <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                                <span class="text-xs">Sin imagen</span>
                            </div>
                        @endif
                    </div>

                    <!-- Info del producto -->
                    <div class="p-4">
                        <h3 class="font-bold text-lg text-gray-900 mb-1">{{ $product->name }}</h3>

                        @if ($product->description)
                            <p class="text-gray-600 text-sm mb-3">{{ Str::limit($product->description, 80) }}</p>
                        @endif

                        <div class="flex items-center justify-between">
                            <span
                                class="text-purple-600 font-bold text-lg">${{ number_format($product->price, 0, ',', '.') }}</span>
                            <a href="{{ route('products.show', $product->slug) }}"
                                class="bg-purple-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-purple-700 transition">
                                Pedir
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500 text-lg">No hay productos disponibles</p>
                    <a href="{{ route('food-vendors.index') }}"
                        class="text-purple-600 hover:text-purple-700 font-semibold mt-4 inline-block">
                        Volver a lugares
                    </a>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $products->links() }}
        </div>
    </main>
</body>

</html>
