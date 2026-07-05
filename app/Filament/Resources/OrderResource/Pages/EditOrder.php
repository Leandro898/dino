<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function resolveRecord($key): Model
    {
        $record = parent::resolveRecord($key);
        
        if ($record && !$record->relationLoaded('items')) {
            $record->load(['items.product.user', 'user']);
        }
        
        return $record;
    }

    protected function getHeaderActions(): array
    {
        $actions = [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];

        if ($this->record && $this->record->status === 'pending') {
            $actions[] = \App\Filament\Resources\OrderResource\Actions\ConfirmOrderAction::make();
        }

        return $actions;
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Pedido actualizado')
            ->body('Los cambios se han guardado correctamente.')
            ->send();
    }
}
