<?php

namespace Tests\Feature\Commission;

use App\Models\ServiceCategory;
use App\Models\StaffProfile;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class CreateCommissionRateTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_owner_can_create_a_commission_rate_via_subdomain_url(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        $staff = StaffProfile::factory()->create(['tenant_id' => $this->tenant->id]);
        $category = ServiceCategory::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($owner)->postToTenant('/commission-rates', [
            'staff_profile_id' => $staff->id,
            'service_category_id' => $category->id,
            'rate_percent' => 15,
            'effective_from' => '2026-01-01',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('commission_rates', [
            'tenant_id' => $this->tenant->id,
            'staff_profile_id' => $staff->id,
            'service_category_id' => $category->id,
            'rate_percent' => 15,
        ]);
    }

    public function test_owner_can_create_a_commission_rate_via_slug_url(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->post($this->bySlugUrl('/commission-rates'), [
            'rate_percent' => 10,
            'effective_from' => '2026-01-01',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('commission_rates', [
            'tenant_id' => $this->tenant->id,
            'staff_profile_id' => null,
            'service_category_id' => null,
            'rate_percent' => 10,
        ]);
    }

    public function test_default_rate_can_be_created_with_null_staff_and_category(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->postToTenant('/commission-rates', [
            'rate_percent' => 8,
            'effective_from' => '2026-01-01',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('commission_rates', [
            'tenant_id' => $this->tenant->id,
            'staff_profile_id' => null,
            'service_category_id' => null,
        ]);
    }

    public function test_validation_rejects_rate_percent_over_100(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->postToTenant('/commission-rates', [
            'rate_percent' => 150,
            'effective_from' => '2026-01-01',
        ]);

        $response->assertSessionHasErrors('rate_percent');
    }

    public function test_validation_rejects_missing_effective_from(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->postToTenant('/commission-rates', [
            'rate_percent' => 10,
        ]);

        $response->assertSessionHasErrors('effective_from');
    }
}
