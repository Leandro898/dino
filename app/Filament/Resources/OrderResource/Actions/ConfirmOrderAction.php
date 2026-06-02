<?php

namespace App\Filament\Resources\OrderResource\Actions;

use App\Models\Order;
use App\Services\OrderNotificationService;
use Filament\Actions\Action;

class ConfirmOrderAction extends Action
{
    public static function make(?string $name = null): static
    {
        $name = $name ?? 'confirm_order';
        return parent::make($name)
            ->label('Confirmar pedido')
            ->color('success')
            ->visible(fn ($record) => $record->status === 'pending')
            ->action(function (Order $record) {
                $record->status = 'assigned';
                $record->save();
                // Notificar al vendedor
                app(OrderNotificationService::class)->notifyVendorOrderAssigned($record);
            });
    }
}
