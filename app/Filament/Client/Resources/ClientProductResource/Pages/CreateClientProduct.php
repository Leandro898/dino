<?php

namespace App\Filament\Client\Resources\ClientProductResource\Pages;

use App\Filament\Client\Resources\ClientProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateClientProduct extends CreateRecord
{
    protected static string $resource = ClientProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }
}
