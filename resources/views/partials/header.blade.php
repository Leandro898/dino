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
        class="support-whatsapp-fab fixed z-50 inline-flex items-center justify-center rounded-full bg-green-500 px-5 py-3 text-sm font-bold text-white shadow-lg ring-1 ring-black/10 transition hover:bg-green-600 hover:shadow-xl">
        <span>Soporte</span>
    </a>

    <style>
        .support-whatsapp-fab {
            left: 50%;
            bottom: 16px;
            transform: translateX(-50%);
        }
    </style>
@endif
