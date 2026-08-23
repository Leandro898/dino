<?php

namespace App\Filament\Resources\RiderResource\Pages;

use App\Filament\Resources\RiderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRider extends CreateRecord
{
    protected static string $resource = RiderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['role'] = 'delivery';
    
        return $data;
    }
}
