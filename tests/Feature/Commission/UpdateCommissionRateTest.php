<?php

namespace Tests\Feature\Commission;

use App\Models\CommissionRate;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class UpdateCommissionRateTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_manager_can_update_a_commission_rate(): void
    {
        $manager = User::factory()->for($this->tenant)->create();
        $manager->assignRole('Manager');
        $rate = CommissionRate::factory()->create(['tenant_id' => $this->tenant->id, 'rate_percent' => 10]);

        $response = $this->actingAs($manager)->putToTenant("/commission-rates/{$rate->id}", [
            'rate_percent' => 25,
            'effective_from' => '2026-02-01',
        ]);

        $response->assertRedirect();
        $this->assertSame('25.00', (string) $rate->fresh()->rate_percent);
    }

    public function test_manager_can_update_a_commission_rate_via_slug_url(): void
    {
        $manager = User::factory()->for($this->tenant)->create();
        $manager->assignRole('Manager');
        $rate = CommissionRate::factory()->create(['tenant_id' => $this->tenant->id, 'rate_percent' => 10]);

        $response = $this->actingAs($manager)->put($this->bySlugUrl("/commission-rates/{$rate->id}"), [
            'rate_percent' => 30,
            'effective_from' => '2026-02-01',
        ]);

        $response->assertRedirect();
        $this->assertSame('30.00', (string) $rate->fresh()->rate_percent);
    }

    public function test_stylist_cannot_update_a_commission_rate(): void
    {
        $stylist = User::factory()->for($this->tenant)->create();
        $stylist->assignRole('Stylist');
        $rate = CommissionRate::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($stylist)->putToTenant("/commission-rates/{$rate->id}", [
            'rate_percent' => 50,
        ]);

        $response->assertForbidden();
    }

    public function test_validation_rejects_negative_rate(): void
    {
        $manager = User::factory()->for($this->tenant)->create();
        $manager->assignRole('Manager');
        $rate = CommissionRate::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($manager)->putToTenant("/commission-rates/{$rate->id}", [
            'rate_percent' => -5,
        ]);

        $response->assertSessionHasErrors('rate_percent');
    }
}
