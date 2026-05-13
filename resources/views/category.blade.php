<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $categoryName }} - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 text-gray-900">
    @include('partials.header')

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <a href="{{ route('home.mic') }}"
                    class="text-purple-600 hover:text-purple-700 font-semibold text-sm mb-3 inline-flex items-center gap-1">
                    ← Volver a home
                </a>
                <h1 class="text-4xl font-bold text-gray-900">{{ $categoryName }}</h1>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($products as $product)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                    <div class="aspect-square bg-gray-200 flex items-center justify-center">
                        @if ($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                class="w-full h-full object-cover">
                        @else
                            <span class="text-gray-400">Sin imagen</span>
                        @endif
                    </div>
                    <div class="p-4">
                        <h3 class="font-bold text-lg text-gray-900 mb-2">{{ $product->name }}</h3>
                        <p class="text-gray-600 text-sm mb-4">{{ Str::limit($product->description, 60) }}</p>
                        <div class="flex items-center justify-between">
                            <span
                                class="text-purple-600 font-bold text-lg">${{ number_format($product->adjusted_price, 2, ',', '.') }}</span>
                            <a href="{{ route('products.show', $product->slug) }}"
                                class="bg-purple-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-purple-700 transition">
                                Ver
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500 text-lg">No hay productos en esta categoría</p>
                    <a href="{{ route('home.mic') }}"
                        class="text-purple-600 hover:text-purple-700 font-semibold mt-4 inline-block">
                        Volver a home
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
