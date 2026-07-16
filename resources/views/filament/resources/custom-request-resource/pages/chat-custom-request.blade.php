<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Chat Area -->
        <div class="col-span-1 md:grid-cols-2 lg:col-span-2 space-y-4">
            <x-filament::section>
                <x-slot name="heading">
                    Mensajes
                </x-slot>

                <div class="space-y-4 max-h-[500px] overflow-y-auto p-4 bg-gray-50 rounded-lg dark:bg-gray-900" id="chat-messages">
                    @forelse($record->messages as $msg)
                        <div class="flex {{ $msg->sender_type === 'admin' ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[70%] rounded-lg px-4 py-2 {{ $msg->is_system_message ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300 text-sm font-semibold' : ($msg->sender_type === 'admin' ? 'text-white' : 'bg-white dark:bg-gray-800 border dark:border-gray-700 text-gray-800 dark:text-gray-200') }}"
                                 style="{{ $msg->sender_type === 'admin' && !$msg->is_system_message ? 'background-color: rgba(var(--primary-600), 1); background-color: var(--fi-color-primary-600, #d97706);' : '' }}">
                                <p>{{ $msg->message }}</p>
                                <span class="text-[10px] opacity-70 block mt-1 {{ $msg->sender_type === 'admin' && !$msg->is_system_message ? 'text-right' : 'text-left' }}">{{ $msg->created_at->format('H:i') }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 text-sm">No hay mensajes aún.</p>
                    @endforelse
                </div>

                <form wire:submit.prevent="sendMessage" class="mt-4 flex gap-2">
                    <x-filament::input.wrapper class="flex-1">
                        <x-filament::input
                            type="text"
                            wire:model="message"
                            placeholder="Escribe un mensaje..."
                        />
                    </x-filament::input.wrapper>
                    
                    <x-filament::button type="submit">
                        Enviar
                    </x-filament::button>
                </form>
            </x-filament::section>
        </div>

        <!-- Quote Area -->
        <div class="col-span-1 space-y-4">
            <x-filament::section>
                <x-slot name="heading">
                    Cotizar y Añadir
                </x-slot>

                <div class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Al enviar la cotización, el cliente podrá aceptarla y se añadirá directamente a su carrito.
                </div>

                <form wire:submit.prevent="sendQuote" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Descripción del producto</label>
                        <x-filament::input.wrapper>
                            <x-filament::input
                                type="text"
                                wire:model="quoteDescription"
                                placeholder="Ej: 1kg de Helado Grido"
                                required
                            />
                        </x-filament::input.wrapper>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Precio Total ($)</label>
                        <x-filament::input.wrapper>
                            <x-filament::input
                                type="number"
                                wire:model="quotePrice"
                                placeholder="Ej: 15000"
                                required
                                min="0"
                                step="0.01"
                            />
                        </x-filament::input.wrapper>
                    </div>

                    <x-filament::button type="submit" color="success" class="w-full">
                        Enviar Cotización al Cliente
                    </x-filament::button>
                </form>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">
                    Detalles del Pedido
                </x-slot>
                <ul class="text-sm space-y-2">
                    <li><strong>ID:</strong> #{{ $record->id }}</li>
                    <li><strong>Estado:</strong> 
                        <span class="inline-flex items-center rounded-md bg-{{ $record->status === 'accepted' ? 'green' : ($record->status === 'quoted' ? 'blue' : 'yellow') }}-50 px-2 py-1 text-xs font-medium text-{{ $record->status === 'accepted' ? 'green' : ($record->status === 'quoted' ? 'blue' : 'yellow') }}-700 ring-1 ring-inset ring-{{ $record->status === 'accepted' ? 'green' : ($record->status === 'quoted' ? 'blue' : 'yellow') }}-600/20">
                            {{ strtoupper($record->status) }}
                        </span>
                    </li>
                    <li><strong>Creado:</strong> {{ $record->created_at->format('d/m/Y H:i') }}</li>
                    @if($record->quoted_price)
                        <li><strong>Precio Cotizado:</strong> ${{ number_format($record->quoted_price, 2, ',', '.') }}</li>
                        <li><strong>Descripción:</strong> {{ $record->quote_description }}</li>
                    @endif
                </ul>
            </x-filament::section>
        </div>

    </div>
    
    <script>
        document.addEventListener('livewire:initialized', () => {
            const chatMessages = document.getElementById('chat-messages');
            if (chatMessages) {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            Livewire.hook('morph.updated', ({ component, el }) => {
                const chatMessages = document.getElementById('chat-messages');
                if (chatMessages) {
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            });
        });
    </script>
</x-filament-panels::page>
