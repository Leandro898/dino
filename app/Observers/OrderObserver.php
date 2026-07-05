<?php

namespace App\Observers;

use App\Models\Order;

class OrderObserver
{
    /**
     * Cuando cambia el estado del pedido, el Livewire polling
     * lo detectará automáticamente en el próximo ciclo (cada 3 segundos)
     */
    public function updated(Order $order): void
    {
        // El cambio se guarda automáticamente en DB
        // VendorOrdersTable con wire:poll.3s lo detectará en el próximo ciclo
        // No necesitamos hacer nada especial aquí
    }
}
