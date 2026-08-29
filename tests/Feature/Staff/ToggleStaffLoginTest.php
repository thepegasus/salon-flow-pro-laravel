<?php

namespace Tests\Feature\Staff;

use App\Models\StaffProfile;
use App\Models\User;
use App\Services\TenantContext;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class ToggleStaffLoginTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_owner_can_disable_a_staff_members_login(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        $staffProfile = StaffProfile::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($owner)->putToTenant("/staff/{$staffProfile->id}/login/disable");

        $response->assertRedirect();
        app(TenantContext::class)->set($this->tenant);
        $this->assertNotNull($staffProfile->user->fresh()->disabled_at);
    }

    public function test_owner_can_enable_a_disabled_staff_members_login(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        $staffProfile = StaffProfile::factory()->create(['tenant_id' => $this->tenant->id]);
        app(TenantContext::class)->set($this->tenant);
        $staffProfile->user->update(['disabled_at' => now()]);

        $response = $this->actingAs($owner)->putToTenant("/staff/{$staffProfile->id}/login/enable");

        app(TenantContext::class)->set($this->tenant);
        $response->assertRedirect();
        $this->assertNull($staffProfile->user->fresh()->disabled_at);
    }

    public function test_front_desk_cannot_disable_a_staff_members_login(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');
        $staffProfile = StaffProfile::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($frontDesk)->putToTenant("/staff/{$staffProfile->id}/login/disable");

        $response->assertForbidden();
    }

    public function test_disabling_login_for_staff_without_a_login_fails_gracefully(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        $staffProfile = StaffProfile::factory()->withoutLogin()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($owner)->putToTenant("/staff/{$staffProfile->id}/login/disable");

        $response->assertSessionHasErrors('login');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $staffProfile = StaffProfile::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->putToTenant("/staff/{$staffProfile->id}/login/disable");

        $response->assertRedirect($this->tenantUrl('/login'));
    }
}
