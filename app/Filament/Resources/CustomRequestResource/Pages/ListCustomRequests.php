<?php

namespace App\Filament\Resources\CustomRequestResource\Pages;

use App\Filament\Resources\CustomRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomRequests extends ListRecords
{
    protected static string $resource = CustomRequestResource::class;

    protected $listeners = [
        'echo:orders,.message.sent' => 'handleNewMessage',
    ];

    public function handleNewMessage()
    {
        $this->dispatch('$refresh');
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
