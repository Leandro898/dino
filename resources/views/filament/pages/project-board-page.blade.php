<x-filament-panels::page>
    <div x-data="projectBoard()" x-init="initBoard()" class="w-full bg-gray-100 dark:bg-gray-900 border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-2xl shadow-inner relative overflow-auto" style="height: 75vh; min-height: 600px;">
        
        <!-- Botón Agregar -->
        <div class="sticky top-4 left-4 z-50 w-max" style="margin-bottom: -60px;">
            <x-filament::button @click="$wire.addNote()" color="primary" size="lg" class="shadow-lg">
                <svg slot="icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nueva Nota
            </x-filament::button>
        </div>

        <!-- Canvas -->
        <div 
            class="relative transition-transform duration-75 origin-top-left" 
            :style="`width: 3000px; height: 3000px; transform: scale(${scale}); background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 20px 20px;`"
            @touchstart="handleCanvasTouchStart"
            @touchmove="handleCanvasTouchMove"
            @wheel="handleWheel"
        >

        <!-- Notas -->
        <template x-for="(note, index) in notes" :key="note.id">
            <div
                class="absolute shadow-md rounded-xl overflow-hidden transition-shadow duration-200 flex flex-col resize"
                :class="isDragging === note.id ? 'shadow-2xl z-50 scale-105' : 'z-10 hover:shadow-xl'"
                :style="`transform: translate(${note.x}px, ${note.y}px); background-color: ${note.color}; width: ${note.width || 256}px; height: ${note.height || 200}px; min-width: 150px; min-height: 150px;`"
                @mouseup="stopResize($event, note)"
            >
                <!-- Header of Note -->
                <div 
                    class="flex justify-between items-center px-3 py-2 bg-black/10 border-b border-black/5 cursor-move"
                    @mousedown="startDrag($event, note)"
                    @touchstart="startDrag($event, note)"
                    style="touch-action: none;"
                >
                    <span class="text-xs font-black text-gray-700 uppercase tracking-wider" x-text="note.author"></span>
                    <button @click="$wire.deleteNote(note.id)" class="text-gray-600 hover:text-red-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <!-- Body of Note -->
                <div class="p-3 flex-1 flex flex-col">
                    <textarea
                        x-model="note.content"
                        @mousedown.stop
                        @change="$wire.updateNoteContent(note.id, note.content)"
                        @focus="isEditing = true"
                        @blur="isEditing = false"
                        class="w-full flex-1 bg-transparent border-none resize-none focus:ring-0 p-0 text-gray-800 placeholder-gray-500 font-medium h-full"
                        placeholder="Escribe una idea..."
                    ></textarea>
                </div>
            </div>
        </template>
        </div>
    </div>

    @script
    <script>
        Alpine.data('projectBoard', () => ({
            notes: $wire.entangle('notes'),
            isDragging: null,
            isEditing: false,
            scale: 1,
            initialDistance: 0,
            initialScale: 1,
            startX: 0,
            startY: 0,
            initialX: 0,
            initialY: 0,
            pollInterval: null,

            handleCanvasTouchStart(e) {
                if (e.touches.length === 2) {
                    this.initialDistance = Math.hypot(
                        e.touches[0].clientX - e.touches[1].clientX,
                        e.touches[0].clientY - e.touches[1].clientY
                    );
                    this.initialScale = this.scale;
                }
            },

            handleCanvasTouchMove(e) {
                if (e.touches.length === 2) {
                    e.preventDefault();
                    const currentDistance = Math.hypot(
                        e.touches[0].clientX - e.touches[1].clientX,
                        e.touches[0].clientY - e.touches[1].clientY
                    );
                    const newScale = this.initialScale * (currentDistance / this.initialDistance);
                    this.scale = Math.min(Math.max(0.3, newScale), 3);
                }
            },
            
            handleWheel(e) {
                if (e.ctrlKey || e.metaKey) {
                    e.preventDefault();
                    const newScale = this.scale - (e.deltaY * 0.005);
                    this.scale = Math.min(Math.max(0.3, newScale), 3);
                }
            },

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

                const clientX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
                const clientY = e.type.includes('touch') ? e.touches[0].clientY : e.clientY;

                this.startX = clientX;
                this.startY = clientY;

                const onMove = (e) => this.drag(e, note);
                const onEnd = () => {
                    document.removeEventListener('mousemove', onMove);
                    document.removeEventListener('mouseup', onEnd);
                    document.removeEventListener('touchmove', onMove);
                    document.removeEventListener('touchend', onEnd);
                    this.stopDrag(note);
                };

                document.addEventListener('mousemove', onMove);
                document.addEventListener('mouseup', onEnd);
                document.addEventListener('touchmove', onMove, { passive: false });
                document.addEventListener('touchend', onEnd);
            },

            drag(e, note) {
                if (!this.isDragging) return;
                
                if (e.type.includes('touch')) e.preventDefault();

                const clientX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
                const clientY = e.type.includes('touch') ? e.touches[0].clientY : e.clientY;

                const dx = (clientX - this.startX) / this.scale;
                const dy = (clientY - this.startY) / this.scale;
                note.x = this.initialX + dx;
                note.y = this.initialY + dy;
            },

            stopDrag(note) {
                if (this.isDragging) {
                    $wire.updateNotePosition(note.id, note.x, note.y);
                    this.isDragging = null;
                }
            },

            stopResize(e, note) {
                // If it's not the container, ignore
                if (e.target !== e.currentTarget) return;
                
                const newWidth = e.target.clientWidth;
                const newHeight = e.target.clientHeight;
                
                if (newWidth !== (note.width || 256) || newHeight !== (note.height || 200)) {
                    note.width = newWidth;
                    note.height = newHeight;
                    $wire.updateNoteSize(note.id, newWidth, newHeight);
                }
            }
        }))
    </script>
    @endscript
</x-filament-panels::page>
