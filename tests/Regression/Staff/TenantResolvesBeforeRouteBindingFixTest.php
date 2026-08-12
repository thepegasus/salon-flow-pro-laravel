<?php

namespace Tests\Regression\Staff;

use App\Models\StaffLeaveRequest;
use App\Models\StaffProfile;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class TenantResolvesBeforeRouteBindingFixTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    /**
     * Bug: ResolveTenant was appended to the web middleware group, which
     * placed it after Laravel's built-in SubstituteBindings middleware.
     * Any route with an implicit model binding (e.g. PUT /staff/{staff})
     * resolved that binding before the tenant context was set, so the
     * tenant-scoped query threw NoTenantContextException on every such
     * route instead of finding the record. Fixed by prepending ResolveTenant
     * so it runs before route model binding.
     */
    public function test_updating_a_route_bound_leave_request_resolves_tenant_first(): void
    {
        $manager = User::factory()->for($this->tenant)->create();
        $manager->assignRole('Manager');
        $staffProfile = StaffProfile::factory()->create(['tenant_id' => $this->tenant->id]);
        $leaveRequest = StaffLeaveRequest::factory()->create([
            'tenant_id' => $this->tenant->id,
            'staff_profile_id' => $staffProfile->id,
        ]);

        $response = $this->actingAs($manager)->putToTenant("/staff/leave-requests/{$leaveRequest->id}", [
            'status' => 'approved',
        ]);

        $response->assertRedirect();
    }
}
