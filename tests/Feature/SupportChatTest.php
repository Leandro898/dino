<?php

use App\Models\User;
use App\Models\SupportMessage;
use App\Events\SupportMessageSent;
use Illuminate\Support\Facades\Event;
use App\Filament\Resources\DeliverySupportResource\Pages\ChatDeliverySupport;
use Livewire\Livewire;

test('delivery user can get and send support messages', function () {
    Event::fake();

    $deliveryUser = User::factory()->create(['role' => 'delivery', 'email_verified_at' => now()]);

    // Send a message via controller endpoint
    $response = $this->actingAs($deliveryUser)
        ->postJson(route('delivery.support.send'), [
            'message' => 'Hola, necesito ayuda con una entrega.'
        ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('support_messages', [
        'delivery_user_id' => $deliveryUser->id,
        'sender_id' => $deliveryUser->id,
        'message' => 'Hola, necesito ayuda con una entrega.',
    ]);

    Event::assertDispatched(SupportMessageSent::class);

    // Get messages via controller endpoint
    $responseGet = $this->actingAs($deliveryUser)
        ->getJson(route('delivery.support.messages'));

    $responseGet->assertStatus(200)
        ->assertJsonCount(1);
});

test('admin can reply to delivery support messages', function () {
    Event::fake();

    $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
    $deliveryUser = User::factory()->create(['role' => 'delivery', 'email_verified_at' => now()]);

    // Delivery user sends message first
    $message = SupportMessage::create([
        'delivery_user_id' => $deliveryUser->id,
        'sender_id' => $deliveryUser->id,
        'message' => 'Hola, admin.',
    ]);

    // Admin replies via ChatDeliverySupport Livewire page
    $component = Livewire::actingAs($admin)
        ->test(ChatDeliverySupport::class, ['record' => $deliveryUser->id])
        ->set('message', 'Hola repartidor, en qué te ayudo?')
        ->call('sendMessage')
        ->assertSet('message', '');

    $this->assertDatabaseHas('support_messages', [
        'delivery_user_id' => $deliveryUser->id,
        'sender_id' => $admin->id,
        'message' => 'Hola repartidor, en qué te ayudo?',
    ]);

    Event::assertDispatched(SupportMessageSent::class);
});
