<?php

namespace App\Filament\Client\Resources\ClientProductResource\Pages;

use App\Filament\Client\Resources\ClientProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClientProducts extends ListRecords
{
    protected static string $resource = ClientProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
