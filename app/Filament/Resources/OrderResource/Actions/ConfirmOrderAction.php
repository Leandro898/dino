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
                error_log('🎬 ConfirmOrderAction::action() called for order: ' . $record->id);
                error_log('   Current status: ' . $record->status);
                
                $record->status = 'assigned';
                error_log('   Status changed to: ' . $record->status);
                
                error_log('   About to save order...');
                $record->save();
                error_log('   Order saved!');
                
                // Notificar al vendedor
                error_log('   Calling notifyVendorOrderAssigned...');
                app(OrderNotificationService::class)->notifyVendorOrderAssigned($record);
                error_log('   notifyVendorOrderAssigned completed!');
            });
    }
}
