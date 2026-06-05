<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        // Solo vendors y admin ven ventas
        return $user && in_array($user->role, ['admin', 'vendor']);
    }
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Pedidos';

    protected static ?string $modelLabel = 'Pedido';

    protected static ?string $pluralModelLabel = 'Pedidos';

    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')->label('ID')->disabled(),
                Forms\Components\Select::make('status')
                    ->options(function () {
                        $user = auth()->user();
                        if ($user && $user->role === 'admin') {
                            return [
                                'pending' => 'Pendiente',
                                'assigned' => 'Asignado',
                                'pending_transfer' => 'Pendiente de transferencia',
                                'proof_sent' => 'Comprobante enviado',
                                'processing' => 'En preparación',
                                'paid_confirmed' => 'Pago confirmado',
                                'completed' => 'Completado',
                                'shipped' => 'Enviado',
                                'cancelled' => 'Cancelado',
                            ];
                        }
                        
                        // Opciones para el Vendedor
                        return [
                            'assigned' => 'Asignado',
                            'processing' => 'En preparación',
                            'shipped' => 'Listo para retirar/enviado',
                        ];
                    })
                    ->disabled(fn ($record) => auth()->user()->role !== 'admin' && $record?->status === 'pending')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('total')
                    ->label('💰 Total')
                    ->sortable()
                    ->money('ARS')
                    ->weight('bold')
                    ->size('lg'),
                
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Pago')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'mercadopago' => '💳 Mercado Pago',
                        'efectivo' => '💵 Efectivo',
                        'transferencia' => '🏦 Transferencia',
                        default => $state ?? '—',
                    })
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'mercadopago' => 'success',
                        'efectivo' => 'gray',
                        'transferencia' => 'warning',
                        default => 'gray',
                    })
                    ->visibleFrom('sm'),
                
                Tables\Columns\TextColumn::make('shipping_zone')
                    ->label('📍 Zona')
                    ->visible(fn () => auth()->user()?->role === "admin")
                    ->visibleFrom('md'),
                
                Tables\Columns\TextColumn::make('shipping_cost')
                    ->label('📦 Envío')
                    ->money('ARS')
                    ->sortable()
                    ->visible(fn () => auth()->user()?->role === "admin")
                    ->visibleFrom('lg'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/y H:i')
                    ->label('📅 Fecha')
                    ->sortable()
                    ->visibleFrom('md'),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->hidden(),
                
                Tables\Columns\SelectColumn::make('status')
                    ->label('📌 Estado')
                    ->options(function () {
                        $user = auth()->user();
                        if ($user && $user->role === 'admin') {
                            return [
                                'pending' => '⏳ Pendiente',
                                'assigned' => '✅ Asignado',
                                'pending_transfer' => '⏳ Pte. Transf.',
                                'proof_sent' => '📄 Comprobante',
                                'processing' => '⚙️ En prep.',
                                'paid_confirmed' => '💚 Pagado',
                                'completed' => '✔️ Completado',
                                'shipped' => '🚚 Enviado',
                                'cancelled' => '❌ Cancelado',
                            ];
                        }
                        
                        return [
                            'assigned' => '✅ Asignado',
                            'processing' => '⚙️ En prep.',
                            'shipped' => '🚚 Listo/Enviado',
                        ];
                    })
                    ->selectablePlaceholder(false)
                    ->disabled(fn ($record) => auth()->user()->role !== 'admin' && $record?->status === 'pending')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pendiente',
                        'assigned' => 'Asignado',
                        'pending_transfer' => 'Pendiente de transferencia',
                        'proof_sent' => 'Comprobante enviado',
                        'processing' => 'En preparación',
                        'paid_confirmed' => 'Pago confirmado',
                        'completed' => 'Completado',
                        'shipped' => 'Enviado',
                        'cancelled' => 'Cancelado',
                    ]),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'mercadopago' => 'Mercado Pago',
                        'efectivo' => 'Efectivo',
                        'transferencia' => 'Transferencia',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            //'create' => Pages\CreateOrder::route('/create'), // Disabled
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        // Si es admin, ve todos los pedidos
        if ($user && $user->role === 'admin') {
            return parent::getEloquentQuery();
        }
        // Si es vendedor, ve solo los pedidos que ya están ASIGNADOS y le pertenecen
        return parent::getEloquentQuery()
            ->where('status', '!=', 'pending')
            ->whereHas('items.product', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
    }
}
