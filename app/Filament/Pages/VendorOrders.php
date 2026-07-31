<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class VendorOrders extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Pedidos';

    protected static string $view = 'filament.pages.vendor-orders';

    protected static ?string $title = 'Pedidos';

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        return $user && $user->role === 'vendor';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make('createOrder')
                ->model(\App\Models\Order::class)
                ->label('Nuevo Pedido')
                ->icon('heroicon-o-plus')
                ->modalHeading('Cargar Nueva Comanda')
                ->modalWidth('2xl')
                ->mutateFormDataUsing(function (array $data): array {
                    $data['status'] = 'assigned';
                    $data['shipping_zone'] = 'Local'; // Marca como delivery
                    $data['email'] = $data['email'] ?? 'sinemail@ejemplo.com';
                    $user = auth()->user();
                    if ($user && $user->role === 'vendor') {
                        $data['vendor_id'] = $user->id;
                    }
                    return $data;
                })
                ->form([
                    \Filament\Forms\Components\TextInput::make('name')
                        ->label('Nombre del Cliente')
                        ->required()
                        ->maxLength(255),
                    \Filament\Forms\Components\TextInput::make('phone')
                        ->label('Teléfono (Obligatorio)')
                        ->tel()
                        ->required()
                        ->maxLength(255),
                    \Filament\Forms\Components\TextInput::make('address')
                        ->label('Dirección')
                        ->required()
                        ->maxLength(255),
                    \Filament\Forms\Components\TextInput::make('total')
                        ->label('Total del Pedido (ARS)')
                        ->required()
                        ->numeric()
                        ->prefix('$'),
                    \Filament\Forms\Components\Textarea::make('order_details')
                        ->label('Detalle del Pedido')
                        ->placeholder('Ej: 2 docenas de empanadas...')
                        ->columnSpanFull(),
                    \Filament\Forms\Components\Textarea::make('beverage_details')
                        ->label('Bebidas (Opcional)')
                        ->placeholder('Ej: 1 Coca-Cola 2L')
                        ->columnSpanFull(),
                    \Filament\Forms\Components\Select::make('payment_method')
                        ->label('Método de Pago')
                        ->options([
                            'Efectivo' => 'Efectivo',
                            'Transferencia' => 'Transferencia',
                        ])
                        ->required()
                        ->default('Efectivo'),
                ])
                ->successNotificationTitle('Comanda enviada correctamente')
                ->after(function (\Livewire\Component $livewire) {
                    $livewire->dispatch('order-updated');
                }),
        ];
    }
}
