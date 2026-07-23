<?php

namespace App\Filament\Resources\DeliverySupportResource\Pages;

use App\Filament\Resources\DeliverySupportResource;
use Filament\Resources\Pages\ListRecords;

class ListDeliverySupports extends ListRecords
{
    protected static string $resource = DeliverySupportResource::class;

    protected $listeners = [
        'echo:orders,.support-message.sent' => 'handleNewMessage',
    ];

    public function handleNewMessage()
    {
        try {
            $this->dispatch('play-notification-sound');
        } catch (\Exception $e) {}
        
        $this->dispatch('$refresh');
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
