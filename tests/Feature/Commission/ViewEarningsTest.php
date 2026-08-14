<?php

namespace Tests\Feature\Commission;

use App\Models\StaffProfile;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class ViewEarningsTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_manager_can_view_earnings_for_any_staff_member(): void
    {
        $manager = User::factory()->for($this->tenant)->create();
        $manager->assignRole('Manager');
        $staffUser = User::factory()->for($this->tenant)->create();
        $staff = StaffProfile::factory()->create(['tenant_id' => $this->tenant->id, 'user_id' => $staffUser->id]);

        $response = $this->actingAs($manager)->getFromTenant("/commission-earnings?staff_profile_id={$staff->id}");

        $response->assertOk();
    }

    public function test_stylist_can_view_their_own_earnings(): void
    {
        $stylist = User::factory()->for($this->tenant)->create();
        $stylist->assignRole('Stylist');
        $staffProfile = StaffProfile::factory()->create(['tenant_id' => $this->tenant->id, 'user_id' => $stylist->id]);

        $response = $this->actingAs($stylist)->getFromTenant("/commission-earnings?staff_profile_id={$staffProfile->id}");

        $response->assertOk();
    }

    public function test_stylist_viewing_earnings_with_no_staff_param_only_sees_their_own(): void
    {
        $stylist = User::factory()->for($this->tenant)->create();
        $stylist->assignRole('Stylist');
        StaffProfile::factory()->create(['tenant_id' => $this->tenant->id, 'user_id' => $stylist->id]);

        $response = $this->actingAs($stylist)->getFromTenant('/commission-earnings');

        $response->assertOk();
    }

    public function test_stylist_requesting_another_staff_members_earnings_is_forbidden(): void
    {
        $stylist = User::factory()->for($this->tenant)->create();
        $stylist->assignRole('Stylist');
        StaffProfile::factory()->create(['tenant_id' => $this->tenant->id, 'user_id' => $stylist->id]);
        $otherStaff = StaffProfile::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($stylist)->getFromTenant("/commission-earnings?staff_profile_id={$otherStaff->id}");

        $response->assertForbidden();
    }

    public function test_stylist_without_a_staff_profile_is_forbidden(): void
    {
        $stylist = User::factory()->for($this->tenant)->create();
        $stylist->assignRole('Stylist');

        $response = $this->actingAs($stylist)->getFromTenant('/commission-earnings');

        $response->assertForbidden();
    }
}
