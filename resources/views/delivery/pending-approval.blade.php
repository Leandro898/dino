<x-guest-layout>
    <div class="mb-4 text-center">
        <h2 class="text-2xl font-bold text-gray-900">Cuenta en Revisión</h2>
        <p class="text-sm text-gray-600 mt-4">
            Tu dirección de correo electrónico ha sido verificada exitosamente. Sin embargo, tu cuenta de repartidor aún debe ser aprobada por un administrador antes de que puedas comenzar a recibir pedidos.
        </p>
        <p class="text-sm text-gray-600 mt-4">
            Por favor, espera a que nos comuniquemos contigo o vuelve a revisar más tarde.
        </p>
    </div>

    <div class="flex items-center justify-center mt-6">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-primary-button class="bg-gray-600 hover:bg-gray-700">
                {{ __('Cerrar Sesión') }}
            </x-primary-button>
        </form>
    </div>
</x-guest-layout>
