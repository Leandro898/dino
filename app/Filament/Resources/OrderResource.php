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
        return auth()->user()?->role === 'admin';
    }
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Pedidos';

    protected static ?string $modelLabel = 'Pedido';

    protected static ?string $pluralModelLabel = 'Pedidos';

    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return auth()->user()?->role === 'admin' || auth()->user()?->role === 'vendor';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')->label('ID')->disabled()->hiddenOn('create'),
                Forms\Components\Select::make('vendor_id')
                    ->label('Comercio (Vendedor)')
                    ->options(function () {
                        return \App\Models\User::query()
                            ->where('role', 'vendor')
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->required()
                    ->visible(fn () => auth()->user()?->role === 'admin')
                    ->hiddenOn('edit'),
                Forms\Components\TextInput::make('name')
                    ->label('Nombre del Cliente')
                    ->required()
                    ->maxLength(255)
                    ->hiddenOn('edit'),
                Forms\Components\TextInput::make('phone')
                    ->label('Teléfono (Obligatorio para el repartidor)')
                    ->required()
                    ->maxLength(20)
                    ->hiddenOn('edit'),
                Forms\Components\TextInput::make('address')
                    ->label('Dirección de entrega')
                    ->required()
                    ->maxLength(255)
                    ->hiddenOn('edit'),
                Forms\Components\Textarea::make('order_details')
                    ->label('Pedido (Detalle exacto de lo que pidió)')
                    ->nullable()
                    ->hiddenOn('edit'),
                Forms\Components\TextInput::make('beverage_details')
                    ->label('Bebida (Opcional)')
                    ->nullable()
                    ->hiddenOn('edit'),
                Forms\Components\TextInput::make('total')
                    ->label('Total a cobrar')
                    ->numeric()
                    ->required()
                    ->hiddenOn('edit'),
                Forms\Components\Select::make('payment_method')
                    ->label('Método de Pago')
                    ->options([
                        'efectivo' => 'Efectivo',
                        'mercadopago' => 'Mercado Pago',
                        'transferencia' => 'Transferencia',
                    ])
                    ->required()
                    ->hiddenOn('edit'),
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
                    ->required()
                    ->hiddenOn('create'),
                Forms\Components\Select::make('delivery_user_id')
                    ->label('Repartidor Asignado')
                    ->options(function () {
                        return \App\Models\User::query()
                            ->where('role', 'delivery')
                            ->where('is_approved', true)
                            ->get()
                            ->mapWithKeys(function ($rider) {
                                $status = $rider->isOnline() ? '🟢 Conectado | ' : '🔴 Desconectado | ';
                                return [$rider->id => $status . $rider->name];
                            });
                    })
                    ->searchable()
                    ->placeholder('Selecciona un repartidor')
                    ->visible(fn () => auth()->user()?->role === 'admin')
                    ->disabled(fn ($record) => $record && in_array($record->status, ['completed', 'cancelled']))
                    ->hiddenOn('create'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('5s')
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
                
                Tables\Columns\TextColumn::make('deliveryUser.name')
                    ->label('🏍️ Repartidor')
                    ->formatStateUsing(function (?string $state, $record) {
                        if (!$record || !$record->deliveryUser) return '—';
                        $icon = $record->deliveryUser->isOnline() ? '🟢 ' : '🔴 ';
                        return $icon . $record->deliveryUser->name;
                    })
                    ->visible(fn () => auth()->user()?->role === "admin")
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('shipping_cost')
                    ->label('📦 Envío (Cobrado)')
                    ->money('ARS')
                    ->sortable()
                    ->visible(fn () => auth()->user()?->role === "admin")
                    ->visibleFrom('lg'),

                Tables\Columns\TextColumn::make('rider_earnings')
                    ->label('💸 Pago a Rider')
                    ->getStateUsing(function (Order $record) {
                        return max(0, $record->shipping_cost - 1000);
                    })
                    ->money('ARS')
                    ->color('success')
                    ->visible(fn () => auth()->user()?->role === "admin"),

                Tables\Columns\TextColumn::make('admin_earnings')
                    ->label('💼 Comisión Servicio')
                    ->getStateUsing(function (Order $record) {
                        return $record->shipping_cost > 0 ? 1000 : 0;
                    })
                    ->money('ARS')
                    ->color('primary')
                    ->visible(fn () => auth()->user()?->role === "admin"),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/y H:i')
                    ->label('📅 Fecha')
                    ->sortable()
                    ->visibleFrom('md'),

                Tables\Columns\IconColumn::make('is_accepted_by_rider')
                    ->label('Aceptado Repartidor')
                    ->boolean()
                    ->sortable()
                    ->visibleFrom('sm'),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->hidden(),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('📌 Estado')
                    ->formatStateUsing(function (?string $state): string {
                        return match ($state) {
                            'pending' => '⏳ Pendiente',
                            'assigned' => '✅ Asignado',
                            'pending_transfer' => '⏳ Pte. Transf.',
                            'proof_sent' => '📄 Comprobante',
                            'processing' => '⚙️ En prep.',
                            'paid_confirmed' => '💚 Pagado',
                            'completed' => '✔️ Completado',
                            'shipped' => '🚚 Enviado',
                            'cancelled' => '❌ Cancelado',
                            default => $state ?? '—',
                        };
                    })
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'pending' => 'warning',
                        'assigned' => 'success',
                        'processing' => 'info',
                        'completed' => 'success',
                        'shipped' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
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
                Tables\Actions\Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-m-chat-bubble-oval-left-ellipsis')
                    ->color('success')
                    ->url(function (Order $record) {
                        $phone = preg_replace('/[^0-9]/', '', $record->phone ?? '');
                        if (strlen($phone) == 10) {
                            $phone = '549' . $phone;
                        }
                        $text = urlencode('Hola soy de baritienda y le escribo para confirmar su pedido #' . $record->id);
                        return "https://wa.me/{$phone}?text={$text}";
                    })
                    ->openUrlInNewTab()
                    ->visible(fn (Order $record) => auth()->user()?->role === 'admin' && !empty($record->phone)),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    // Sub-acciones para cambiar estado (sin modal)
                    Tables\Actions\Action::make('status_assigned')
                        ->label('✅ Asignado')
                        ->icon('heroicon-m-check-circle')
                        ->action(fn (Order $record) => $record->update(['status' => 'assigned']))
                        ->visible(fn (Order $record) => auth()->user()?->role === 'admin' && $record->status !== 'assigned'),
                    
                    Tables\Actions\Action::make('status_processing')
                        ->label('⚙️ En prep.')
                        ->icon('heroicon-m-cog')
                        ->action(fn (Order $record) => $record->update(['status' => 'processing']))
                        ->visible(fn (Order $record) => $record->status !== 'processing'),
                    
                    Tables\Actions\Action::make('status_shipped')
                        ->label('🚚 Enviado')
                        ->icon('heroicon-m-truck')
                        ->action(fn (Order $record) => $record->update(['status' => 'shipped']))
                        ->visible(fn (Order $record) => $record->status !== 'shipped'),
                    
                    Tables\Actions\Action::make('status_completed')
                        ->label('✔️ Completado')
                        ->icon('heroicon-m-check')
                        ->action(fn (Order $record) => $record->update(['status' => 'completed']))
                        ->visible(fn (Order $record) => auth()->user()?->role === 'admin' && $record->status !== 'completed'),
                    
                    Tables\Actions\Action::make('status_pending')
                        ->label('⏳ Pendiente')
                        ->icon('heroicon-m-clock')
                        ->action(fn (Order $record) => $record->update(['status' => 'pending']))
                        ->visible(fn (Order $record) => auth()->user()?->role === 'admin' && $record->status !== 'pending'),
                    
                    Tables\Actions\Action::make('status_cancelled')
                        ->label('❌ Cancelado')
                        ->icon('heroicon-m-x-circle')
                        ->requiresConfirmation()
                        ->action(fn (Order $record) => $record->update(['status' => 'cancelled']))
                        ->visible(fn (Order $record) => auth()->user()?->role === 'admin' && $record->status !== 'cancelled'),
                ])
                    ->label('Estado')
                    ->icon('heroicon-m-pencil-square')
                    ->button()
                    ->visible(fn () => auth()->user()?->role === 'admin' || auth()->user()?->role === 'vendor'),
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
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        // Si es admin, ve todos los pedidos
        if ($user && $user->role === 'admin') {
            return parent::getEloquentQuery()
                ->with(['items.product.user', 'user']);
        }
        // Si es vendedor, ve solo los pedidos que ya están ASIGNADOS y le pertenecen
        return parent::getEloquentQuery()
            ->with(['items.product.user', 'user'])
            ->where('status', '!=', 'pending')
            ->where(function ($q) use ($user) {
                $q->where('vendor_id', $user->id)
                  ->orWhereHas('items.product', function ($subQ) use ($user) {
                      $subQ->where('user_id', $user->id);
                  });
            });
    }
}
