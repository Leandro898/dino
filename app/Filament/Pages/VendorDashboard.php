<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;

class VendorDashboard extends BaseDashboard implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.pages.vendor-dashboard';

    protected static ?string $navigationLabel = 'Escritorio';
    protected static ?string $title = 'Escritorio';

    public ?array $data = [];

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        return $user && $user->role === 'vendor';
    }

    public function getHeading(): string
    {
        return 'Configuración del Comercio';
    }

    public function mount()
    {
        $user = auth()->user();
        
        if ($user?->role !== 'vendor') {
            abort(403, 'Acceso denegado. Esta sección es exclusiva para vendedores.');
        }

        $this->form->fill([
            'name' => $user->name,
            'banner' => $user->banner,
            'address' => $user->address,
            'opening_time' => $user->opening_time,
            'closing_time' => $user->closing_time,
            'opening_time_2' => $user->opening_time_2,
            'closing_time_2' => $user->closing_time_2,
            'closed_days' => $user->closed_days ?? [],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Datos Generales')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre del Comercio')
                            ->required(),
                        TextInput::make('address')
                            ->label('Dirección del Comercio (Calle y altura)')
                            ->placeholder('Ej: Mitre 1130')
                            ->maxLength(255),
                        FileUpload::make('banner')
                            ->label('Banner del Comercio')
                            ->image()
                            ->disk('public')
                            ->directory('vendors/banners')
                            ->visibility('public')
                            ->nullable(),
                    ]),

                Section::make('Horarios de Atención (Primer Turno)')
                    ->description('Especifica el horario principal de tu local.')
                    ->schema([
                        TimePicker::make('opening_time')
                            ->label('Hora de Apertura')
                            ->seconds(false)
                            ->nullable(),
                        TimePicker::make('closing_time')
                            ->label('Hora de Cierre')
                            ->seconds(false)
                            ->nullable(),
                    ])->columns(2),

                Section::make('Horarios de Atención (Segundo Turno - Opcional)')
                    ->description('Dejar en blanco si el comercio atiende de corrido en un solo turno.')
                    ->schema([
                        TimePicker::make('opening_time_2')
                            ->label('Hora de Apertura (Segundo Turno)')
                            ->seconds(false)
                            ->nullable(),
                        TimePicker::make('closing_time_2')
                            ->label('Hora de Cierre (Segundo Turno)')
                            ->seconds(false)
                            ->nullable(),
                    ])->columns(2),

                Section::make('Días de Cierre')
                    ->description('Selecciona los días de la semana en los que el comercio NO trabaja y permanece cerrado.')
                    ->schema([
                        CheckboxList::make('closed_days')
                            ->label('Días que permanece Cerrado')
                            ->options([
                                'Monday' => 'Lunes',
                                'Tuesday' => 'Martes',
                                'Wednesday' => 'Miércoles',
                                'Thursday' => 'Jueves',
                                'Friday' => 'Viernes',
                                'Saturday' => 'Sábado',
                                'Sunday' => 'Domingo',
                            ])
                            ->columns(4)
                            ->nullable(),
                    ]),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $user = auth()->user();
        $data = $this->form->getState();

        // 1. Sanitizar el banner (Filament FileUpload retorna un array en páginas personalizadas)
        if (isset($data['banner'])) {
            if (is_array($data['banner'])) {
                $data['banner'] = !empty($data['banner']) ? reset($data['banner']) : null;
            } elseif ($data['banner'] === '') {
                $data['banner'] = null;
            }
        }

        // 2. Sanitizar cadenas vacías a null para campos de base de datos
        foreach (['opening_time', 'closing_time', 'opening_time_2', 'closing_time_2', 'address'] as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        // Autodetectar coordenadas si la dirección cambió
        if (!empty($data['address']) && $data['address'] !== $user->address) {
            $coords = app(\App\Services\ZoneDetectionService::class)->getCoordinates($data['address']);
            if ($coords) {
                $data['latitude'] = $coords['lat'];
                $data['longitude'] = $coords['lng'];
            }
        } elseif (empty($data['address'])) {
            $data['latitude'] = null;
            $data['longitude'] = null;
        }

        $user->update($data);

        // Refrescar el formulario con los nuevos valores
        $this->form->fill([
            'name' => $user->name,
            'banner' => $user->banner,
            'address' => $user->address,
            'opening_time' => $user->opening_time,
            'closing_time' => $user->closing_time,
            'opening_time_2' => $user->opening_time_2,
            'closing_time_2' => $user->closing_time_2,
            'closed_days' => $user->closed_days ?? [],
        ]);

        Notification::make()
            ->title('Guardado')
            ->body('Los datos de tu comercio se han guardado exitosamente.')
            ->success()
            ->send();
    }
}
