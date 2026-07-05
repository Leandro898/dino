<?php

namespace App\Filament\Resources\OrderResource\Actions;

use App\Models\Order;
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
            ->button()
            ->action(function (Order $record) {
                // Placeholder - la acción real está en los sub-actions
            });
    }

    public static function getSubActions(Order $record): array
    {
        $user = auth()->user();
        
        if ($user && $user->role === 'admin') {
            $statusOptions = [
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
        } else {
            $statusOptions = [
                'assigned' => '✅ Asignado',
                'processing' => '⚙️ En prep.',
                'shipped' => '🚚 Listo/Enviado',
            ];
        }

        $actions = [];
        foreach ($statusOptions as $statusKey => $statusLabel) {
            if ($statusKey === $record->status) {
                continue; // No mostrar el estado actual
            }

            $actions[] = Action::make("status_$statusKey")
                ->label($statusLabel)
                ->icon('heroicon-m-check-circle')
                ->action(function () use ($record, $statusKey) {
                    $record->update(['status' => $statusKey]);
                })
                ->requiresConfirmation(fn () => $statusKey === 'cancelled');
        }

        return $actions;
    }
}
