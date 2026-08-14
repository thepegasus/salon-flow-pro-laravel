<?php

namespace Tests\Feature\Clients;

use App\Models\Client;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class CreateClientTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_front_desk_can_create_a_client(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');

        $response = $this->actingAs($frontDesk)->postToTenant('/clients', [
            'name' => 'Priya Nair',
            'phone' => '9000000001',
            'family_link' => 'Bridal party with Anjali',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('clients', [
            'name' => 'Priya Nair',
            'phone' => '9000000001',
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_validation_rejects_duplicate_phone_within_tenant(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');
        Client::factory()->create(['tenant_id' => $this->tenant->id, 'phone' => '9000000001']);

        $response = $this->actingAs($frontDesk)->postToTenant('/clients', [
            'name' => 'Duplicate Client',
            'phone' => '9000000001',
        ]);

        $response->assertSessionHasErrors('phone');
    }

    public function test_front_desk_can_update_preference_notes(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');
        $client = Client::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($frontDesk)->putToTenant("/clients/{$client->id}", [
            'notes' => 'Allergic to ammonia-based dyes',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('clients', ['id' => $client->id, 'notes' => 'Allergic to ammonia-based dyes']);
    }
}
