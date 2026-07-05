<script>
    window.filamentVendorId = @json(auth()->user()->id);
    window.filamentAssignedOrdersCount = @json(\App\Models\Order::where('status', 'assigned')->whereHas('items.product', function ($q) { $q->where('user_id', auth()->id()); })->count());
</script>
