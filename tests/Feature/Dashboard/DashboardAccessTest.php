<?php

namespace Tests\Feature\Dashboard;

use App\Models\Bill;
use App\Models\Client;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->getFromTenant('/dashboard');

        $response->assertOk();
        $response->assertViewIs('admin.dashboard');
    }

    public function test_dashboard_displays_todays_revenue_and_pending_payments(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        $client = Client::factory()->create(['tenant_id' => $this->tenant->id]);
        Bill::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $client->id,
            'created_by' => $owner->id,
            'total' => 42850,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($owner)->getFromTenant('/dashboard');

        $response->assertOk();
        $response->assertSee('42,850.00');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->getFromTenant('/dashboard');

        $response->assertRedirect($this->tenantUrl('/login'));
    }
}
