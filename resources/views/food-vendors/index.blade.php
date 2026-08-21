<x-front-layout title="{{ $categoryName }} - {{ config('app.name') }}">
    <main class="max-md:pb-[calc(9rem+env(safe-area-inset-bottom))] max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-10">
        <div class="flex items-center justify-between mb-8">
            <div>
                <a href="{{ route('home') }}"
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
                    <div class="aspect-video bg-white border-b border-gray-150 flex items-center justify-center relative overflow-hidden">
                        @if ($vendor->banner)
                            <img src="{{ $vendor->banner_url }}" alt="{{ $vendor->name }}"
                                style="max-height: 100%; max-width: 100%; object-fit: contain; padding: 8px;" loading="lazy">
                        @elseif ($vendor->products->first()?->image)
                            <img src="{{ $vendor->products->first()->image_src }}" alt="{{ $vendor->name }}"
                                class="w-full h-full object-cover" loading="lazy">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-purple-400 to-orange-400 flex items-center justify-center text-white text-center">
                                <div>
                                    <svg class="w-12 h-12 mx-auto mb-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m0 0h6m-6 0h-6">
                                        </path>
                                    </svg>
                                    <span class="text-xs font-semibold">{{ $vendor->products->count() }} productos</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Info del lugar -->
                    <div class="p-4">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <h3 class="font-bold text-xl text-gray-900 truncate">{{ $vendor->name }}</h3>
                            @if ($vendor->isOpen())
                                <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-500 text-white">
                                    Abierto
                                </span>
                            @else
                                <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-500 text-white">
                                    Cerrado
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mb-2">
                            Horario: {{ $vendor->formatted_opening_hours }}
                        </p>
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
                    <a href="{{ route('home') }}"
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
</x-front-layout>
