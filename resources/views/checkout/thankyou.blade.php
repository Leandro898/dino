<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>¡Gracias por tu compra! - Marketplace Bariloche</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FDFDFC] antialiased flex flex-col min-h-screen">

    <header class="w-full p-6 lg:px-20 bg-white shadow-sm">
        <a href="{{ route('home') }}" class="text-2xl font-bold text-[#f53003]">
            Marketplace Bariloche
        </a>
    </header>

    <main class="flex-grow flex items-center justify-center px-6">
        <div class="max-w-md w-full bg-white p-10 rounded-3xl shadow-sm text-center">
            <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            
            <h1 class="text-3xl font-black uppercase tracking-tighter mb-4">¡Pedido Recibido!</h1>
            <p class="text-gray-600 mb-8">
                Gracias por tu compra. Hemos registrado tu pedido correctamente en nuestro sistema y pronto nos pondremos en contacto contigo.
            </p>

            <a href="{{ route('home') }}" 
               class="inline-block w-full bg-black hover:bg-[#f53003] transition-colors text-white py-4 rounded-xl font-bold uppercase text-sm">
                Volver a la tienda
            </a>
        </div>
    </main>

</body>
</html>