<x-delivery-layout title="Bari Rider — Cuenta en Revisión" themeColor="#4F46E5">
    @push('head')
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @endpush

    @push('styles')
    <style>
        html, body {
            height: 100%;
            width: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
            font-family: 'Outfit', sans-serif;
        }

        /* Ambient glowing circles */
        .glow-circle {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 0;
            opacity: 0.4;
            pointer-events: none;
            animation: float 8s ease-in-out infinite alternate;
        }
        .glow-1 {
            width: 300px;
            height: 300px;
            background: #818cf8;
            top: -50px;
            left: -50px;
        }
        .glow-2 {
            width: 250px;
            height: 250px;
            background: #c084fc;
            bottom: -50px;
            right: -50px;
            animation-delay: -3s;
        }

        @keyframes float {
            0% { transform: translateY(0px) scale(1); }
            100% { transform: translateY(20px) scale(1.1); }
        }

        /* Glassmorphism Card */
        .glass-card {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 440px;
            padding: 36px 24px;
            z-index: 10;
            position: relative;
        }

        .review-icon-container {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(99, 102, 241, 0.1);
            border: 2px dashed rgba(99, 102, 241, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px auto;
            color: #6366f1;
            position: relative;
        }

        .pulse-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 1.5px solid rgba(99, 102, 241, 0.3);
            animation: pulse 2.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(0.95); opacity: 0.8; }
            50% { transform: scale(1.15); opacity: 0.3; }
            100% { transform: scale(1.3); opacity: 0; }
        }

        .btn-check {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            font-weight: 600;
            font-size: 1rem;
            padding: 14px;
            border-radius: 14px;
            width: 100%;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-check:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.4);
        }

        .btn-check:active {
            transform: translateY(1px);
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.2);
        }
    </style>
    </style>
    @endpush
    <div class="relative w-full h-full min-h-screen flex items-center justify-center p-4 overflow-x-hidden overflow-y-auto" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
        <!-- Background Decor -->
        <div class="glow-circle glow-1"></div>
        <div class="glow-circle glow-2"></div>

        <div class="glass-card text-center">
            <!-- Review Icon -->
            <div class="review-icon-container">
                <div class="pulse-ring"></div>
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>

            <!-- Header -->
            <div class="mb-6">
                <div class="text-4xl tracking-tight mb-2">
                    <span class="text-indigo-600 font-extrabold">Bari</span> <span class="text-violet-500 font-light">Rider</span>
                </div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Cuenta en Revisión</h1>
                <p class="text-sm text-slate-600 mt-4 px-2">
                    ¡Tu correo electrónico ha sido verificado! Sin embargo, tu cuenta de repartidor está en proceso de revisión por parte de la administración.
                </p>
                <p class="text-sm text-slate-500 mt-3 px-2">
                    Te notificaremos por correo electrónico una vez que tu cuenta sea aprobada para que puedas comenzar a recibir pedidos.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col gap-3 mt-6">
                <a href="{{ route('delivery.app', absolute: false) }}" class="btn-check">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"></path>
                    </svg>
                    <span>Verificar Estado Actual</span>
                </a>

                <form method="POST" action="{{ route('logout', absolute: false) }}" class="w-full">
                    @csrf
                    <button type="submit" class="w-full py-3 text-slate-500 hover:text-slate-700 font-bold transition text-sm flex items-center justify-center gap-1.5 border border-slate-200 hover:border-slate-300 rounded-xl bg-slate-50/50">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        <span>Cerrar Sesión</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-delivery-layout>
