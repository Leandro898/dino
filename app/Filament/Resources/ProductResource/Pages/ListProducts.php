<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        // Permitir crear productos a admin y vendors
        if ($user && in_array($user->role, ['admin', 'vendor'])) {
            return [
                Actions\CreateAction::make(),
            ];
        }
        return [];
    }
}
