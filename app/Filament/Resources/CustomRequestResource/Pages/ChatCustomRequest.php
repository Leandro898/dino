<?php

namespace App\Filament\Resources\CustomRequestResource\Pages;

use App\Filament\Resources\CustomRequestResource;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class ChatCustomRequest extends Page
{
    use InteractsWithRecord;

    protected static string $resource = CustomRequestResource::class;

    protected static string $view = 'filament.resources.custom-request-resource.pages.chat-custom-request';

    public $message = '';
    public $quotePrice = null;
    public $quoteDescription = '';

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        
        // Marcar como leído por el admin
        if ($this->record->has_unread_admin) {
            $this->record->update(['has_unread_admin' => false]);
        }
    }

    public function getListeners()
    {
        return [
            "echo:custom-request.{$this->record->id},.message.sent" => 'refreshMessages',
        ];
    }

    public function refreshMessages()
    {
        $this->dispatch('play-notification-sound');

        if ($this->record->has_unread_admin) {
            $this->record->update(['has_unread_admin' => false]);
        }
    }

    public function sendMessage()
    {
        if (empty(trim($this->message))) {
            return;
        }

        $msg = $this->record->messages()->create([
            'sender_type' => 'admin',
            'message' => $this->message,
        ]);

        $this->record->update(['has_unread_user' => true]);

        $this->message = '';

        try {
            $this->record->notify(new \App\Notifications\NewCustomRequestReplyNotification($msg));
        } catch (\Exception $e) {
            Log::error('Error sending push notification reply to client', ['error' => $e->getMessage()]);
        }

        try {
            broadcast(new \App\Events\CustomRequestMessageSent($this->record->id))->toOthers();
        } catch (\Exception $e) {}
    }

    public function sendQuote()
    {
        $this->validate([
            'quotePrice' => 'required|numeric|min:0',
            'quoteDescription' => 'required|string',
        ]);

        $this->record->update([
            'status' => 'quoted',
            'quoted_price' => $this->quotePrice,
            'quote_description' => $this->quoteDescription,
            'has_unread_user' => true,
        ]);

        $msg = $this->record->messages()->create([
            'sender_type' => 'admin',
            'is_system_message' => true,
            'message' => "Cotización enviada: {$this->quoteDescription} por $" . number_format($this->quotePrice, 2, ',', '.'),
        ]);

        $this->quotePrice = null;
        $this->quoteDescription = '';

        try {
            $this->record->notify(new \App\Notifications\NewCustomRequestReplyNotification($msg));
        } catch (\Exception $e) {
            Log::error('Error sending push notification quote to client', ['error' => $e->getMessage()]);
        }

        try {
            broadcast(new \App\Events\CustomRequestMessageSent($this->record->id))->toOthers();
        } catch (\Exception $e) {}
    }
}
