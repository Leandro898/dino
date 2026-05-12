<?php

namespace App\Filament\Client\Resources\ClientOrderResource\Pages;

use App\Filament\Client\Resources\ClientOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewClientOrder extends ViewRecord
{
    protected static string $resource = ClientOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
