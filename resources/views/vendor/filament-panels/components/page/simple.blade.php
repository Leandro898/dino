@props([
    'heading' => null,
    'subheading' => null,
])

{{-- DEBUG: simple.blade.php personalizado cargado --}}

@if (request()->is('panel/login'))
    <link rel="stylesheet" href="{{ asset('css/filament/filament/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/filament/support/support.css') }}">
    <link rel="stylesheet" href="{{ asset('css/filament/forms/forms.css') }}">
@endif

<div {{ $attributes->class(['fi-simple-page']) }}>
    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_PAGE_START, scopes: $this->getRenderHookScopes()) }}

    <section class="grid auto-cols-fr gap-y-6">
        <x-filament-panels::header.simple
            :heading="$heading ??= $this->getHeading()"
            :logo="$this->hasLogo()"
            :subheading="$subheading ??= $this->getSubHeading()"
        />

        {{ $slot }}
    </section>

    @if (! $this instanceof \Filament\Tables\Contracts\HasTable)
        <x-filament-actions::modals />
    @endif

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_PAGE_END, scopes: $this->getRenderHookScopes()) }}
</div>
