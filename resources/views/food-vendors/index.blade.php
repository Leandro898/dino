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
                <p class="text-gray-600 mt-2">Descubre nuestros lugares de comida favoritos</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($vendors as $vendor)
                <a href="{{ route('food-vendors.show', $vendor) }}"
                    class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition transform hover:scale-105">
                    <!-- Imagen de portada del lugar -->
                    <div class="aspect-video bg-gradient-to-br from-purple-400 to-orange-400 flex items-center justify-center relative">
                        @if ($vendor->products->first()?->image)
                            <img src="{{ $vendor->products->first()->image }}" alt="{{ $vendor->name }}"
                                class="w-full h-full object-cover">
                        @else
                            <div class="text-white text-center">
                                <svg class="w-16 h-16 mx-auto mb-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m0 0h6m-6 0h-6">
                                    </path>
                                </svg>
                                <span class="text-sm font-semibold">{{ $vendor->products->count() }} productos</span>
                            </div>
                        @endif
                    </div>

                    <!-- Info del lugar -->
                    <div class="p-4">
                        <h3 class="font-bold text-xl text-gray-900 mb-1">{{ $vendor->name }}</h3>
                        <p class="text-gray-600 text-sm mb-3">
                            {{ $vendor->products->count() }}
                            {{ $vendor->products->count() === 1 ? 'producto' : 'productos' }} disponibles
                        </p>

                        <!-- Muestra un preview de 3 primeros productos -->
                        <div class="flex gap-2 mb-4">
                            @foreach ($vendor->products->take(3) as $product)
                                <span
                                    class="inline-block bg-purple-100 text-purple-700 text-xs px-2 py-1 rounded truncate">
                                    {{ $product->name }}
                                </span>
                            @endforeach
                        </div>

                        <button
                            class="w-full bg-purple-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-purple-700 transition">
                            Ver menú
                        </button>
                    </div>
                </a>
            @empty
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500 text-lg">Aún no hay lugares de comida disponibles</p>
                    <a href="{{ route('home.mic') }}"
                        class="text-purple-600 hover:text-purple-700 font-semibold mt-4 inline-block">
                        Volver a home
                    </a>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $vendors->links() }}
        </div>
    </main>
</body>

</html>
