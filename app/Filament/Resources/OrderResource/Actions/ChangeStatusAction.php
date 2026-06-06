<?php

namespace App\Filament\Resources\OrderResource\Actions;

use App\Models\Order;
use Filament\Forms;
use Filament\Tables\Actions\Action;

class ChangeStatusAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'change_status';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Cambiar Estado')
            ->icon('heroicon-m-pencil-square')
            ->form([
                Forms\Components\Select::make('status')
                    ->label('Nuevo Estado')
                    ->options(function ($record) {
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
                    ->default(fn ($record) => $record->status)
                    ->required(),
            ])
            ->action(function (Order $record, array $data) {
                error_log("🎯 ChangeStatusAction: Updating order {$record->id} to status {$data['status']}");
                
                // This WILL load the model and trigger observers
                $record->update(['status' => $data['status']]);
                
                error_log("✅ ChangeStatusAction: Order {$record->id} updated");
            })
            ->success()
            ->slideOver();
    }
}
