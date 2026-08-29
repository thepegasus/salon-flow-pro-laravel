<?php

namespace Tests\Feature\Services;

use App\Models\Service;
use App\Models\StaffProfile;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class EligibleStaffForServiceTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_front_desk_can_fetch_eligible_staff_for_a_service(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');
        $service = Service::factory()->create(['tenant_id' => $this->tenant->id]);
        $eligibleStaffProfile = StaffProfile::factory()->create(['tenant_id' => $this->tenant->id]);
        $ineligibleStaffProfile = StaffProfile::factory()->create(['tenant_id' => $this->tenant->id]);
        $service->staff()->sync([$eligibleStaffProfile->id]);

        $response = $this->actingAs($frontDesk)->getFromTenant("/services/{$service->id}/eligible-staff");

        $response->assertOk();
        $response->assertJsonCount(1, 'staff');
        $response->assertJsonFragment(['id' => $eligibleStaffProfile->id, 'name' => $eligibleStaffProfile->name]);
        $response->assertJsonMissing(['id' => $ineligibleStaffProfile->id]);
    }

    public function test_stylist_without_billing_permission_cannot_fetch_eligible_staff(): void
    {
        $stylist = User::factory()->for($this->tenant)->create();
        $stylist->assignRole('Stylist');
        $service = Service::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($stylist)->getFromTenant("/services/{$service->id}/eligible-staff");

        $response->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $service = Service::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->getFromTenant("/services/{$service->id}/eligible-staff");

        $response->assertRedirect($this->tenantUrl('/login'));
    }
}
