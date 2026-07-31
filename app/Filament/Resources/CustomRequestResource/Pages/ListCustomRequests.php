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
        return [
            Actions\CreateAction::make()
                ->label('Crear Pedido')
                ->modalHeading('Crear Pedido')
                ->createAnother(false)
                ->mutateFormDataUsing(function (array $data): array {
                    $data['session_id'] = \Illuminate\Support\Str::uuid()->toString();
                    
                    $user = auth()->user();
                    if ($user && $user->role === 'vendor') {
                        $data['vendor_id'] = $user->id;
                    }
                    
                    return $data;
                }),
        ];
    }
}
