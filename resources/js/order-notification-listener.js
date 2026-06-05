/**
 * Order Notification Listener
 * Listens for order-assigned events from Reverb and updates the UI
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('🎧 OrderNotificationListener initialized');
    
    if (!window.Echo) {
        console.error('❌ Echo not initialized');
        return;
    }

    const vendorIdEl = document.querySelector('meta[name="vendor-id"]');
    const vendorId = vendorIdEl?.content;
    
    if (!vendorId) {
        console.log('⚠️  No vendor-id meta tag found - skipping Echo listener');
        return;
    }

    console.log(`🎯 Subscribing to vendor.${vendorId}`);

    // Subscribe to vendor's private channel
    window.Echo.private(`vendor.${vendorId}`)
        .listen('order-assigned', (data) => {
            console.log('🔔 Order assigned event received:', data);

            // Play notification sound
            playNotificationSound();

            // Refresh the Filament table (Livewire)
            if (window.Livewire) {
                // This will trigger a table refresh in Filament
                window.Livewire.dispatch('refresh-orders', { order_id: data.order_id });
                console.log('📊 Livewire refresh dispatched');
            } else {
                // Fallback: reload page
                console.log('⚠️  Livewire not found, reloading page');
                location.reload();
            }
        })
        .error((error) => {
            console.error('❌ Error subscribing to vendor channel:', error);
        });

    // Fallback: Reload page if Echo doesn't work
    setTimeout(() => {
        if (!window.Echo?.connection?.connected) {
            console.warn('⚠️  Echo connection not established, using polling fallback');
            // Add polling back as fallback
            setInterval(() => {
                if (window.Livewire) {
                    window.Livewire.dispatch('refresh-orders');
                }
            }, 10000);
        }
    }, 5000);
});

/**
 * Play notification sound
 */
function playNotificationSound() {
    // Try to play an audio file
    const audioPath = '/sounds/notification.mp3';
    
    // Create audio element if it doesn't exist
    let audio = document.getElementById('order-notification-audio');
    if (!audio) {
        audio = new Audio(audioPath);
        audio.id = 'order-notification-audio';
        document.body.appendChild(audio);
    }

    // Reset and play
    audio.currentTime = 0;
    
    const playPromise = audio.play();
    if (playPromise !== undefined) {
        playPromise
            .then(() => {
                console.log('🔊 Notification sound played');
            })
            .catch(error => {
                console.warn('⚠️  Audio play failed (user may not have interacted with page):', error);
                // Fallback: Use Web Audio API for a simple beep
                playBeep();
            });
    }
}

/**
 * Fallback: Play a simple beep using Web Audio API
 */
function playBeep() {
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);

        oscillator.frequency.value = 1000;
        oscillator.type = 'sine';

        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);

        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.5);

        console.log('🔊 Beep sound played via Web Audio API');
    } catch (e) {
        console.warn('⚠️  Could not play beep:', e);
    }
}
