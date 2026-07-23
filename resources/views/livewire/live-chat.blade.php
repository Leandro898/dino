<div>
    <!-- Floating Chat Button when chat is closed -->
    @if(!$isOpen)
        <button wire:click="openChat" class="lc-fab">
            @if($customRequest && $customRequest->has_unread_user)
                <span class="lc-badge">!</span>
            @endif
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
        </button>
    @endif

    <!-- Chat Modal / Drawer -->
    @if($isOpen)
            <div class="lc-overlay" x-data x-trap="true">
                <div class="lc-modal">
                    
                    <!-- Header -->
                    <div class="lc-header">
                        <div class="lc-header-title">
                            @if($vendor_name)
                                <span>Chat con {{ $vendor_name }}</span>
                            @else
                                <span>Pedido Especial</span>
                            @endif
                        </div>
                        <button wire:click="closeChat" class="lc-close">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>

                    <!-- Messages -->
                    <div class="lc-messages" id="user-chat-messages">
                        <!-- Banner de Notificaciones -->
                        <div id="lc-push-banner" class="lc-push-banner" style="display: none;">
                            <p>🔔 ¿Quieres recibir avisos en tu celular cuando te coticemos?</p>
                            <button type="button" id="lc-enable-push-btn" class="lc-push-banner-btn">Activar Avisos</button>
                        </div>

                        @if(count($messages) === 0)
                            <div class="lc-empty-state">
                                @if($vendor_name)
                                    <p class="lc-empty-title">¿Tienes alguna consulta?</p>
                                    <p class="lc-empty-subtitle">Escríbele directamente a {{ $vendor_name }} antes de comprar.</p>
                                @else
                                    <p class="lc-empty-title">¿No encontraste lo que buscabas?</p>
                                    <p class="lc-empty-subtitle">Escribe lo que necesitas y te lo cotizamos al instante.</p>
                                @endif
                            </div>
                        @endif

                        @foreach($messages as $msg)
                            <div class="lc-msg-row {{ $msg['sender_type'] }}">
                                <div class="lc-bubble {{ $msg['is_system_message'] ? 'system' : $msg['sender_type'] }}">
                                    <p class="lc-bubble-text">{{ $msg['message'] }}</p>
                                    @if(!$msg['is_system_message'])
                                        <span class="lc-time">
                                            {{ \Carbon\Carbon::parse($msg['created_at'])->format('H:i') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Quote Action Area -->
                    @if($customRequest && $customRequest->status === 'quoted')
                        <div class="lc-quote-area">
                            <p class="lc-quote-title">¡Tienes una cotización lista!</p>
                            <p class="lc-quote-desc">{{ $customRequest->quote_description }} por <strong>${{ number_format($customRequest->quoted_price, 2, ',', '.') }}</strong></p>
                            <button wire:click="acceptQuote" class="lc-quote-btn">
                                Aceptar y Añadir al Carrito
                            </button>
                        </div>
                    @endif

                    <!-- Input Area -->
                    @if(!$customRequest || $customRequest->status !== 'accepted')
                        <div class="lc-input-area" x-data="{ localMessage: '' }">
                            <form @submit.prevent="if(localMessage.trim() !== '') { $wire.sendMessage(localMessage); localMessage = ''; $refs.msgInput.style.height='42px'; }" class="lc-input-form">
                                <textarea 
                                    x-ref="msgInput"
                                    x-model="localMessage"
                                    placeholder="Escribe tu consulta..." 
                                    class="lc-textarea"
                                    rows="1"
                                    x-data="{ resize() { $el.style.height = '42px'; $el.style.height = $el.scrollHeight + 'px' } }"
                                    x-init="resize()"
                                    @input="resize()"
                                    @keydown.enter.prevent="if(!$event.shiftKey) { $el.closest('form').dispatchEvent(new Event('submit', { cancelable: true, bubbles: true })); }"
                                ></textarea>
                                <button type="submit" class="lc-send-btn" wire:loading.attr="disabled" wire:target="sendMessage">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576 6.636 10.07Zm6.787-8.201L1.591 6.602l4.339 2.76 7.494-7.493Z"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    <script>
        document.addEventListener('livewire:initialized', () => {
            const scrollToBottom = () => {
                const el = document.getElementById('user-chat-messages');
                if (el) el.scrollTop = el.scrollHeight;
            };
            
            scrollToBottom();

            // Function to subscribe to Echo channel
            const subscribeToChat = (requestId) => {
                if (requestId && window.Echo) {
                    window.Echo.channel('custom-request.' + requestId)
                        .listen('.message.sent', () => {
                            Livewire.find('{{ $this->getId() }}').handleNewMessage();
                        });
                }
            };

            // Listen for the Livewire event
            Livewire.on('subscribe-to-chat', (event) => {
                const data = Array.isArray(event) ? event[0] : event;
                const requestId = data.requestId;
                subscribeToChat(requestId);
            });

            // Also subscribe on initial load if requestId is already set
            @if($customRequestId)
                subscribeToChat({{ $customRequestId }});
            @endif

            // --- Lógica de Notificaciones Push para Invitados ---
            const checkPushSubscription = () => {
                if ('serviceWorker' in navigator && 'PushManager' in window) {
                    const banner = document.getElementById('lc-push-banner');
                    if (banner && Notification.permission !== 'granted' && Notification.permission !== 'denied') {
                        banner.style.display = 'block';
                    }
                }
            };

            const urlBase64ToUint8Array = (base64String) => {
                const padding = '='.repeat((4 - base64String.length % 4) % 4);
                const base64 = (base64String + padding)
                    .replace(/\-/g, '+')
                    .replace(/_/g, '/');

                const rawData = window.atob(base64);
                const outputArray = new Uint8Array(rawData.length);

                for (let i = 0; i < rawData.length; ++i) {
                    outputArray[i] = rawData.charCodeAt(i);
                }
                return outputArray;
            };

            const subscribeGuestUser = () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(swReg => {
                        const vapidPublicKey = document.querySelector('meta[name="vapid-public-key"]')?.getAttribute('content');
                        if (!vapidPublicKey) return;

                        const convertedVapidKey = urlBase64ToUint8Array(vapidPublicKey);

                        return swReg.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: convertedVapidKey
                        });
                    })
                    .then(subscription => {
                        if (!subscription) return;
                        
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        
                        return fetch('/guest-push-subscribe', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token
                            },
                            body: JSON.stringify(subscription)
                        });
                    })
                    .then(response => {
                        if (response && response.ok) {
                            console.log('Guest push subscription saved successfully');
                            const banner = document.getElementById('lc-push-banner');
                            if (banner) banner.style.display = 'none';
                        }
                    })
                    .catch(err => {
                        console.error('Failed to subscribe guest user: ', err);
                    });
            };

            // Event delegation for the enable notifications button
            document.addEventListener('click', (e) => {
                if (e.target && e.target.id === 'lc-enable-push-btn') {
                    Notification.requestPermission().then(permission => {
                        if (permission === 'granted') {
                            subscribeGuestUser();
                        } else {
                            const banner = document.getElementById('lc-push-banner');
                            if (banner) banner.style.display = 'none';
                        }
                    });
                }
            });

            // Run checks initially and on every morph update
            checkPushSubscription();

            Livewire.hook('morph.updated', () => {
                scrollToBottom();
                checkPushSubscription();
            });
        });
    </script>
</div>
