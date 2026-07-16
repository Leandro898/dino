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

window.addEventListener('play-notification-sound', () => {
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        
        const playTone = (freq, startTime, duration) => {
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            oscillator.type = 'sine';
            oscillator.frequency.setValueAtTime(freq, startTime);
            
            gainNode.gain.setValueAtTime(0, startTime);
            gainNode.gain.linearRampToValueAtTime(0.3, startTime + 0.02);
            gainNode.gain.exponentialRampToValueAtTime(0.01, startTime + duration);
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.start(startTime);
            oscillator.stop(startTime + duration);
        };
        
        const now = audioContext.currentTime;
        // Doble tono (bloop-bleep) característico para el cliente
        playTone(500, now, 0.1);
        playTone(750, now + 0.15, 0.15);
    } catch (e) {
        console.warn('Notification sound failed', e);
    }
});
