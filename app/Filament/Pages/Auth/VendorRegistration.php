<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Register as BaseRegister;

class VendorRegistration extends BaseRegister
{
    protected function mutateFormDataBeforeRegister(array $data): array
    {
        $data['role'] = 'vendor';
        $data['is_approved'] = true;

        return $data;
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNameFormComponent()->label('Nombre del Comercio / Negocio'),
                        \Filament\Forms\Components\TextInput::make('phone')
                            ->label('Teléfono (WhatsApp)')
                            ->tel()
                            ->required()
                            ->maxLength(255),
                        $this->getEmailFormComponent()->label('Correo Electrónico'),
                        $this->getPasswordFormComponent()->label('Contraseña'),
                        $this->getPasswordConfirmationFormComponent()->label('Confirmar Contraseña'),
                    ])
                    ->statePath('data'),
            ),
        ];
    }
}
