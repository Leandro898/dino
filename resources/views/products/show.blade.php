<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $product->name }} - Marketplace Bariloche</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] antialiased">

    <header class="w-full p-6 lg:px-20 flex justify-between items-center bg-white dark:bg-[#161615] shadow-sm sticky top-0 z-50">
        <a href="{{ route('home') }}" class="text-2xl font-bold text-[#f53003]">Marketplace Bariloche</a>
        <a href="{{ route('home') }}" class="text-sm font-bold uppercase tracking-widest hover:text-[#f53003] transition-colors">Volver</a>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-10 lg:py-20">
        <div class="flex flex-col lg:flex-row gap-12">
            
            <div class="w-full lg:w-3/5">
                <div class="rounded-3xl overflow-hidden bg-gray-100 dark:bg-[#1d1d1d] shadow-2xl">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" 
                             alt="{{ $product->name }}" 
                             class="w-full h-auto object-cover">
                    @else
                        <div class="aspect-square flex items-center justify-center text-gray-400 italic bg-gray-200">
                            Sin imagen disponible
                        </div>
                    @endif
                </div>
            </div>

            <div class="w-full lg:w-2/5 flex flex-col">
                <div class="mb-6">
                    <span class="inline-block bg-orange-100 dark:bg-orange-900/30 text-[#f53003] text-[10px] font-black uppercase tracking-[0.2em] px-3 py-1 rounded-full mb-4">
                        Publicado por {{ $product->user->name }}
                    </span>
                    <h1 class="text-4xl md:text-5xl font-black dark:text-white uppercase leading-none mb-4">
                        {{ $product->name }}
                    </h1>
                </div>

                <div class="bg-gray-50 dark:bg-[#161615] p-6 rounded-3xl mb-8 border border-gray-100 dark:border-white/5">
                    <div class="flex items-end gap-2 mb-2">
                        <span class="text-4xl font-black text-black dark:text-white">
                            ${{ number_format($product->price, 0, ',', '.') }}
                        </span>
                        <span class="text-sm font-bold text-gray-400 uppercase mb-1">ARS</span>
                    </div>
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-tight">Precio final sin comisiones</p>
                </div>

                <div class="prose dark:prose-invert mb-10">
                    <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400 mb-3">Descripción</h2>
                    <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                        {{ $product->description ?? 'El vendedor no ha proporcionado una descripción detallada para este producto.' }}
                    </p>
                </div>

                <div class="mt-auto space-y-4">
                    <button class="w-full bg-[#f53003] text-white py-5 rounded-2xl font-black text-xl uppercase tracking-tighter hover:bg-[#d42a02] transition-all shadow-xl shadow-orange-500/20 active:scale-[0.98]">
                        Comprar ahora
                    </button>
                    
                    <a href="https://wa.me/5491100000000?text=Hola! Estoy interesado en el producto: {{ $product->name }}" 
                       target="_blank"
                       class="w-full flex justify-center items-center gap-2 border-2 border-gray-200 dark:border-white/10 dark:text-white py-4 rounded-2xl font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all">
                        Consultar al vendedor
                    </a>
                </div>

                <div class="mt-10 pt-6 border-t border-gray-100 dark:border-white/5 flex items-center justify-between text-gray-400">
                    <div class="flex flex-col">
                        <span class="text-[10px] uppercase font-bold">Ubicación</span>
                        <span class="text-xs font-bold text-gray-600 dark:text-gray-300">San Carlos de Bariloche</span>
                    </div>
                    <div class="text-right flex flex-col">
                        <span class="text-[10px] uppercase font-bold">Publicado</span>
                        <span class="text-xs font-bold text-gray-600 dark:text-gray-300">{{ $product->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <footer class="py-10 text-center text-sm text-[#706f6c] border-t border-gray-100 dark:border-white/5">
        &copy; {{ date('Y') }} Marketplace Bariloche
    </footer>
</body>
</html>