<?php

namespace App\Livewire;

use App\Models\CustomRequest;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Session;

class LiveChat extends Component
{
    public $isOpen = false;
    public $message = '';
    public $customRequest = null;
    public $customRequestId = null;
    public $messages = [];
    public $vendor_id = null; // ID del vendedor al que se dirige el chat
    public $vendor_name = null; // Nombre del vendedor

    public function mount($vendor_id = null)
    {
        $this->vendor_id = $vendor_id;
        
        if ($this->vendor_id) {
            $vendor = \App\Models\User::find($this->vendor_id);
            if ($vendor) {
                $this->vendor_name = $vendor->name;
            }
        }

        $sessionId = Session::getId();
        
        $query = CustomRequest::where('session_id', $sessionId)
                              ->whereIn('status', ['open', 'quoted']);
                              
        if ($this->vendor_id) {
            $query->where('vendor_id', $this->vendor_id);
        } else {
            $query->whereNull('vendor_id');
        }

        $this->customRequest = $query->first();

        if ($this->customRequest) {
            $this->customRequestId = $this->customRequest->id;
            $this->loadMessages();
            $this->dispatch('subscribe-to-chat', requestId: $this->customRequest->id);
        }
    }

    #[On('open-live-chat')]
    public function openChat()
    {
        $this->isOpen = true;
        if (!$this->customRequest) {
            $this->customRequest = CustomRequest::create([
                'session_id' => Session::getId(),
                'vendor_id' => $this->vendor_id,
                'status' => 'open',
            ]);
        }
        
        $this->customRequestId = $this->customRequest->id;
        $this->dispatch('subscribe-to-chat', requestId: $this->customRequest->id);

        if ($this->customRequest->has_unread_user) {
            $this->customRequest->update(['has_unread_user' => false]);
        }
        
        $this->loadMessages();
    }

    public function closeChat()
    {
        $this->isOpen = false;
    }

    public function loadMessages()
    {
        if ($this->customRequest) {
            $this->customRequest = CustomRequest::find($this->customRequest->id);
            $this->customRequestId = $this->customRequest->id;
            $this->messages = $this->customRequest->messages()->oldest()->get()->toArray();
        }
    }

    public function sendMessage($text = null)
    {
        $text = $text ?? $this->message;
        
        if (empty(trim($text))) {
            return;
        }

        if (!$this->customRequest) {
            $this->openChat();
        }

        $messageModel = $this->customRequest->messages()->create([
            'sender_type' => 'user',
            'message' => $text,
        ]);

        $this->customRequest->update(['has_unread_admin' => true]);

        $this->reset('message');
        $this->loadMessages();
        $this->dispatch('message-sent');

        try {
            if ($this->vendor_id) {
                // Notificar solo al vendedor de este local
                $vendor = \App\Models\User::find($this->vendor_id);
                if ($vendor) {
                    $vendor->notify(new \App\Notifications\NewCustomRequestMessagePushNotification($messageModel, $this->customRequest));
                }
            } else {
                // Notificar a todos los admins (chat global)
                $admins = \App\Models\User::where('role', 'admin')->get();
                foreach ($admins as $admin) {
                    $admin->notify(new \App\Notifications\NewCustomRequestMessagePushNotification($messageModel, $this->customRequest));
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error sending push notification for custom request msg', ['error' => $e->getMessage()]);
        }

        try {
            broadcast(new \App\Events\CustomRequestMessageSent($this->customRequest->id))->toOthers();
        } catch (\Exception $e) {
            // Ignorar fallos de websockets
        }
    }

    public function acceptQuote()
    {
        if (!$this->customRequest || $this->customRequest->status !== 'quoted') {
            return;
        }

        // Crear producto real para no romper la lógica del checkout y las tablas de órdenes
        $product = \App\Models\Product::create([
            'user_id' => $this->vendor_id ?? \App\Models\User::first()->id, // Vendedor del chat o principal
            'name' => "Pedido: " . $this->customRequest->quote_description,
            'description' => "Pedido Especial #" . $this->customRequest->id,
            'price' => $this->customRequest->quoted_price,
            'stock' => 99,
            'is_active' => false,
        ]);

        // Añadir al carrito
        $cart = session()->get('cart', []);
        
        $cart[$product->id] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'quantity' => 1,
            'price' => $product->price,
            'image' => null,
            'is_custom' => true,
        ];
        
        session()->put('cart', $cart);

        $this->customRequest->update([
            'status' => 'accepted'
        ]);

        $this->customRequest->messages()->create([
            'sender_type' => 'admin',
            'is_system_message' => true,
            'message' => '¡Cotización aceptada! Producto añadido al carrito.',
        ]);

        $this->isOpen = false;
        
        $this->dispatch('cart-updated');
        
        return redirect()->route('checkout.index');
    }


    public function handleNewMessage()
    {
        if (!$this->customRequest) return;
        
        $this->dispatch('play-notification-sound');
        
        $this->loadMessages();
        
        if ($this->isOpen && $this->customRequest->has_unread_user) {
            $this->customRequest->update(['has_unread_user' => false]);
        }
    }

    public function render()
    {
        return view('livewire.live-chat');
    }
}
