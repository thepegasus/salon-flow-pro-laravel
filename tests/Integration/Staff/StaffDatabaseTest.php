<?php

namespace Tests\Integration\Staff;

use App\Models\Service;
use App\Models\StaffProfile;
use App\Models\Tenant;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_profile_persists_with_correct_relationships(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);

        $staffProfile = StaffProfile::factory()->create(['tenant_id' => $tenant->id]);
        $service = Service::factory()->create(['tenant_id' => $tenant->id]);
        $staffProfile->services()->attach($service);

        $this->assertDatabaseHas('staff_profiles', ['id' => $staffProfile->id]);
        $this->assertTrue($staffProfile->tenant->is($tenant));
        $this->assertTrue($staffProfile->services->contains($service));
    }

    public function test_tenant_scope_excludes_staff_from_other_tenants(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        StaffProfile::factory()->create(['tenant_id' => $tenantA->id]);
        StaffProfile::factory()->create(['tenant_id' => $tenantB->id]);

        app(TenantContext::class)->set($tenantA);

        $this->assertSame(1, StaffProfile::count());
    }

    public function test_deleting_staff_profile_soft_deletes_and_preserves_history(): void
    {
        $tenant = Tenant::factory()->create();
        $staffProfile = StaffProfile::factory()->create(['tenant_id' => $tenant->id]);

        $staffProfile->delete();

        $this->assertSoftDeleted('staff_profiles', ['id' => $staffProfile->id]);
    }
}
