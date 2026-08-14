<?php

namespace Tests\Feature\Commission;

use App\Models\CommissionRate;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class DeleteCommissionRateTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_owner_can_delete_a_commission_rate(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        $rate = CommissionRate::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($owner)->deleteFromTenant("/commission-rates/{$rate->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('commission_rates', ['id' => $rate->id]);
    }

    public function test_owner_can_delete_a_commission_rate_via_slug_url(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        $rate = CommissionRate::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($owner)->delete($this->bySlugUrl("/commission-rates/{$rate->id}"));

        $response->assertRedirect();
        $this->assertSoftDeleted('commission_rates', ['id' => $rate->id]);
    }

    public function test_stylist_cannot_delete_a_commission_rate(): void
    {
        $stylist = User::factory()->for($this->tenant)->create();
        $stylist->assignRole('Stylist');
        $rate = CommissionRate::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($stylist)->deleteFromTenant("/commission-rates/{$rate->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('commission_rates', ['id' => $rate->id, 'deleted_at' => null]);
    }
}
