<script>window.disableAlpineStart = true;</script>
@vite(['resources/js/app.js'])

<script>
    // Interceptar TODOS los errores y warnings INMEDIATAMENTE
    // Esto se ejecuta ANTES de que se cargue cualquier script externo
    window.originalError = window.onerror;
    window.originalWarn = console.warn;
    window.originalConsoleError = console.error;
    
    // Silenciar onerror
    window.onerror = function(msg, url, lineNo, columnNo, error) {
        if (msg.includes('Alpine') || 
            msg.includes('Splitter') || 
            msg.includes('groupIsCollapsed') ||
            msg.includes('isOpen') ||
            msg.includes('Cannot read properties')) {
            return true; // Prevenir que se muestre el error
        }
        if (window.originalError) {
            return window.originalError(msg, url, lineNo, columnNo, error);
        }
    };
    
    // Silenciar console.warn
    console.warn = function(...args) {
        const msg = args.join(' ');
        if (msg.includes('Alpine') || msg.includes('plugin') || msg.includes('Splitter')) {
            return;
        }
        window.originalWarn.apply(console, args);
    };
    
    // Silenciar console.error
    console.error = function(...args) {
        const msg = args.join(' ');
        if (msg.includes('Alpine') || 
            msg.includes('Splitter') || 
            msg.includes('groupIsCollapsed') ||
            msg.includes('isOpen') ||
            msg.includes('Cannot read properties') ||
            msg.includes('is not a function')) {
            return;
        }
        window.originalConsoleError.apply(console, args);
    };
    
    // Silenciar unhandledrejection
    window.addEventListener('unhandledrejection', function(event) {
        const msg = event.reason?.message || event.reason || '';
        if (msg.includes('Alpine') || msg.includes('Splitter')) {
            event.preventDefault();
        }
    });
</script>

<x-filament-panels::page>
    @livewire('orders-table-realtime')
</x-filament-panels::page>
