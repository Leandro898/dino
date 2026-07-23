<x-filament-panels::page>
    <div class="bg-white dark:bg-[#161615] p-6 rounded-2xl shadow-sm border border-gray-200 dark:border-[#2a2a2a]">
        <form wire:submit.prevent="submit" class="space-y-6">
            {{ $this->form }}

            <div class="flex justify-start pt-4">
                <x-filament::button type="submit" size="lg" class="bg-indigo-600 hover:bg-indigo-700">
                    Guardar Cambios
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
