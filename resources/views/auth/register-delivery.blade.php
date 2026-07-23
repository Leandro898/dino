<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#4F46E5">
    <title>Bari Rider — Registrarse</title>
    
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

        .input-group {
            position: relative;
            margin-bottom: 16px;
        }

        .input-field {
            width: 100%;
            padding: 12px 16px 12px 44px;
            background: rgba(255, 255, 255, 0.9);
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-size: 0.95rem;
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

        .show-password-btn {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            outline: none;
            padding: 0;
        }

        .show-password-btn:hover {
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="relative w-full h-full min-h-screen flex items-center justify-center p-4 overflow-x-hidden overflow-y-auto" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
        <!-- Background Decor -->
        <div class="glow-circle glow-1"></div>
        <div class="glow-circle glow-2"></div>

        <div class="glass-card my-8">
            <!-- Header -->
            <div class="text-center mb-6">
                <a href="/" class="inline-block mb-2 hover:opacity-90 transition">
                    <div class="text-4xl tracking-tight">
                        <span class="text-indigo-600 font-extrabold">Bari</span> <span class="text-violet-500 font-light">Rider</span>
                    </div>
                </a>
                <h1 class="text-xl font-bold text-slate-800 tracking-tight">Únete como Repartidor</h1>
                <p class="text-xs text-slate-500 mt-1 px-4">Crea tu cuenta para empezar a entregar pedidos y usar la App de Repartidores.</p>
            </div>

            <!-- Validation Errors & Status -->
            @if ($errors->any())
                <div class="mb-4 p-3 bg-rose-50 border border-rose-100 text-rose-700 rounded-xl text-xs font-medium">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register.delivery', absolute: false) }}">
                @csrf

                <!-- Name -->
                <div class="input-group">
                    <input id="name" 
                           type="text" 
                           name="name" 
                           value="{{ old('name') }}" 
                           required 
                           autofocus 
                           autocomplete="name" 
                           placeholder="Nombre completo"
                           class="input-field">
                    
                    <span class="input-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </span>
                </div>

                <!-- Email Address -->
                <div class="input-group">
                    <input id="email" 
                           type="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required 
                           autocomplete="username" 
                           placeholder="Correo electrónico"
                           class="input-field">
                    
                    <span class="input-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                    </span>
                </div>

                <!-- Password -->
                <div class="input-group" x-data="{ show: false }">
                    <input id="password" 
                           :type="show ? 'text' : 'password'" 
                           name="password" 
                           required 
                           autocomplete="new-password" 
                           placeholder="Contraseña"
                           class="input-field pr-12">
                    
                    <span class="input-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                    </span>

                    <button type="button" class="show-password-btn" @click="show = !show">
                        <svg x-show="!show" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="show" style="display: none;" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.978 9.978 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>

                <!-- Confirm Password -->
                <div class="input-group" x-data="{ show: false }">
                    <input id="password_confirmation" 
                           :type="show ? 'text' : 'password'" 
                           name="password_confirmation" 
                           required 
                           autocomplete="new-password" 
                           placeholder="Confirmar contraseña"
                           class="input-field pr-12">
                    
                    <span class="input-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                    </span>

                    <button type="button" class="show-password-btn" @click="show = !show">
                        <svg x-show="!show" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="show" style="display: none;" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.978 9.978 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit mt-4">
                    <span>Registrarme como Repartidor</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="8.5" cy="7" r="4"></circle>
                        <line x1="20" y1="8" x2="20" y2="14"></line>
                        <line x1="23" y1="11" x2="17" y2="11"></line>
                    </svg>
                </button>
            </form>

            <!-- Back to Login Link -->
            <div class="text-center mt-6 border-t border-slate-200/60 pt-6">
                <span class="text-slate-500 text-sm font-medium">¿Ya tienes cuenta?</span>
                <a href="{{ route('login', absolute: false) }}" class="block mt-2 text-indigo-600 hover:text-indigo-700 font-bold transition text-sm">
                    Inicia sesión en tu cuenta
                </a>
            </div>
        </div>
    </div>

</body>
</html>
