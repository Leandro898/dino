// Server-Sent Events listener for real-time vendor notifications

const vendorId = window.vendorNotificationUserId;

if (vendorId) {
    console.log('🔔 SSE Notification Stream initialized for vendor:', vendorId);

    // Create audio element for notification sound
    const bell = document.createElement('audio');
    bell.src = '/sounds/bell.wav';
    bell.preload = 'auto';
    bell.volume = 0.85;
    bell.setAttribute('aria-hidden', 'true');
    bell.style.display = 'none';

    const appendBell = () => {
        if (!document.body.contains(bell)) {
            document.body.appendChild(bell);
        }
    };

    if (document.body) {
        appendBell();
    } else {
        document.addEventListener('DOMContentLoaded', appendBell, { once: true });
    }

    let audioUnlocked = false;
    let pendingRing = false;

    function unlockBellAudio() {
        if (audioUnlocked) {
            return;
        }

        bell.play().then(() => {
            bell.pause();
            bell.currentTime = 0;
            audioUnlocked = true;

            if (pendingRing) {
                pendingRing = false;
                ringBell();
            }
        }).catch(() => {});
    }

    function ringBell() {
        if (!audioUnlocked) {
            pendingRing = true;
            return;
        }

        bell.currentTime = 0;
        bell.play().catch(() => {});
    }

    ['pointerdown', 'click', 'keydown', 'touchstart'].forEach((eventName) => {
        document.addEventListener(eventName, unlockBellAudio, { capture: true, passive: true });
    });

    // Connect to SSE stream
    const eventSource = new EventSource('/notifications/stream');
    let reconnectAttempts = 0;
    const maxReconnectAttempts = 5;

    eventSource.addEventListener('notification-count', (event) => {
        const data = JSON.parse(event.data);
        console.log('🔔 Notification count updated:', data.unread_count);
        // Optionally update bell icon count here
    });

    eventSource.addEventListener('order-assigned', (event) => {
        const data = JSON.parse(event.data);
        console.log('📦 Order assigned event received:', data);
        
        // Ring the bell
        ringBell();
        
        // Trigger table refresh in Filament
        if (window.Livewire) {
            console.log('🔄 Dispatching refresh-orders-table event');
            window.Livewire.dispatch('refresh-orders-table', { order_id: data.order_id });
        } else {
            console.warn('⚠️ Livewire not available for refresh');
        }
    });

    eventSource.addEventListener('connection-closed', (event) => {
        console.warn('⚠️ Notification stream closed:', event.data);
        reconnectAttempts++;
        if (reconnectAttempts < maxReconnectAttempts) {
            console.log(`🔄 Reconnecting... (attempt ${reconnectAttempts}/${maxReconnectAttempts})`);
            setTimeout(() => {
                location.reload();
            }, 3000);
        }
    });

    eventSource.onerror = (error) => {
        console.error('❌ EventSource error:', error);
        reconnectAttempts++;
        if (reconnectAttempts >= maxReconnectAttempts) {
            console.error('❌ Max reconnection attempts reached');
        }
        eventSource.close();
    };

    console.log('✅ SSE stream listener attached');

    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        eventSource.close();
    });
} else {
    console.log('⚠️ Vendor notification stream skipped (not a vendor user)');
}
