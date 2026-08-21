<x-filament-panels::page>
    <div x-data="projectBoard()" x-init="initBoard()" class="w-full bg-gray-100 dark:bg-gray-900 border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-2xl shadow-inner relative overflow-hidden" style="height: 75vh; min-height: 600px; background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 20px 20px;">
        
        <!-- Botón Agregar -->
        <div class="absolute top-4 left-4 z-50">
            <x-filament::button @click="$wire.addNote()" color="primary" size="lg" class="shadow-lg">
                <svg slot="icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nueva Nota
            </x-filament::button>
        </div>

        <!-- Notas -->
        <template x-for="(note, index) in notes" :key="note.id">
            <div
                class="absolute w-64 shadow-md rounded-xl overflow-hidden cursor-move transition-shadow duration-200"
                :class="isDragging === note.id ? 'shadow-2xl z-50 scale-105' : 'z-10 hover:shadow-xl'"
                :style="`transform: translate(${note.x}px, ${note.y}px); background-color: ${note.color};`"
                @mousedown="startDrag($event, note)"
            >
                <!-- Header of Note -->
                <div class="flex justify-between items-center px-3 py-2 bg-black/10 border-b border-black/5">
                    <span class="text-xs font-black text-gray-700 uppercase tracking-wider" x-text="note.author"></span>
                    <button @click="$wire.deleteNote(note.id)" class="text-gray-600 hover:text-red-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <!-- Body of Note -->
                <div class="p-3">
                    <textarea
                        x-model="note.content"
                        @mousedown.stop
                        @change="$wire.updateNoteContent(note.id, note.content)"
                        @focus="isEditing = true"
                        @blur="isEditing = false"
                        class="w-full h-36 bg-transparent border-none resize-none focus:ring-0 p-0 text-gray-800 placeholder-gray-500 font-medium"
                        placeholder="Escribe una idea..."
                    ></textarea>
                </div>
            </div>
        </template>
    </div>

    @script
    <script>
        Alpine.data('projectBoard', () => ({
            notes: $wire.entangle('notes'),
            isDragging: null,
            isEditing: false,
            startX: 0,
            startY: 0,
            initialX: 0,
            initialY: 0,
            pollInterval: null,

            initBoard() {
                // Sincronización automática cada 3 segundos si nadie está editando/arrastrando
                this.pollInterval = setInterval(() => {
                    if (!this.isDragging && !this.isEditing) {
                        $wire.loadNotes();
                    }
                }, 3000);
            },

            startDrag(e, note) {
                // Evitar arrastrar si se hace clic en el textarea o botón
                if (e.target.tagName.toLowerCase() === 'textarea' || e.target.tagName.toLowerCase() === 'button' || e.target.closest('button')) {
                    return;
                }
                
                this.isDragging = note.id;
                this.initialX = note.x;
                this.initialY = note.y;
                this.startX = e.clientX;
                this.startY = e.clientY;

                const onMouseMove = (e) => this.drag(e, note);
                const onMouseUp = () => {
                    document.removeEventListener('mousemove', onMouseMove);
                    document.removeEventListener('mouseup', onMouseUp);
                    this.stopDrag(note);
                };

                document.addEventListener('mousemove', onMouseMove);
                document.addEventListener('mouseup', onMouseUp);
            },

            drag(e, note) {
                if (!this.isDragging) return;
                const dx = e.clientX - this.startX;
                const dy = e.clientY - this.startY;
                note.x = this.initialX + dx;
                note.y = this.initialY + dy;
            },

            stopDrag(note) {
                if (this.isDragging) {
                    $wire.updateNotePosition(note.id, note.x, note.y);
                    this.isDragging = null;
                }
            }
        }))
    </script>
    @endscript
</x-filament-panels::page>
