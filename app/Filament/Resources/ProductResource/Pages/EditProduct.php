<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Auth\Access\AuthorizationException;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);
        
        // Validar que un vendor solo edite sus propios productos
        $user = auth()->user();
        if ($user && $user->role === 'vendor' && $this->record->user_id !== $user->id) {
            abort(403, 'No autorizado para editar este producto.');
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // No permitir cambiar el propietario del producto al editar
        $data['user_id'] = $this->record->user_id;

        return $data;
    }
}
