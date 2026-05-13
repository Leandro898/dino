<?php

namespace App\Filament\Pages;

use App\Models\StoreSetting;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class PriceControl extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Control de precios';

    protected static ?string $navigationGroup = 'Configuracion';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.price-control';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'global_price_adjustment' => StoreSetting::globalPriceAdjustment(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('global_price_adjustment')
                    ->label('Monto adicional global')
                    ->numeric()
                    ->prefix('$')
                    ->required()
                    ->minValue(0)
                    ->step(0.01)
                    ->helperText('Este monto se suma a todos los productos en el sitio, carrito y checkout.'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();
        StoreSetting::updateGlobalPriceAdjustment((float) ($state['global_price_adjustment'] ?? 0));

        Notification::make()
            ->title('Configuracion guardada')
            ->body('Se guardo correctamente el nuevo control global de precios.')
            ->success()
            ->send();

        $this->redirect(url('/admin/price-control'), navigate: true);
    }
}
