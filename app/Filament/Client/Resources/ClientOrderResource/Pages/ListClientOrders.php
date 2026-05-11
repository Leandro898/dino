<?php

namespace App\Filament\Client\Resources\ClientOrderResource\Pages;

use App\Filament\Client\Resources\ClientOrderResource;
use Filament\Resources\Pages\ListRecords;

class ListClientOrders extends ListRecords
{
    protected static string $resource = ClientOrderResource::class;
}
