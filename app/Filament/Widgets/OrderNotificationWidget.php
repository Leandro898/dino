<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Widgets\Widget;

class OrderNotificationWidget extends Widget
{
    protected static string $view = 'filament.widgets.order-notification-widget';

    public int $pendingOrdersCount = 0;
    public $recentOrders;

    public function mount(): void
    {
        $this->pendingOrdersCount = Order::whereIn('status', ['pending', 'pending_transfer', 'proof_sent'])->count();
        $this->recentOrders = Order::latest()->limit(5)->get();
    }

    protected function getViewData(): array
    {
        return [
            'pendingOrdersCount' => $this->pendingOrdersCount,
            'recentOrders' => $this->recentOrders,
            'ordersUrl' => OrderResource::getUrl('index'),
        ];
    }
}
