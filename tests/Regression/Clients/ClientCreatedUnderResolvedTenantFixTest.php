<?php

namespace Tests\Regression\Clients;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class ClientCreatedUnderResolvedTenantFixTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    /**
     * Bug risk: ClientsController::store() originally read tenant_id from
     * $request->user()->tenant_id directly instead of the resolved
     * TenantContext. Every other write path in the app derives tenant_id
     * from TenantContext, which is set by ResolveTenant based on the
     * request's host — not from the acting user's own tenant_id column.
     * If those two ever diverge, a client would be silently attributed to
     * the wrong tenant. Fixed to use TenantContext consistently.
     */
    public function test_created_client_is_attributed_to_the_resolved_tenant_context(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');

        $response = $this->actingAs($frontDesk)->postToTenant('/clients', [
            'name' => 'Priya Nair',
            'phone' => '9000000001',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('clients', [
            'name' => 'Priya Nair',
            'tenant_id' => $this->tenant->id,
        ]);
    }
}
