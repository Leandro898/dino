<?php

namespace App\Filament\Client\Resources\ClientProductResource\Pages;

use App\Filament\Client\Resources\ClientProductResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListClientProducts extends ListRecords
{
    protected static string $resource = ClientProductResource::class;

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        if (!$this->shouldShowMasivoTabs()) {
            return [];
        }

        return [
            'all' => Tab::make('Todos los productos'),
            'beverages' => Tab::make('Bebidas')
                ->modifyQueryUsing(function (Builder $query): Builder {
                    $keywords = [
                        'coca',
                        'gaseosa',
                        'fernet',
                        'cerveza',
                        'vino',
                        'agua',
                        'sprite',
                        'pepsi',
                        'campari',
                        'smirnoff',
                        'gancia',
                        'cinzano',
                        'vodka',
                        'gin',
                        'whisky',
                        'aperol',
                        'skyy',
                    ];

                    return $query->where(function (Builder $tabQuery) use ($keywords) {
                        $tabQuery->whereIn('external_category', ['bebidas', 'drinks']);

                        foreach ($keywords as $keyword) {
                            $tabQuery->orWhere('name', 'like', "%{$keyword}%");
                        }
                    });
                }),
        ];
    }

    private function shouldShowMasivoTabs(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        return (int) $user->id === 6
            || strcasecmp((string) $user->email, 'masivo@baritienda.online') === 0;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
