<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default() // El panel admin ahora es default, login y dashboard en /admin
            ->id('admin')
            ->path('admin')
            ->login()
            ->databaseNotifications()
            ->databaseNotificationsPolling('5s')
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => Blade::render('
                    <audio id="order-sound" src="/sounds/order.mp3" preload="auto" style="display:none"></audio>
                    <script>
                        let lastUnreadCount = null;
                        let audioUnlocked = false;
                        let isRinging = false;

                        function playNotificationSound() {
                            console.log("NOTIFICACION: Intentando sonar...");
                            const sound = document.getElementById("order-sound");
                            if (sound) {
                                sound.muted = false;
                                sound.volume = 1.0;
                                sound.loop = true; // Suena continuo como PedidosYa hasta que se atienda
                                if (sound.paused) {
                                    sound.play().then(() => {
                                        console.log("NOTIFICACION: Sonido continuo activado (Estilo PedidosYa)");
                                        isRinging = true;
                                    }).catch(error => {
                                        console.error("NOTIFICACION: Bloqueado por navegador", error);
                                    });
                                }
                            } else {
                                console.error("NOTIFICACION: No existe elemento audio");
                            }
                        }

                        function stopNotificationSound() {
                            const sound = document.getElementById("order-sound");
                            if (sound && !sound.paused) {
                                sound.pause();
                                sound.currentTime = 0;
                                console.log("NOTIFICACION: Sonido detenido");
                                isRinging = false;
                            }
                        }

                        function unlockAudio() {
                            if (audioUnlocked) return;
                            const sound = document.getElementById("order-sound");
                            if (sound) {
                                sound.play().then(() => {
                                    sound.pause();
                                    sound.currentTime = 0;
                                    audioUnlocked = true;
                                    console.log("NOTIFICACION: Audio Desbloqueado");
                                    document.removeEventListener("click", unlockAudio);
                                    
                                    // Si al desbloquear hay notificaciones pendientes, activar el timbre
                                    if (lastUnreadCount > 0) {
                                        playNotificationSound();
                                    }
                                }).catch(e => console.log("NOTIFICACION: Esperando clic para desbloquear..."));
                            }
                        }

                        document.addEventListener("click", unlockAudio);
                        
                        function checkNotifications() {
                            const badge = document.querySelector(".fi-topbar-database-notifications-btn .fi-badge") || 
                                          document.querySelector(".fi-topbar-database-notifications-btn [class*=\'badge\']") ||
                                          document.querySelector(".fi-icon-btn-badge-ctn .fi-badge") ||
                                          document.querySelector(".fi-icon-btn-badge");
                            
                            const count = badge ? parseInt(badge.textContent.trim().replace(/\D/g, "")) : 0;
                            
                            if (badge) {
                                console.log("NOTIFICACION: Badge hoy con valor:", count);
                            }
                            
                            if (lastUnreadCount === null) {
                                lastUnreadCount = count;
                                console.log("NOTIFICACION: Inicio con", count);
                                if (count > 0) {
                                    playNotificationSound();
                                }
                                return;
                            }

                            if (count > lastUnreadCount) {
                                console.log("NOTIFICACION: Nuevo pedido!", lastUnreadCount, "->", count);
                                lastUnreadCount = count;
                                playNotificationSound();
                            } else if (count < lastUnreadCount || count === 0) {
                                console.log("NOTIFICACION: Pedido leído o menor conteo. Cantidad actual:", count);
                                lastUnreadCount = count;
                                if (count === 0) {
                                    stopNotificationSound();
                                }
                            }
                        }

                        // Monitorear cambios cada 1 segundo para máxima respuesta
                        setInterval(checkNotifications, 1000);

                        // Sincronizar sonido con el refresco de Livewire (Polling de la tabla)
                        document.addEventListener(\'livewire:initialized\', () => {
                            Livewire.hook(\'request\', ({ succeed }) => {
                                succeed(() => {
                                    setTimeout(checkNotifications, 500);
                                });
                            });
                        });
                    </script>
                '),
            )
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->pages([
                \App\Filament\Pages\AdminDashboard::class,
                \App\Filament\Pages\VendorDashboard::class,
                \App\Filament\Pages\PriceControl::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
