<script>
    window.filamentVendorId = @json(auth()->user()->id);
    window.filamentAssignedOrdersCount = @json($recentOrders->count() ?? 0);
</script>
@vite(['resources/js/filament-vendor-order-polling.js'])
