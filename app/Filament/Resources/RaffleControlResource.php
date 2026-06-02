<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource;
use App\Filament\Resources\RaffleControlResource\Pages;
use App\Models\OrderItem;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RaffleControlResource extends Resource
{
    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        return $user && $user->role === 'admin';
    }
    protected static ?string $model = OrderItem::class;

    protected static ?string $modelLabel = 'Venta de sorteo';

    protected static ?string $pluralModelLabel = 'Control de sorteos';

    protected static ?string $navigationLabel = 'Control de sorteos';

    protected static ?string $navigationGroup = 'Sorteos';

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Producto sorteo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('raffle_number')
                    ->label('Numero vendido')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order.id')
                    ->label('Pedido #')
                    ->sortable(),
                Tables\Columns\TextColumn::make('order.name')
                    ->label('Cliente')
                    ->searchable(),
                Tables\Columns\TextColumn::make('order.phone')
                    ->label('Telefono')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('order.status')
                    ->label('Estado pedido')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending_transfer' => 'Pendiente transferencia',
                        'proof_sent' => 'Comprobante enviado',
                        'paid_confirmed' => 'Pago confirmado',
                        default => str_replace('_', ' ', ucfirst($state)),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'pending_transfer' => 'warning',
                        'proof_sent' => 'info',
                        'processing' => 'info',
                        'paid_confirmed' => 'success',
                        'completed' => 'success',
                        'shipped' => 'primary',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha venta')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Producto')
                    ->relationship(
                        'product',
                        'name',
                        fn (Builder $query) => $query
                            ->where('is_raffle', true)
                            ->where('user_id', auth()->id())
                    )
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('order_status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'pending_transfer' => 'Pendiente transferencia',
                        'proof_sent' => 'Comprobante enviado',
                        'processing' => 'Procesando',
                        'paid_confirmed' => 'Pago confirmado',
                        'completed' => 'Completado',
                        'shipped' => 'Enviado',
                        'cancelled' => 'Cancelado',
                    ])
                    ->query(fn (Builder $query, array $data): Builder =>
                        $query->when(
                            filled($data['value'] ?? null),
                            fn (Builder $query): Builder => $query->whereHas(
                                'order',
                                fn (Builder $orderQuery): Builder => $orderQuery->where('status', $data['value'])
                            )
                        )
                    ),
                Filter::make('today')
                    ->label('Solo hoy')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', now()->toDateString())),
            ])
            ->actions([
                Tables\Actions\Action::make('ver_pedido')
                    ->label('Ver pedido')
                    ->icon('heroicon-o-eye')
                    ->url(fn (OrderItem $record): string => OrderResource::getUrl('view', ['record' => $record->order_id]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRaffleControls::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereNotNull('raffle_number')
            ->whereHas('product', function (Builder $query): void {
                $query
                    ->where('is_raffle', true)
                    ->where('user_id', auth()->id());
            });
    }
}
