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

    public function getTabs(): array
    {
        if (auth()->user()?->role === 'vendor') {
            return [];
        }
        $keywords = [
            'agua', 'agua con gas', 'agua saborizada', 'aquarius', 'aperol', 'bebida', 'bebida deportiva',
            'bebida energizante', 'bonaqua', 'branca', 'campari', 'cerveza', 'cinzano', 'coca', 'coca cola',
            'coca-cola', 'coñac', 'cognac', 'energético', 'energetico', 'espumante', 'fanta', 'fernet', 'gancia',
            'gaseosa', 'gaseos', 'gatorade', 'gin', 'isotonica', 'isotónica', 'jugo', 'licor', 'monster', 'moster',
            'néctar', 'nectar', 'pepsi', 'powerade', 'red bull', 'refresco', 'ron', 'saborizada', 'schweppes',
            'seven up', 'sidra', 'skyy', 'smirnoff', 'soda', 'sprite', 'tequila', 'terma', 'tonica', 'tónica',
            'vodka', 'vino', 'whiskey', 'whisky', '7up',
        ];

        return [
            'todos' => \Filament\Resources\Components\Tab::make('Todos'),
            'bebidas' => \Filament\Resources\Components\Tab::make('Bebidas')
                ->modifyQueryUsing(function (\Illuminate\Database\Eloquent\Builder $query) use ($keywords) {
                    $query->where('user_id', 6)
                          ->where(function (\Illuminate\Database\Eloquent\Builder $q) use ($keywords) {
                              $q->whereIn('external_category', ['bebidas', 'drinks']);
                              foreach ($keywords as $keyword) {
                                  $pattern = '%' . mb_strtolower($keyword) . '%';
                                  $q->orWhereRaw('LOWER(COALESCE(name, "")) LIKE ?', [$pattern])
                                    ->orWhereRaw('LOWER(COALESCE(description, "")) LIKE ?', [$pattern]);
                              }
                          });
                }),
            'almacen' => \Filament\Resources\Components\Tab::make('Almacén')
                ->modifyQueryUsing(function (\Illuminate\Database\Eloquent\Builder $query) use ($keywords) {
                    $query->where('user_id', 6)
                    ->where(function (\Illuminate\Database\Eloquent\Builder $q) use ($keywords) {
                        $q->where(function (\Illuminate\Database\Eloquent\Builder $categoryQuery) {
                            $categoryQuery->whereNull('external_category')
                                ->orWhereNotIn('external_category', ['bebidas', 'drinks']);
                        });
                        foreach ($keywords as $keyword) {
                            $pattern = '%' . mb_strtolower($keyword) . '%';
                            $q->whereRaw('LOWER(COALESCE(name, "")) NOT LIKE ?', [$pattern])
                              ->whereRaw('LOWER(COALESCE(description, "")) NOT LIKE ?', [$pattern]);
                        }
                    });
                }),
            'comidas' => \Filament\Resources\Components\Tab::make('Comidas')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('user_id', '!=', 6)->whereHas('user', fn($q) => $q->where('role', 'vendor'))),
            'farmacia' => \Filament\Resources\Components\Tab::make('Farmacia')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('external_source', 'pedidosya')->where('external_category', 'farmacia')),
        ];
    }
}
