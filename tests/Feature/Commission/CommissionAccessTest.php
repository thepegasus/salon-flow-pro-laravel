<?php

namespace Tests\Feature\Commission;

use App\Models\CommissionRate;
use App\Models\StaffProfile;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class CommissionAccessTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_owner_can_access_commission_rates_and_earnings(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $this->actingAs($owner)->getFromTenant('/commission-rates')->assertOk();
        $this->actingAs($owner)->getFromTenant('/commission-earnings')->assertOk();
    }

    public function test_manager_can_access_commission_rates_and_earnings(): void
    {
        $manager = User::factory()->for($this->tenant)->create();
        $manager->assignRole('Manager');

        $this->actingAs($manager)->getFromTenant('/commission-rates')->assertOk();
        $this->actingAs($manager)->getFromTenant('/commission-earnings')->assertOk();
    }

    public function test_stylist_can_view_earnings_but_is_forbidden_from_rate_management(): void
    {
        $stylist = User::factory()->for($this->tenant)->create();
        $stylist->assignRole('Stylist');
        StaffProfile::factory()->create(['tenant_id' => $this->tenant->id, 'user_id' => $stylist->id]);

        $this->actingAs($stylist)->getFromTenant('/commission-earnings')->assertOk();
        $this->actingAs($stylist)->getFromTenant('/commission-rates')->assertForbidden();
        $this->actingAs($stylist)->getFromTenant('/commission-rates/create')->assertForbidden();
    }

    public function test_stylist_cannot_delete_commission_rates(): void
    {
        $stylist = User::factory()->for($this->tenant)->create();
        $stylist->assignRole('Stylist');
        $rate = CommissionRate::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($stylist)->deleteFromTenant("/commission-rates/{$rate->id}");

        $response->assertForbidden();
    }

    public function test_front_desk_is_forbidden_from_all_commission_routes(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');

        $this->actingAs($frontDesk)->getFromTenant('/commission-rates')->assertForbidden();
        $this->actingAs($frontDesk)->getFromTenant('/commission-earnings')->assertForbidden();
        $this->actingAs($frontDesk)->getFromTenant('/staff-incentives/create')->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->getFromTenant('/commission-rates');

        $response->assertRedirect('/login');
    }
}
