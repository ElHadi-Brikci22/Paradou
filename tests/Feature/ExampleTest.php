<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that guest is redirected to login.
     */
    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    /**
     * Test that authenticated users can access checkout.
     */
    public function test_authenticated_users_can_access_checkout(): void
    {
        // Seed guest client since CheckoutController expects it
        Client::create([
            'code' => 'GUEST',
            'name' => 'Client Passage',
            'discount_percent' => 0,
            'credit' => 0.00
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
    }
}
