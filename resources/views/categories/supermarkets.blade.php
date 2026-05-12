<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $categoryTitle ?? 'Categoria' }} - {{ config('app.name', 'Bari Tienda') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon-arg.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.ga4')
</head>

<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] antialiased">

    @include('partials.header')

    <main class="max-w-7xl mx-auto px-4 py-8 md:px-10 lg:px-20 md:py-10">
        <section
            class="rounded-3xl bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-700 px-6 py-8 text-white shadow-xl md:px-8">
            <p class="text-xs font-black uppercase tracking-[0.24em] text-white/80">Categoria</p>
            <h1 class="mt-2 text-3xl font-black uppercase md:text-4xl">{{ $categoryTitle ?? 'Categoria' }}</h1>
            <p class="mt-3 max-w-3xl text-sm font-medium text-white/90 md:text-base">
                Productos importados desde Carrefour para esta categoria, listados dentro de tu propia tienda con
                precio, imagen y compra directa.
            </p>

            <form method="GET"
                action="{{ $categorySlug === 'almacen' ? route('categories.supermarkets') : route('categories.carrefour', ['categorySlug' => $categorySlug]) }}"
                class="mt-5 max-w-xl">
                <label for="supermarket-search" class="sr-only">Buscar productos de supermercado</label>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <input id="supermarket-search" type="search" name="q" value="{{ $search }}"
                        placeholder="Buscar por nombre"
                        class="w-full rounded-2xl border border-white/20 bg-white px-4 py-3 text-sm font-semibold text-gray-800 outline-none">
                    <button type="submit"
                        class="rounded-2xl bg-[#1b1b18] px-5 py-3 text-sm font-black uppercase tracking-wide text-white transition hover:bg-black">
                        Buscar
                    </button>
                </div>
            </form>
        </section>

        <section class="mt-8">
            @if ($products->isEmpty())
                <div
                    class="rounded-3xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center text-gray-500">
                    No hay productos importados para mostrar en esta categoria.
                </div>
            @else
                <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-4">
                    @foreach ($products as $product)
                        <a href="{{ route('products.show', ['product' => $product->slug]) }}"
                            class="group flex h-full flex-col overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                            <div class="flex h-52 items-center justify-center bg-gray-50 p-4">
                                @if ($product->image_src)
                                    <img src="{{ $product->image_src }}" alt="{{ $product->name }}"
                                        class="h-40 w-40 object-contain transition duration-300 group-hover:scale-105">
                                @else
                                    <div class="text-sm font-semibold text-gray-400">Sin imagen</div>
                                @endif
                            </div>

                            <div class="flex flex-1 flex-col p-4">
                                <h2 class="min-h-[3.25rem] text-sm font-black uppercase leading-tight text-[#1b1b18]">
                                    {{ $product->name }}
                                </h2>

                                <div class="mt-auto border-t border-gray-100 pt-3">
                                    <p class="text-2xl font-black text-emerald-700">
                                        ${{ number_format($product->price, 0, ',', '.') }}
                                    </p>
                                    <p class="mt-1 text-[11px] font-bold uppercase tracking-wide text-gray-400">Precio
                                        actualizado</p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @endif
        </section>
    </main>
</body>

</html>
