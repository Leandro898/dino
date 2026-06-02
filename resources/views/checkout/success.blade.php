@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto py-16 px-4 text-center">
    <h1 class="text-3xl font-bold mb-4 text-green-600">¡Pedido realizado con éxito!</h1>
    <p class="mb-6">Tu pedido fue registrado correctamente. El comercio ya fue notificado y se pondrá en contacto para coordinar la entrega.</p>
    @if(session('whatsAppUrl'))
        <a href="{{ session('whatsAppUrl') }}" target="_blank" class="inline-block bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-lg shadow transition mb-4">
            Confirmar y coordinar por WhatsApp
        </a>
        <p class="text-sm text-gray-500">Puedes coordinar el pago y la entrega directamente con el comercio por WhatsApp.</p>
    @endif
    <a href="/" class="block mt-8 text-purple-600 hover:underline">Volver al inicio</a>
</div>
@endsection
