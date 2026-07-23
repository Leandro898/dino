<?php

namespace App\Filament\Resources\DeliverySupportResource\Pages;

use App\Filament\Resources\DeliverySupportResource;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Illuminate\Support\Facades\Log;

class ChatDeliverySupport extends Page
{
    use InteractsWithRecord;

    protected static string $resource = DeliverySupportResource::class;

    protected static string $view = 'filament.resources.delivery-support-resource.pages.chat-delivery-support';

    public $message = '';

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        
        // Marcar como leídos por el admin
        \App\Models\SupportMessage::where('delivery_user_id', $this->record->id)
            ->where('sender_id', '!=', auth()->id())
            ->where('is_read_by_admin', false)
            ->update(['is_read_by_admin' => true]);
    }

    public function getListeners()
    {
        return [
            "echo:support.{$this->record->id},.support-message.sent" => 'refreshMessages',
        ];
    }

    public function refreshMessages()
    {
        try {
            $this->dispatch('play-notification-sound');
        } catch (\Exception $e) {}

        \App\Models\SupportMessage::where('delivery_user_id', $this->record->id)
            ->where('sender_id', '!=', auth()->id())
            ->where('is_read_by_admin', false)
            ->update(['is_read_by_admin' => true]);
    }

    public function sendMessage()
    {
        if (empty(trim($this->message))) {
            return;
        }

        $msg = \App\Models\SupportMessage::create([
            'delivery_user_id' => $this->record->id,
            'sender_id' => auth()->id(),
            'message' => $this->message,
            'is_read_by_admin' => true,
            'is_read_by_delivery' => false,
        ]);

        $msg->load('sender');

        $this->message = '';

        try {
            broadcast(new \App\Events\SupportMessageSent($msg))->toOthers();
        } catch (\Exception $e) {
            Log::error('Error broadcasting support message sent by admin:', ['error' => $e->getMessage()]);
        }
    }
}
