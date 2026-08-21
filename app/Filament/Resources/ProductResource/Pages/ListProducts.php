<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        if ($user && in_array($user->role, ['admin', 'vendor'])) {
            return [
                Actions\CreateAction::make(),
            ];
        }
        return [];
    }

    public function getTabs(): array
    {
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
            'todos' => Tab::make('Todos'),
            'bebidas' => Tab::make('Bebidas')
                ->modifyQueryUsing(function (Builder $query) use ($keywords) {
                    $query->whereHas('user', fn($q) => $q->where('role', '!=', 'vendor'))
                          ->where(function (Builder $q) use ($keywords) {
                              $q->whereIn('external_category', ['bebidas', 'drinks']);
                              $q->orWhere(function (Builder $subQ) use ($keywords) {
                                  $subQ->whereNull('external_category');
                                  $subQ->where(function (Builder $keywordQ) use ($keywords) {
                                      foreach ($keywords as $keyword) {
                                          $pattern = '%' . mb_strtolower($keyword) . '%';
                                          $keywordQ->orWhereRaw('LOWER(COALESCE(name, "")) LIKE ?', [$pattern])
                                                   ->orWhereRaw('LOWER(COALESCE(description, "")) LIKE ?', [$pattern]);
                                      }
                                  });
                              });
                          });
                }),
            'almacen' => Tab::make('Almacén')
                ->modifyQueryUsing(function (Builder $query) use ($keywords) {
                    $query->whereHas('user', fn($q) => $q->where('role', '!=', 'vendor'))
                          ->where(function (Builder $q) use ($keywords) {
                              $q->where('external_category', 'almacen');
                              $q->orWhere(function (Builder $subQ) use ($keywords) {
                                  $subQ->where(function (Builder $categoryQuery) {
                                      $categoryQuery->whereNull('external_category')
                                          ->orWhereNotIn('external_category', ['bebidas', 'drinks', 'almacen', 'comidas', 'farmacia']);
                                  });
                                  foreach ($keywords as $keyword) {
                                      $pattern = '%' . mb_strtolower($keyword) . '%';
                                      $subQ->whereRaw('LOWER(COALESCE(name, "")) NOT LIKE ?', [$pattern])
                                        ->whereRaw('LOWER(COALESCE(description, "")) NOT LIKE ?', [$pattern]);
                                  }
                              });
                          });
                }),
            'comidas' => Tab::make('Comidas')
                ->modifyQueryUsing(function (Builder $query) {
                    $query->where(function (Builder $q) {
                        $q->where('external_category', 'comidas')
                          ->orWhere(function (Builder $subQ) {
                              $subQ->where('user_id', '!=', 6)->whereHas('user', fn($userQ) => $userQ->where('role', 'vendor'));
                          });
                    });
                }),
            'farmacia' => Tab::make('Farmacia')
                ->modifyQueryUsing(function (Builder $query) {
                    $query->where('external_category', 'farmacia');
                }),
        ];
    }
}
