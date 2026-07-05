<div class="hidden">
    {{-- This component is only for handling Livewire events from broadcast --}}
    <audio id="notification-sound" preload="auto">
        <source src="{{ asset('sounds/notification.mp3') }}" type="audio/mpeg">
    </audio>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Echo listener for order assignments
    if (window.Echo) {
        const vendorId = document.querySelector('meta[name="vendor-id"]')?.content;
        
        if (vendorId) {
            window.Echo.private(`vendor.${vendorId}`)
                .listen('order-assigned', (data) => {
                    /* console.log */('🔔 Order assigned received:', data);
                    
                    // Play sound
                    const audio = document.getElementById('notification-sound');
                    if (audio) {
                        audio.currentTime = 0;
                        audio.play().catch(err => /* console.log */('Audio play failed:', err));
                    }
                    
                    // Dispatch Livewire event to refresh orders
                    Livewire.dispatch('refresh-orders', { order_id: data.order_id });
                });
        }
    }
});
</script>
