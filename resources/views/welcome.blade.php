<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Bari Tienda') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] antialiased">

    @php
        $cartCount = collect(session('cart', []))->sum('quantity');
    @endphp

    @include('partials.header')

    <section
        class="relative w-full h-[400px] bg-gradient-to-r from-orange-600 to-red-700 flex items-center overflow-hidden">
        <div class="absolute inset-0 opacity-20">
            <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <path d="M0 100 C 20 0 50 0 100 100 Z" fill="white"></path>
            </svg>
        </div>

        <div class="relative max-w-7xl mx-auto px-6 lg:px-20 w-full text-white">
            <h2 class="text-4xl md:text-6xl font-extrabold mb-4 drop-shadow-md">Todo lo que buscas,<br>en tu ciudad.
            </h2>
            <p class="text-lg md:text-xl opacity-90 max-w-lg mb-8">Comprá y vendé de forma local en Bariloche de manera
                fácil y rápida.</p>
            <div class="flex gap-4">
                <a href="#productos"
                    class="bg-white text-[#f53003] px-6 py-3 rounded-full font-bold hover:bg-gray-100 transition-all shadow-lg">Ver
                    productos</a>
            </div>
        </div>
    </section>

    <main id="productos" class="w-full max-w-7xl mx-auto p-4 md:p-10 lg:p-20">
        <div class="mb-8">

        </div>

        <!-- GRID FIJO DE 4 COLUMNAS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($products as $product)
                <a href="{{ route('products.show', $product->id) }}"
                    class="group bg-white dark:bg-[#1d1d1d] rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all flex flex-col h-full border border-gray-100 dark:border-white/5">

                    <div class="relative h-64 overflow-hidden bg-gray-200">
                        @if ($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-400 italic">
                                Sin imagen
                            </div>
                        @endif

                        <div
                            class="absolute bottom-4 left-4 bg-white dark:bg-black p-2 rounded-lg text-center min-w-[50px] shadow-md">
                            <span class="text-xl font-black block leading-none dark:text-white">
                                {{ $product->created_at->format('d') }}
                            </span>
                            <span class="text-[10px] uppercase font-bold text-gray-500">
                                {{ $product->created_at->format('M') }}
                            </span>
                        </div>
                    </div>

                    <div class="p-4 flex flex-col flex-grow">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">
                            {{ $product->user->name }}
                        </span>

                        <h3
                            class="text-sm font-extrabold dark:text-white uppercase leading-tight mb-2 line-clamp-2 group-hover:text-[#f53003] transition-colors">
                            {{ $product->name }}
                        </h3>

                        <div
                            class="mt-auto pt-3 border-t border-gray-100 dark:border-white/5 flex items-center justify-between">
                            <span class="text-lg font-black text-black dark:text-white">
                                ${{ number_format($product->price, 0, ',', '.') }}
                            </span>
                            <span class="text-[10px] font-bold text-[#f53003] uppercase">Ver más</span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-4 text-center py-20">
                    <p class="text-gray-500">No hay productos disponibles actualmente.</p>
                </div>
            @endforelse
        </div>
    </main>

    <footer class="py-10 text-center text-sm text-[#706f6c]">
        &copy; {{ date('Y') }} Bari Tienda
    </footer>
</body>

</html>
