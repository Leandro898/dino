@php
    $cartCount = collect(session('cart', []))->sum('quantity');
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

        @auth
            <a href="{{ url('/dashboard') }}" class="text-sm font-medium hover:text-purple-200">
                Dashboard
            </a>
        @else
            <a href="{{ route('login') }}" class="text-sm font-medium hover:text-purple-200">
                Entrar
            </a>
        @endauth

    </nav>

</header>
