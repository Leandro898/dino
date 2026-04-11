<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Asignar el usuario actual como propietario del producto
        $data['user_id'] = auth()->id();
        $data['description'] = $data['description'] ?? '';

        return $data;
    }
}
