<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StreetZoneResource\Pages;
use App\Models\ShippingZone;
use App\Models\StreetZone;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StreetZoneResource extends Resource
{
    protected static ?string $model = StreetZone::class;

    protected static ?string $navigationIcon  = 'heroicon-o-map-pin';
    protected static ?string $navigationLabel = 'Calles por zona';
    protected static ?string $navigationGroup = 'Envíos';
    protected static ?int    $navigationSort  = 2;

    public static function form(Form $form): Form
    {
        $zones = ShippingZone::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['zone_key', 'label', 'price'])
            ->mapWithKeys(fn ($zone) => [
                $zone->zone_key => $zone->label . ' ($' . number_format((int) $zone->price, 0, ',', '.') . ')',
            ])
            ->toArray();

        if (empty($zones)) {
            $zones = collect(config('shipping.zones', []))
                ->mapWithKeys(fn ($zone, $key) => [$key => $zone['label'] . ' ($' . number_format($zone['price'], 0, ',', '.') . ')'])
                ->toArray();
        }

        return $form->schema([
            Forms\Components\TextInput::make('street_name')
                ->label('Nombre de calle (normalizado)')
                ->helperText('Minúsculas, sin tildes, sin prefijos (av., gral., etc.). Ej: albarracin')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('number_from')
                ->label('Altura desde')
                ->helperText('Dejar vacío si aplica a toda la calle')
                ->numeric()
                ->minValue(1),

            Forms\Components\TextInput::make('number_to')
                ->label('Altura hasta')
                ->helperText('Dejar vacío si aplica a toda la calle')
                ->numeric()
                ->minValue(1),

            Forms\Components\Select::make('zone_key')
                ->label('Zona')
                ->options($zones)
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        $zones = ShippingZone::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['zone_key', 'label'])
            ->mapWithKeys(fn ($zone) => [$zone->zone_key => $zone->label])
            ->toArray();

        if (empty($zones)) {
            $zones = collect(config('shipping.zones', []))
                ->mapWithKeys(fn ($zone, $key) => [$key => $zone['label']])
                ->toArray();
        }

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('street_name')
                    ->label('Calle')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('number_from')
                    ->label('Desde')
                    ->placeholder('Toda la calle'),

                Tables\Columns\TextColumn::make('number_to')
                    ->label('Hasta')
                    ->placeholder('Toda la calle'),

                Tables\Columns\BadgeColumn::make('zone_key')
                    ->label('Zona')
                    ->formatStateUsing(fn ($state) => $zones[$state] ?? $state)
                    ->colors([
                        'success' => 'centro',
                        'warning' => 'belgrano_melipal',
                        'danger'  => 'exterior',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('zone_key')
                    ->label('Zona')
                    ->options($zones),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('street_name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStreetZones::route('/'),
            'create' => Pages\CreateStreetZone::route('/create'),
            'edit'   => Pages\EditStreetZone::route('/{record}/edit'),
        ];
    }
}
