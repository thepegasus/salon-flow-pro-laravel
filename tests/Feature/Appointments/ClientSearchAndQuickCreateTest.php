<?php

namespace Tests\Feature\Appointments;

use App\Models\Client;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class ClientSearchAndQuickCreateTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_searching_by_phone_returns_matching_clients(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');
        Client::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Anjali Menon', 'phone' => '9876543210']);
        Client::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Someone Else', 'phone' => '9000000000']);

        $response = $this->actingAs($frontDesk)->getFromTenant('/appointments/clients/search?q=98765');

        $response->assertOk();
        $response->assertJsonCount(1, 'clients');
        $response->assertJsonFragment(['name' => 'Anjali Menon']);
    }

    public function test_searching_by_name_returns_matching_clients(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');
        Client::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Anjali Menon', 'phone' => '9876543210']);

        $response = $this->actingAs($frontDesk)->getFromTenant('/appointments/clients/search?q=Anjali');

        $response->assertOk();
        $response->assertJsonFragment(['phone' => '9876543210']);
    }

    public function test_search_with_a_short_query_returns_no_results(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');

        $response = $this->actingAs($frontDesk)->getFromTenant('/appointments/clients/search?q=9');

        $response->assertOk();
        $response->assertJsonCount(0, 'clients');
    }

    public function test_quick_create_client_persists_a_new_client(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');

        $response = $this->actingAs($frontDesk)->postJson($this->tenantUrl('/appointments/clients/quick-create'), [
            'name' => 'New Client',
            'phone' => '9123456780',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('clients', [
            'tenant_id' => $this->tenant->id,
            'name' => 'New Client',
            'phone' => '9123456780',
        ]);
    }

    public function test_quick_create_client_rejects_duplicate_phone_within_tenant(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');
        Client::factory()->create(['tenant_id' => $this->tenant->id, 'phone' => '9123456780']);

        $response = $this->actingAs($frontDesk)->postJson($this->tenantUrl('/appointments/clients/quick-create'), [
            'name' => 'Duplicate Phone',
            'phone' => '9123456780',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('phone');
    }

    public function test_stylist_cannot_search_or_create_clients(): void
    {
        $stylist = User::factory()->for($this->tenant)->create();
        $stylist->assignRole('Stylist');

        $searchResponse = $this->actingAs($stylist)->getFromTenant('/appointments/clients/search?q=test');
        $createResponse = $this->actingAs($stylist)->postJson($this->tenantUrl('/appointments/clients/quick-create'), [
            'name' => 'X', 'phone' => '9000000001',
        ]);

        $searchResponse->assertForbidden();
        $createResponse->assertForbidden();
    }
}
