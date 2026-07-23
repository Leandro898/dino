<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#4F46E5">
    <title>Bari Rider — Verificar Correo</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon-arg.svg') }}">
    
    <!-- Scripts & Tailwind via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

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
            padding: 32px 24px;
            z-index: 10;
            position: relative;
            transform: translateY(0);
            transition: all 0.3s ease;
        }

        .btn-submit {
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
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.4);
        }

        .btn-submit:active {
            transform: translateY(1px);
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.2);
        }
    </style>
</head>
<body>
    <div class="relative w-full h-full min-h-screen flex items-center justify-center p-4 overflow-x-hidden overflow-y-auto" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
        <!-- Background Decor -->
        <div class="glow-circle glow-1"></div>
        <div class="glow-circle glow-2"></div>

        <div class="glass-card">
            <!-- Header -->
            <div class="text-center mb-6">
                <a href="/" class="inline-block mb-3 hover:opacity-90 transition">
                    <div class="text-4xl tracking-tight">
                        <span class="text-indigo-600 font-extrabold">Bari</span> <span class="text-violet-500 font-light">Rider</span>
                    </div>
                </a>
                <h1 class="text-xl font-bold text-slate-800 tracking-tight">Verifica tu Correo</h1>
                <p class="text-sm text-slate-500 mt-2 px-2">¡Gracias por registrarte! Antes de comenzar, ¿podrías verificar tu dirección de correo haciendo clic en el enlace que te acabamos de enviar? Si no recibiste el correo, te enviaremos otro con gusto.</p>
            </div>

            <!-- Verification Link Sent Status -->
            @if (session('status') == 'verification-link-sent')
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-xs font-semibold shadow-sm flex items-start gap-2">
                    <svg class="w-4 h-4 text-green-600 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Se ha enviado un nuevo enlace de verificación a la dirección de correo proporcionada.</span>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex flex-col gap-3">
                <form method="POST" action="{{ route('verification.send', absolute: false) }}">
                    @csrf
                    <button type="submit" class="btn-submit">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="22,6 12,13 2,6"></polyline>
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        </svg>
                        <span>Reenviar Correo de Verificación</span>
                    </button>
                </form>

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

</body>
</html>
