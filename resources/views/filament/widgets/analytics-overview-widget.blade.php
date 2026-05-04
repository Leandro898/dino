<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Analytics — Visitas reales</x-slot>
        <x-slot name="description">Solo usuarios reales (bots filtrados automáticamente)</x-slot>

        {{-- ─── Tarjetas de resumen ─────────────────────────────────────────── --}}
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
            {{-- Hoy --}}
            <div class="rounded-xl bg-blue-50 dark:bg-blue-900/20 p-4 border border-blue-100 dark:border-blue-800">
                <p class="text-xs font-semibold uppercase tracking-widest text-blue-500 mb-1">Hoy</p>
                <p class="text-3xl font-bold text-blue-700 dark:text-blue-300">{{ number_format($todayViews) }}</p>
                <p class="text-sm text-blue-500 mt-1">{{ number_format($todayUnique) }} sesiones únicas</p>
            </div>

            {{-- Esta semana --}}
            <div class="rounded-xl bg-purple-50 dark:bg-purple-900/20 p-4 border border-purple-100 dark:border-purple-800">
                <p class="text-xs font-semibold uppercase tracking-widest text-purple-500 mb-1">Esta semana</p>
                <p class="text-3xl font-bold text-purple-700 dark:text-purple-300">{{ number_format($weekViews) }}</p>
                <p class="text-sm text-purple-500 mt-1">{{ number_format($weekUnique) }} sesiones únicas</p>
            </div>

            {{-- Este mes --}}
            <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/20 p-4 border border-emerald-100 dark:border-emerald-800 col-span-2 md:col-span-1">
                <p class="text-xs font-semibold uppercase tracking-widest text-emerald-500 mb-1">Este mes</p>
                <p class="text-3xl font-bold text-emerald-700 dark:text-emerald-300">{{ number_format($monthViews) }}</p>
                <p class="text-sm text-emerald-500 mt-1">{{ number_format($monthUnique) }} sesiones únicas</p>
            </div>
        </div>

        {{-- ─── Últimos 14 días ─────────────────────────────────────────────── --}}
        <div class="mb-6">
            <p class="text-sm font-semibold text-gray-600 dark:text-gray-300 mb-3">Visitas últimos 14 días</p>
            @php $maxVal = collect($days)->max('total') ?: 1; @endphp
            <div class="flex items-end gap-1 h-24">
                @foreach ($days as $day)
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <div
                            class="w-full rounded-t bg-purple-400 dark:bg-purple-500 hover:bg-purple-600 transition-all"
                            style="height: {{ max(4, round(($day['total'] / $maxVal) * 80)) }}px"
                            title="{{ $day['date'] }}: {{ $day['total'] }} visitas ({{ $day['unique'] }} únicas)"
                        ></div>
                        <span class="text-[9px] text-gray-400 hidden md:block">{{ $day['date'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ─── Tablas de desglose ──────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- Top páginas --}}
            <div>
                <p class="text-sm font-semibold text-gray-600 dark:text-gray-300 mb-2">Páginas más vistas (mes)</p>
                <ul class="space-y-1">
                    @forelse ($topPages as $page)
                        <li class="flex justify-between text-sm">
                            <span class="truncate text-gray-700 dark:text-gray-300 max-w-[75%]">
                                /{{ $page->path === '' ? '(inicio)' : $page->path }}
                            </span>
                            <span class="font-bold text-gray-900 dark:text-white">{{ number_format($page->total) }}</span>
                        </li>
                    @empty
                        <li class="text-sm text-gray-400">Sin datos aún</li>
                    @endforelse
                </ul>
            </div>

            {{-- Dispositivos --}}
            <div>
                <p class="text-sm font-semibold text-gray-600 dark:text-gray-300 mb-2">Dispositivos (mes)</p>
                <ul class="space-y-1">
                    @forelse ($devices as $device)
                        <li class="flex justify-between text-sm">
                            <span class="capitalize text-gray-700 dark:text-gray-300 flex items-center gap-1">
                                @if($device->device === 'mobile') 📱
                                @elseif($device->device === 'tablet') 📟
                                @else 💻 @endif
                                {{ $device->device ?? 'desconocido' }}
                            </span>
                            <span class="font-bold text-gray-900 dark:text-white">{{ number_format($device->total) }}</span>
                        </li>
                    @empty
                        <li class="text-sm text-gray-400">Sin datos aún</li>
                    @endforelse
                </ul>
            </div>

            {{-- Fuentes de tráfico --}}
            <div>
                <p class="text-sm font-semibold text-gray-600 dark:text-gray-300 mb-2">Fuente de tráfico (mes)</p>
                <ul class="space-y-1">
                    @forelse ($sources as $source)
                        <li class="flex justify-between text-sm">
                            <span class="capitalize text-gray-700 dark:text-gray-300">{{ $source->source }}</span>
                            <span class="font-bold text-gray-900 dark:text-white">{{ number_format($source->total) }}</span>
                        </li>
                    @empty
                        <li class="text-sm text-gray-400">Sin datos aún</li>
                    @endforelse
                </ul>
            </div>

        </div>

    </x-filament::section>
</x-filament-widgets::widget>
