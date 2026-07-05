<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_sees_minimal_dashboard()
    {
        // Create a vendor user
        $user = User::factory()->create([
            'role' => 'vendor',
            'email' => 'vendor@example.test',
        ]);

        $this->actingAs($user);

        $response = $this->get('/admin');

        $response->assertStatus(200);

        // Vendor should NOT see admin widgets or analytics
        $response->assertDontSee('Analytics');
        $response->assertDontSee('Pedidos recientes');

        // Vendor should see the minimal vendor message
        $response->assertSee('No hay información administrativa disponible');
    }
}
