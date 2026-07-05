import './bootstrap';

import Alpine from 'alpinejs';

// Silenciar TODOS los errores y warnings de Filament/Alpine
const filterMessage = (msg) => {
    const text = String(msg).toLowerCase();
    return text.includes('alpine') || 
           text.includes('splitter') || 
           text.includes('groupiscollapsed') ||
           text.includes('isopen') ||
           text.includes('cannot read properties') ||
           text.includes('plugin') ||
           text.includes('intersect') ||
           text.includes('collapse') ||
           text.includes('x-');
};

const originalWarn = console.warn;
const originalError = console.error;
const originalLog = console.log;

console.warn = function(...args) {
    if (filterMessage(args.join(' '))) return;
    originalWarn.apply(console, args);
};

console.error = function(...args) {
    if (filterMessage(args.join(' '))) return;
    originalError.apply(console, args);
};

// También filtrar logs que contengan "UncaughtTypeError" o similares
const origConsoleLog = console.log;
console.log = function(...args) {
    const msg = args.join(' ');
    if (msg.includes('UncaughtTypeError') || msg.includes('Alpine')) {
        return;
    }
    origConsoleLog.apply(console, args);
};

if (!window.disableAlpineStart) {
    window.Alpine = Alpine;
    Alpine.start();
}
