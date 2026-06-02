<?php

namespace App\Filament\Client\Resources;

use App\Filament\Client\Resources\ClientOrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClientOrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $navigationLabel = 'Mis pedidos';

    protected static ?string $modelLabel = 'Pedido';

    protected static ?string $pluralModelLabel = 'Pedidos';

    protected static ?string $navigationGroup = 'Mi negocio';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Resumen')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'assigned' => 'Asignado',
                                'processing' => 'En preparación',
                                'completed' => 'Completado',
                                'cancelled' => 'Cancelado',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('name')
                            ->label('Cliente')
                            ->disabled(),
                        Forms\Components\TextInput::make('phone')
                            ->label('Telefono')
                            ->disabled(),
                        Forms\Components\TextInput::make('payment_method')
                            ->label('Pago')
                            ->disabled(),
                        Forms\Components\TextInput::make('shipping_zone')
                            ->label('Zona')
                            ->disabled(),
                        Forms\Components\TextInput::make('shipping_cost')
                            ->label('Envio')
                            ->numeric()
                            ->prefix('$')
                            ->disabled(),
                        Forms\Components\TextInput::make('total')
                            ->label('Total')
                            ->numeric()
                            ->prefix('$')
                            ->disabled(),
                        Forms\Components\Textarea::make('address')
                            ->label('Direccion')
                            ->columnSpanFull()
                            ->disabled(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Items')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->disabled(),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Cantidad')
                                    ->disabled(),
                                Forms\Components\TextInput::make('price')
                                    ->label('Precio')
                                    ->prefix('$')
                                    ->disabled(),
                            ])
                            ->columns(3)
                            ->disabled(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->poll('2s')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Cliente')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'assigned' => 'info',
                        'processing' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Pago')
                    ->badge(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('ARS')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'assigned' => 'Asignado',
                        'processing' => 'En preparación',
                        'completed' => 'Completado',
                        'cancelled' => 'Cancelado',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', 'assigned')
            ->whereHas('items.product', function ($q) {
                $q->where('user_id', auth()->id());
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClientOrders::route('/'),
            'view' => Pages\ViewClientOrder::route('/{record}'),
            'edit' => Pages\EditClientOrder::route('/{record}/edit'),
        ];
    }
}
