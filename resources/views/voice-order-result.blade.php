<x-front-layout title="Bari Tienda | Pedido por voz" bodyClass="font-['Outfit',sans-serif]">
    @push('styles')
    <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,700|outfit:300,400,600,700" rel="stylesheet" />
    @endpush

    @php
        $pedido = request('pedido', '');
    @endphp

    <div class="min-h-screen grid place-items-center p-4" style="background: radial-gradient(900px 460px at -15% -20%, rgba(124, 58, 237, 0.18) 0%, transparent 65%), radial-gradient(560px 320px at 110% 0%, rgba(34, 197, 94, 0.1) 0%, transparent 70%), linear-gradient(160deg, #f8f7ff 0%, #f2efff 45%, #eef7ff 100%);">
        <main class="w-full max-w-[720px] bg-white border border-[#e5dbff] rounded-[30px] shadow-[0_20px_44px_rgba(69,36,140,0.12)] p-5">
            <div class="flex justify-between gap-3 items-center flex-wrap mb-4">
                <div class="inline-flex gap-2 items-center px-3 py-1.5 rounded-full bg-gradient-to-r from-[#f0eaff] to-[#e6f8ed] text-[#3b2070] text-sm font-extrabold">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-600" aria-hidden="true"></span> Pedido por voz
                </div>
                <a class="border border-[#ddd0ff] rounded-xl px-4 py-3 text-[0.92rem] font-extrabold cursor-pointer no-underline inline-flex items-center justify-center transition-all hover:-translate-y-[1px] text-[#4b2c86] bg-[#f5f2ff]" href="{{ route('home') }}">Volver a la home</a>
            </div>

            <h1 class="mt-1 mb-2 font-['Space_Grotesk'] text-[clamp(1.8rem,5vw,3rem)] leading-none tracking-tight">Esto es lo que entendimos.</h1>
            <p class="text-[#66547f] text-base mb-4 max-w-[52ch]">La voz se transcribió y te mostramos el texto para que puedas confirmarlo antes de seguir.</p>

            <section class="rounded-[22px] bg-gradient-to-b from-[#fcfaff] to-[#f7f4ff] border border-[#e5dbff] p-4" aria-label="Resultado del pedido por voz">
                <div class="text-[0.78rem] font-extrabold uppercase tracking-widest text-[#7b61c3] mb-2">Tu pedido</div>
                <div class="font-['Space_Grotesk'] text-[clamp(1.35rem,4vw,2.2rem)] font-bold text-[#24154a] leading-[1.12] break-words {{ $pedido ? '' : 'text-[#8477a4] font-semibold' }}">
                    {{ $pedido ?: 'No llegó ningún texto todavía.' }}
                </div>
            </section>

            <div class="flex gap-3 flex-wrap mt-4">
                <a class="border-0 rounded-xl px-4 py-3 text-[0.92rem] font-extrabold cursor-pointer no-underline inline-flex items-center justify-center transition-all hover:-translate-y-[1px] text-white bg-gradient-to-br from-[#6d28d9] to-[#4c1d95] shadow-[0_12px_24px_rgba(70,30,152,0.2)]" href="{{ route('home') }}?q={{ urlencode($pedido) }}#productos">Buscar esto en el catálogo</a>
                <a class="border border-[#ddd0ff] rounded-xl px-4 py-3 text-[0.92rem] font-extrabold cursor-pointer no-underline inline-flex items-center justify-center transition-all hover:-translate-y-[1px] text-[#4b2c86] bg-[#f5f2ff]" href="{{ route('home') }}">Dictar otro pedido</a>
            </div>

            <section class="mt-4" aria-label="Productos sugeridos">
                <div class="flex items-center justify-between gap-3 mb-3 flex-wrap">
                    <h2 class="font-['Space_Grotesk'] text-[1.15rem] font-bold text-[#24154a] m-0">Algunos productos que coinciden</h2>
                    <span class="text-[0.84rem] font-extrabold text-[#6d28d9] bg-[#f4f0ff] border border-[#e5dbff] px-3 py-1.5 rounded-full">{{ isset($suggestedProducts) ? $suggestedProducts->count() : 0 }} sugerencias</span>
                </div>

                @if (!empty($suggestedProducts) && $suggestedProducts->isNotEmpty())
                    <div class="grid grid-cols-[repeat(auto-fit,minmax(150px,1fr))] gap-3.5">
                        @foreach ($suggestedProducts as $product)
                            <a class="block no-underline text-inherit bg-white border border-[#e9e0ff] rounded-2xl overflow-hidden shadow-[0_12px_28px_rgba(69,36,140,0.08)] transition-all hover:-translate-y-0.5 hover:shadow-[0_18px_34px_rgba(69,36,140,0.12)]" href="{{ route('products.show', ['product' => $product->slug]) }}">
                                <div class="aspect-square bg-gradient-to-b from-[#faf7ff] to-[#f3efff] grid place-items-center p-3">
                                    @if ($product->image)
                                        <img class="w-full h-full object-contain" src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                                    @else
                                        <div class="text-[#8a84a5] text-sm font-bold">Sin imagen</div>
                                    @endif
                                </div>
                                <div class="p-3.5">
                                    <p class="m-0 mb-2 text-[0.92rem] font-extrabold leading-tight text-[#27144b] min-h-[2.4em]">{{ $product->name }}</p>
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="font-['Space_Grotesk'] text-base font-bold text-[#4c1d95]">${{ number_format($product->adjusted_price, 0, ',', '.') }}</span>
                                        <span class="text-[0.72rem] font-extrabold uppercase tracking-wide text-[#6d28d9]">Ver</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="mt-4 p-3.5 rounded-[18px] bg-[#faf7ff] border border-[#e5dbff] text-[#5e4b84] font-bold">
                        Todavía no encontré productos claros para ese pedido. Probá decir una bebida, un snack, farmacia o algo de súper.
                    </div>
                @endif
            </section>

            <p class="mt-4 text-[#66547f] text-sm leading-relaxed">Si querés, en el siguiente paso puedo hacer que este texto se convierta automáticamente en categorías o productos concretos de tu tienda.</p>
        </main>
    </div>
</x-front-layout>
