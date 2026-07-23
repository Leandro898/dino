<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#4F46E5">
    <title>Bari Rider — Recuperar Contraseña</title>
    
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
            max-width: 420px;
            padding: 32px 24px;
            z-index: 10;
            position: relative;
            transform: translateY(0);
            transition: all 0.3s ease;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
        }

        .input-field {
            width: 100%;
            padding: 14px 16px 14px 44px;
            background: rgba(255, 255, 255, 0.9);
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-size: 1rem;
            color: #1e293b;
            font-weight: 500;
            transition: all 0.2s ease;
            outline: none;
        }

        .input-field:focus {
            border-color: #6366f1;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            transition: color 0.2s ease;
            pointer-events: none;
        }

        .input-field:focus + .input-icon {
            color: #6366f1;
        }

        .btn-submit {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            font-weight: 600;
            font-size: 1.05rem;
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
            <div class="text-center mb-8">
                <a href="/" class="inline-block mb-3 hover:opacity-90 transition">
                    <div class="text-4xl tracking-tight">
                        <span class="text-indigo-600 font-extrabold">Bari</span> <span class="text-violet-500 font-light">Rider</span>
                    </div>
                </a>
                <h1 class="text-xl font-bold text-slate-800 tracking-tight">¿Olvidaste tu contraseña?</h1>
                <p class="text-sm text-slate-500 mt-2 px-2">No hay problema. Ingresa tu correo y te enviaremos un enlace para restablecerla y elegir una nueva.</p>
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm font-medium shadow-sm">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email', absolute: false) }}">
                @csrf

                <!-- Email Address -->
                <div class="input-group">
                    <input id="email" 
                           type="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required 
                           autofocus 
                           placeholder="Correo electrónico"
                           class="input-field">
                    
                    <span class="input-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                    </span>
                    
                    @if($errors->has('email'))
                        <span class="text-rose-500 text-xs font-semibold mt-1 block px-1">
                            {{ $errors->first('email') }}
                        </span>
                    @endif
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit mb-4">
                    <span>Enviar enlace al correo</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                </button>
            </form>

            <!-- Back to Login Link -->
            <div class="text-center mt-6 border-t border-slate-200/60 pt-6">
                <a href="{{ route('login', absolute: false) }}" class="text-indigo-600 hover:text-indigo-700 font-bold transition text-sm flex items-center justify-center gap-1">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    <span>Volver al inicio de sesión</span>
                </a>
            </div>
        </div>
    </div>

</body>
</html>
