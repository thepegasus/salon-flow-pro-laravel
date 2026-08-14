<?php

namespace Tests\Regression\Reports;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class DashboardLowStockCardShowsRealCountFixTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
    }

    /**
     * Bug: the dashboard's "Low Stock" card was hardcoded to "Not tracked
     * yet" before the Inventory module existed. Fixed to show the live
     * low-stock product count once Inventory landed.
     */
    public function test_dashboard_shows_low_stock_count_instead_of_placeholder(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'quantity_on_hand' => 1,
            'reorder_level' => 5,
        ]);

        $response = $this->actingAs($owner)->getFromTenant('/dashboard');

        $response->assertOk();
        $response->assertDontSee('Not tracked yet');
        $response->assertSee('1');
    }

    public function test_dashboard_shows_all_stocked_when_nothing_is_low(): void
    {
        $owner = User::factory()->for($this->tenant)->create();

        $response = $this->actingAs($owner)->getFromTenant('/dashboard');

        $response->assertOk();
        $response->assertSee('All stocked');
    }
}
