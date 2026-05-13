@php
    $cartCount = collect(session('cart', []))->sum('quantity');
    $supportWhatsAppNumber = preg_replace(
        '/\D+/',
        '',
        (string) (config('services.support.whatsapp_number') ?: config('services.bank_transfer.whatsapp_number')),
    );
    $supportWhatsAppUrl = null;

    if (!empty($supportWhatsAppNumber)) {
        $supportMessage = 'Hola, necesito ayuda con mi compra en Bari Tienda.';
        $supportWhatsAppUrl = 'https://wa.me/' . $supportWhatsAppNumber . '?text=' . urlencode($supportMessage);
    }
@endphp

<header
    class="w-full p-6 lg:px-20 flex justify-between items-center bg-gradient-to-r from-purple-600 via-purple-700 to-indigo-800 text-white shadow-md sticky top-0 z-50">

    <a href="{{ route('home') }}" class="text-2xl font-bold text-white">
        Bari Tienda
    </a>

    <nav class="flex gap-6 items-center">
        <a href="{{ route('cart.index') }}" class="relative text-xl">
            🛒
            @if ($cartCount > 0)
                <span class="absolute -top-2 -right-3 bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">
                    {{ $cartCount }}
                </span>
            @endif
        </a>
    </nav>

</header>

@if (!empty($supportWhatsAppUrl))
    <a href="{{ $supportWhatsAppUrl }}" target="_blank" rel="noopener noreferrer"
        aria-label="Contactar soporte por WhatsApp"
        class="fixed right-4 bottom-5 z-50 inline-flex items-center gap-2.5 rounded-full bg-green-500 px-4 py-3 text-sm font-bold text-white shadow-lg ring-1 ring-black/10 transition hover:bg-green-600 hover:shadow-xl sm:right-6 sm:bottom-6">
        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-white text-green-500">
            <svg class="h-4 w-4" viewBox="0 0 448 512" fill="currentColor" aria-hidden="true">
                <path
                    d="M380.9 97.1C339-3.1 233.4-33.4 147.6 9.4C61.8 52.1 18.7 153.2 44.1 245.7L0 384l141.1-43.5c31.4 17.2 66.9 26.3 102.9 26.3c123.6 0 224-100.4 224-224c0-36-9-71.5-26.1-102.7zM244 334.5c-31.4 0-62.1-8.4-89-24.3l-6.4-3.8l-83.8 25.8l26.3-82.7l-4.2-6.7c-17.6-28.1-26.9-60.5-26.9-93.8c0-97 78.9-176 176-176s176 79 176 176s-78.9 175.7-176 175.7zm101.5-132.1c-5.6-2.8-33.1-16.3-38.2-18.2c-5.1-1.9-8.8-2.8-12.5 2.8c-3.7 5.6-14.4 18.2-17.6 22c-3.2 3.7-6.5 4.2-12.1 1.4c-33.1-16.5-54.8-29.4-76.8-66.6c-5.8-10 .6-9.3 16.5-31c1.9-2.8.9-5.1-.5-7.9c-1.4-2.8-12.5-30.1-17.1-41.2c-4.5-10.8-9.1-9.3-12.5-9.5c-3.2-.2-6.9-.2-10.6-.2s-9.7 1.4-14.8 7c-5.1 5.6-19.4 18.9-19.4 46.1s19.9 53.5 22.7 57.2c2.8 3.7 39.2 59.9 95 84c13.3 5.8 23.7 9.3 31.8 11.9c13.4 4.3 25.6 3.7 35.2 2.2c10.7-1.6 33.1-13.5 37.8-26.6c4.6-13 4.6-24.2 3.2-26.6c-1.4-2.3-5.1-3.7-10.7-6.5z" />
            </svg>
        </span>
        <span class="hidden sm:inline">Consultas</span>
    </a>
@endif
