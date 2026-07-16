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
    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        return $user && $user->role === 'admin';
    }
    protected static ?string $model = StreetZone::class;

    protected static ?string $navigationIcon  = 'heroicon-o-map-pin';
    protected static ?string $navigationLabel = 'Calles por zona';
    protected static ?string $navigationGroup = 'Envíos';
    protected static ?int    $navigationSort  = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('street_name')
                ->label('Nombre de la calle')
                ->helperText('Usa minúsculas, sin tildes. Ej: mitre, bustillo')
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

            Forms\Components\TextInput::make('price')
                ->label('Precio de envío ($)')
                ->helperText('Monto en pesos. Ej: 5000')
                ->numeric()
                ->required()
                ->minValue(0),
        ]);
    }

    public static function table(Table $table): Table
    {
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

                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->formatStateUsing(fn ($state) => '$' . number_format($state, 0, ',', '.'))
                    ->sortable(),
            ])
            ->filters([
                //
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
