<?php

namespace App\Filament\Client\Resources\ClientProductResource\Pages;

use App\Filament\Client\Resources\ClientProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClientProduct extends EditRecord
{
    protected static string $resource = ClientProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
