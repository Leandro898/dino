<?php

use App\Livewire\LiveChat;
use App\Models\CustomRequest;
use Livewire\Livewire;
use App\Models\User;

test('a user can open the chat and send a message', function () {
    // 1. Assert that initially customRequest and customRequestId are null, and chat is closed
    $component = Livewire::test(LiveChat::class)
        ->assertSet('isOpen', false)
        ->assertSet('customRequest', null)
        ->assertSet('customRequestId', null);

    // 2. Call openChat and assert it gets created, sets open, and allocates ID
    $component->call('openChat')
        ->assertSet('isOpen', true);

    $request = CustomRequest::first();
    expect($request)->not->toBeNull();
    expect($request->status)->toBe('open');

    $component->assertSet('customRequestId', $request->id);

    // 3. Send a message
    $component->set('message', 'Hola, necesito cotizar un pedido especial.')
        ->call('sendMessage')
        ->assertSet('message', '') // Reset after sending
        ->assertCount('messages', 1);

    // 4. Assert message was saved in database
    expect($request->messages()->count())->toBe(1);
    expect($request->messages()->first()->message)->toBe('Hola, necesito cotizar un pedido especial.');
});

test('a user can accept a quote and add to cart', function () {
    // Create admin user (needed in acceptQuote for owner relation)
    User::factory()->create(['role' => 'admin']);

    $request = CustomRequest::create([
        'session_id' => session()->getId(),
        'status' => 'quoted',
        'quoted_price' => 1500,
        'quote_description' => '1kg Helado',
    ]);

    $component = Livewire::test(LiveChat::class)
        ->assertSet('customRequestId', $request->id)
        ->call('acceptQuote');

    // Assert redirect and database update
    $component->assertRedirect(route('checkout.index'));
    
    $request->refresh();
    expect($request->status)->toBe('accepted');
    expect(session()->get('cart'))->not->toBeEmpty();
});
