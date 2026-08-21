<x-front-layout title="{{ $vendor->name }} - {{ config('app.name') }}" bodyClass="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] antialiased">
    <main class="pb-[calc(8rem+env(safe-area-inset-bottom))] md:pb-10">
        <!-- BANNER DEL COMERCIO -->
        @if ($vendor->banner)
            <div class="w-full relative bg-white dark:bg-[#111] border-b border-gray-200 dark:border-[#2a2a2a] flex justify-center items-center overflow-hidden" style="height: 220px;">
                <!-- Botón Volver -->
                <div class="absolute top-4 left-6 z-10">
                    <a href="{{ route('food-vendors.index') }}" class="bg-black/50 hover:bg-black/75 text-white px-4 py-2 rounded-full text-sm font-semibold backdrop-blur-sm transition inline-flex items-center gap-1">
                        ← Volver
                    </a>
                </div>

                <img src="{{ $vendor->banner_url }}" alt="{{ $vendor->name }} Banner" style="max-height: 100%; max-width: 100%; object-fit: contain; padding: 16px;">
            </div>
        @else
            <!-- Si no hay banner, mostramos un fondo degradado con el botón Volver -->
            <div class="w-full relative bg-gradient-to-r from-purple-600 to-indigo-750 flex justify-center items-center overflow-hidden" style="height: 140px;">
                <!-- Botón Volver -->
                <div class="absolute top-4 left-6 z-10">
                    <a href="{{ route('food-vendors.index') }}" class="bg-black/30 hover:bg-black/50 text-white px-4 py-2 rounded-full text-sm font-semibold backdrop-blur-sm transition inline-flex items-center gap-1">
                        ← Volver
                    </a>
                </div>
                
                <h2 class="text-white text-3xl font-black">{{ $vendor->name }}</h2>
            </div>
        @endif

        <!-- INFORMACIÓN DEL COMERCIO -->
        <div class="max-w-7xl mx-auto px-6 pt-6 pb-4">
            <div class="flex flex-wrap items-center justify-between gap-4 w-full">
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-3xl font-black text-[#1b1b18] dark:text-white">{{ $vendor->name }}</h1>
                    @if ($vendor->isOpen())
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-500 text-white">
                            🟢 Abierto ahora
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-500 text-white">
                            🔴 Cerrado ahora
                        </span>
                    @endif
                </div>

                <button x-data @click="$dispatch('open-live-chat')" class="flex-shrink-0 inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 hover:bg-purple-200 dark:hover:bg-purple-800/40 rounded-full text-xs font-bold transition-colors shadow-sm ml-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 15c4.418 0 8-3.134 8-7s-3.582-7-8-7-8 3.134-8 7c0 1.76.743 3.37 1.97 4.6-.097.42-.26.868-.495 1.32-.236.452-.524.872-.857 1.229-.148.158-.292.302-.423.428a.5.5 0 0 0 .315.864c.164.004.331.002.502-.008.232-.014.472-.038.718-.073a9.026 9.026 0 0 0 2.508-.667 9.176 9.176 0 0 0 2.257.507zM5 7a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm4 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm4 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                    </svg>
                    Chatear
                </button>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Comercio adherido • Horario: {{ $vendor->formatted_opening_hours }} • {{ $products->count() }} productos disponibles
            </p>
            @if (!$vendor->isOpen())
                <div class="mt-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl dark:bg-red-950/20 dark:border-red-600">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            ⚠️
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700 dark:text-red-400 font-bold">
                                Este comercio se encuentra actualmente fuera de su horario de atención. Tus pedidos podrían demorarse o procesarse al abrir.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
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
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" loading="lazy">
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
                                <span class="bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white px-4 md:px-6 py-3 md:py-4 rounded-xl text-sm md:text-base font-black cursor-pointer select-none transition-all shadow-lg hover:shadow-purple-500/50 transform hover:scale-105 whitespace-nowrap inline-block text-center">
                                    + PEDIR
                                </span>
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

    <!-- Chat directo con el local -->
    <livewire:live-chat :vendor_id="$vendor->id" />
</x-front-layout>
