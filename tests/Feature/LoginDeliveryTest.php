<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

test('standard login route returns standard login view by default', function () {
    $response = $this->get(route('login'));

    $response->assertStatus(200);
    $response->assertViewIs('auth.login');
    $response->assertDontSee('Acceso Repartidores');
});

test('login route returns delivery login view when query param role is delivery', function () {
    $response = $this->get(route('login', ['role' => 'delivery']));

    $response->assertStatus(200);
    $response->assertViewIs('auth.login-delivery');
    $response->assertSee('Acceso Repartidores');
    $response->assertSee('Bari Rider');
});

test('login route returns delivery login view when query param role is repartidor', function () {
    $response = $this->get(route('login', ['role' => 'repartidor']));

    $response->assertStatus(200);
    $response->assertViewIs('auth.login-delivery');
    $response->assertSee('Acceso Repartidores');
});

test('login route returns delivery login view when requested from repartidor subdomain', function () {
    $response = $this->get('https://repartidor.baritienda.online/login');

    $response->assertStatus(200);
    $response->assertViewIs('auth.login-delivery');
    $response->assertSee('Acceso Repartidores');
});

test('delivery login view contains functional links to register and password recovery', function () {
    $response = $this->get(route('login', ['role' => 'delivery']));

    // Verificar que los enlaces correctos están en el HTML
    $response->assertSee(route('register.delivery', absolute: false));
    $response->assertSee(route('password.request', absolute: false));

    // Verificar que la página de registro del repartidor funciona
    $responseRegister = $this->get(route('register.delivery'));
    $responseRegister->assertStatus(200);
    $responseRegister->assertSee('Únete como Repartidor');

    // Verificar que la página de recuperación de contraseña funciona y devuelve la vista correcta en contexto de repartidor
    $responseForgotPassword = $this->get(route('password.request', ['role' => 'delivery']));
    $responseForgotPassword->assertStatus(200);
    $responseForgotPassword->assertViewIs('auth.forgot-password-delivery');
    $responseForgotPassword->assertSee('Bari Rider');
    $responseForgotPassword->assertSee('¿Olvidaste tu contraseña?');
});

test('verify email route returns delivery verify view for unverified delivery riders', function () {
    $deliveryUser = User::factory()->create([
        'role' => 'delivery',
        'email_verified_at' => null
    ]);

    $response = $this->actingAs($deliveryUser)
        ->get(route('verification.notice'));

    $response->assertStatus(200);
    $response->assertViewIs('auth.verify-email-delivery');
    $response->assertSee('Bari Rider');
    $response->assertSee('Verifica tu Correo');
});

test('email verification notification uses custom verification class', function () {
    \Illuminate\Support\Facades\Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $user->sendEmailVerificationNotification();

    \Illuminate\Support\Facades\Notification::assertSentTo(
        $user,
        \App\Notifications\VerifyEmailNotification::class
    );
});

test('delivery app route returns pending approval view for unapproved delivery riders', function () {
    $deliveryUser = User::factory()->create([
        'role' => 'delivery',
        'is_approved' => false,
        'email_verified_at' => now()
    ]);

    $response = $this->actingAs($deliveryUser)
        ->get(route('delivery.app'));

    $response->assertStatus(200);
    $response->assertViewIs('delivery.pending-approval');
    $response->assertSee('Bari Rider');
    $response->assertSee('Cuenta en Revisión');
});
