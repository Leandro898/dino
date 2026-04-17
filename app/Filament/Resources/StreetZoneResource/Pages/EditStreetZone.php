<?php

namespace App\Filament\Resources\StreetZoneResource\Pages;

use App\Filament\Resources\StreetZoneResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStreetZone extends EditRecord
{
    protected static string $resource = StreetZoneResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
