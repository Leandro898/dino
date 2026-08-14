<x-front-layout bodyClass="bg-gray-50">
    @push('styles')
        <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700,800" rel="stylesheet" />
        <style>
            .app-landing {
                min-height: calc(100vh - 60px);
                background: radial-gradient(circle at 10% 20%, rgba(106, 49, 223, 0.1) 0%, transparent 40%),
                            radial-gradient(circle at 90% 80%, rgba(244, 63, 94, 0.08) 0%, transparent 40%),
                            #f8fafc;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 2rem 1.5rem;
                font-family: 'Outfit', sans-serif;
                text-align: center;
            }
            .glass-card {
                background: rgba(255, 255, 255, 0.7);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border: 1px solid rgba(255, 255, 255, 0.5);
                border-radius: 32px;
                padding: 3rem 2rem;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.04), 0 1px 3px rgba(0,0,0,0.02);
                max-width: 420px;
                width: 100%;
                position: relative;
                overflow: hidden;
            }
            .glass-card::before {
                content: '';
                position: absolute;
                top: 0; left: 0; right: 0; height: 6px;
                background: linear-gradient(90deg, #6a31df, #f43f5e);
            }
            .app-icon {
                width: 96px;
                height: 96px;
                border-radius: 24px;
                background: linear-gradient(135deg, #6a31df, #4c1d95);
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 3rem;
                margin: 0 auto 1.5rem;
                box-shadow: 0 12px 24px rgba(106, 49, 223, 0.25);
            }
            .title {
                font-size: 2rem;
                font-weight: 800;
                color: #1e293b;
                margin-bottom: 0.5rem;
                letter-spacing: -0.02em;
                line-height: 1.2;
            }
            .subtitle {
                font-size: 1.05rem;
                color: #64748b;
                margin-bottom: 2.5rem;
                line-height: 1.5;
            }
            .features {
                text-align: left;
                margin-bottom: 2.5rem;
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }
            .feature-item {
                display: flex;
                align-items: center;
                gap: 1rem;
                padding: 0.75rem 1rem;
                background: rgba(255,255,255,0.6);
                border-radius: 16px;
                border: 1px solid rgba(255,255,255,0.8);
            }
            .feature-icon {
                background: #f1f5f9;
                width: 40px; height: 40px;
                border-radius: 12px;
                display: flex; align-items: center; justify-content: center;
                font-size: 1.25rem;
            }
            .feature-text {
                font-weight: 600;
                color: #334155;
                font-size: 0.95rem;
            }
            .btn-install {
                width: 100%;
                padding: 1.1rem;
                border-radius: 999px;
                border: none;
                background: linear-gradient(135deg, #6a31df, #4c1d95);
                color: white;
                font-size: 1.1rem;
                font-weight: 700;
                font-family: inherit;
                cursor: pointer;
                box-shadow: 0 10px 20px rgba(106, 49, 223, 0.25);
                transition: transform 0.2s, box-shadow 0.2s;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
            }
            .btn-install:hover {
                transform: translateY(-2px);
                box-shadow: 0 14px 28px rgba(106, 49, 223, 0.35);
            }
            .btn-install:active {
                transform: translateY(1px);
            }
            #installInstructions {
                display: none;
                margin-top: 1.5rem;
                padding: 1rem;
                background: #fdf2f8;
                border: 1px solid #fbcfe8;
                border-radius: 16px;
                color: #be185d;
                font-size: 0.9rem;
                font-weight: 500;
                text-align: left;
            }
        </style>
    @endpush

    <div class="app-landing">
        <div class="glass-card">
            <div class="app-icon">
                🦖
            </div>
            <h1 class="title">BariTienda App</h1>
            <p class="subtitle">Instalá nuestra aplicación oficial para una experiencia más rápida y directa.</p>
            
            <div class="features">
                <div class="feature-item">
                    <div class="feature-icon">🚀</div>
                    <div class="feature-text">Carga ultrarrápida (incluso sin internet)</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">📱</div>
                    <div class="feature-text">Acceso directo en tu pantalla de inicio</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">🔔</div>
                    <div class="feature-text">Notificaciones instantáneas de tus pedidos</div>
                </div>
            </div>

            <button id="installBtn" class="btn-install">
                Descargar e Instalar App
            </button>
            
            <div id="installInstructions">
                <b>Para instalar en iOS (iPhone/iPad):</b><br>
                1. Toca el botón <b>Compartir</b> en la barra inferior de Safari.<br>
                2. Baja y selecciona <b>"Agregar a Inicio"</b>.
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let deferredPrompt;
            const installBtn = document.getElementById('installBtn');
            const installInstructions = document.getElementById('installInstructions');

            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
            const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

            if (isStandalone) {
                installBtn.textContent = '¡App Ya Instalada!';
                installBtn.disabled = true;
                installBtn.style.opacity = '0.7';
                installBtn.style.background = '#10b981';
            } else if (isIOS) {
                installBtn.addEventListener('click', () => {
                    installInstructions.style.display = 'block';
                });
            } else {
                window.addEventListener('beforeinstallprompt', (e) => {
                    e.preventDefault();
                    deferredPrompt = e;
                });

                installBtn.addEventListener('click', async () => {
                    if (deferredPrompt) {
                        deferredPrompt.prompt();
                        const { outcome } = await deferredPrompt.userChoice;
                        if (outcome === 'accepted') {
                            console.log('Usuario aceptó instalar la PWA');
                        }
                        deferredPrompt = null;
                    } else {
                        installInstructions.innerHTML = 'Tu navegador no permite la instalación automática o ya la tienes instalada. Para forzarla, abre el menú de opciones (⋮) y selecciona "Instalar aplicación" o "Agregar a la pantalla principal".';
                        installInstructions.style.display = 'block';
                    }
                });
            }
            
            window.addEventListener('appinstalled', () => {
                deferredPrompt = null;
                installBtn.textContent = '¡Instalada Exitosamente!';
                installBtn.style.background = '#10b981';
            });
        </script>
    @endpush
</x-front-layout>
