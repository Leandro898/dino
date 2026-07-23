<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Chat Area -->
        <div class="col-span-1 md:grid-cols-2 lg:col-span-2 space-y-4">
            <x-filament::section>
                <x-slot name="heading">
                    Soporte con {{ $record->name }}
                </x-slot>

                <div class="space-y-4 max-h-[500px] overflow-y-auto p-4 bg-gray-50 rounded-lg dark:bg-gray-900" id="support-chat-messages">
                    @forelse($record->supportMessages()->oldest()->get() as $msg)
                        <div class="flex {{ $msg->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[70%] rounded-lg px-4 py-2 {{ $msg->sender_id === auth()->id() ? 'text-white' : 'bg-white dark:bg-gray-800 border dark:border-gray-700 text-gray-800 dark:text-gray-200' }}"
                                 style="{{ $msg->sender_id === auth()->id() ? 'background-color: rgba(var(--primary-600), 1); background-color: var(--fi-color-primary-600, #d97706);' : '' }}">
                                <p>{{ $msg->message }}</p>
                                <span class="text-[10px] opacity-70 block mt-1 {{ $msg->sender_id === auth()->id() ? 'text-right' : 'text-left' }}">{{ $msg->created_at->format('H:i') }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 text-sm">No hay mensajes de soporte aún.</p>
                    @endforelse
                </div>

                <form wire:submit.prevent="sendMessage" class="mt-4 flex gap-2">
                    <x-filament::input.wrapper class="flex-1">
                        <x-filament::input
                            type="text"
                            wire:model="message"
                            placeholder="Escribe un mensaje de respuesta..."
                        />
                    </x-filament::input.wrapper>
                    
                    <x-filament::button type="submit">
                        Enviar
                    </x-filament::button>
                </form>
            </x-filament::section>
        </div>

        <!-- Info Area -->
        <div class="col-span-1 space-y-4">
            <x-filament::section>
                <x-slot name="heading">
                    Detalles del Repartidor
                </x-slot>
                <ul class="text-sm space-y-2">
                    <li><strong>ID:</strong> #{{ $record->id }}</li>
                    <li><strong>Nombre:</strong> {{ $record->name }}</li>
                    <li><strong>Email:</strong> {{ $record->email }}</li>
                    <li><strong>Rol:</strong> 
                        <span class="inline-flex items-center rounded-md bg-purple-50 px-2 py-1 text-xs font-medium text-purple-700 ring-1 ring-inset ring-purple-700/10">
                            {{ strtoupper($record->role) }}
                        </span>
                    </li>
                    <li><strong>Aprobado:</strong> 
                        <span class="inline-flex items-center rounded-md bg-{{ $record->is_approved ? 'green' : 'red' }}-50 px-2 py-1 text-xs font-medium text-{{ $record->is_approved ? 'green' : 'red' }}-700 ring-1 ring-inset ring-{{ $record->is_approved ? 'green' : 'red' }}-600/20">
                            {{ $record->is_approved ? 'SÍ' : 'NO' }}
                        </span>
                    </li>
                    <li><strong>Miembro desde:</strong> {{ $record->created_at ? $record->created_at->format('d/m/Y') : 'N/A' }}</li>
                </ul>
            </x-filament::section>
        </div>

    </div>
    
    <script>
        document.addEventListener('livewire:initialized', () => {
            const chatMessages = document.getElementById('support-chat-messages');
            if (chatMessages) {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            Livewire.hook('morph.updated', ({ component, el }) => {
                const chatMessages = document.getElementById('support-chat-messages');
                if (chatMessages) {
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            });
        });
    </script>
</x-filament-panels::page>
