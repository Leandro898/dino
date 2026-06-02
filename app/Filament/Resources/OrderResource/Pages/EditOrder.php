<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];

        // Agregar acción de confirmación si el pedido está pendiente
        if ($this->record && $this->record->status === 'pending') {
            $actions[] = \App\Filament\Resources\OrderResource\Actions\ConfirmOrderAction::make();
        }

        return $actions;
    }
}
