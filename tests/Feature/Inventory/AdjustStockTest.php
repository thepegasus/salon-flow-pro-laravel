<?php

namespace Tests\Feature\Inventory;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class AdjustStockTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_owner_can_adjust_stock_upward(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        $product = Product::factory()->create(['tenant_id' => $this->tenant->id, 'quantity_on_hand' => 10]);

        $response = $this->actingAs($owner)->postToTenant("/products/{$product->id}/stock-adjustments", [
            'quantity_delta' => 5,
            'reason' => 'Restock',
        ]);

        $response->assertRedirect();
        $this->assertSame('15.00', $product->fresh()->quantity_on_hand);
        $this->assertDatabaseHas('stock_adjustments', [
            'product_id' => $product->id,
            'quantity_delta' => 5,
            'reason' => 'Restock',
            'adjusted_by' => $owner->id,
        ]);
    }

    public function test_owner_can_adjust_stock_downward(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        $product = Product::factory()->create(['tenant_id' => $this->tenant->id, 'quantity_on_hand' => 10]);

        $response = $this->actingAs($owner)->postToTenant("/products/{$product->id}/stock-adjustments", [
            'quantity_delta' => -3,
            'reason' => 'Damaged',
        ]);

        $response->assertRedirect();
        $this->assertSame('7.00', $product->fresh()->quantity_on_hand);
    }

    public function test_validation_requires_a_reason(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        $product = Product::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($owner)->postToTenant("/products/{$product->id}/stock-adjustments", [
            'quantity_delta' => 5,
        ]);

        $response->assertSessionHasErrors('reason');
    }

    public function test_front_desk_cannot_adjust_stock(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');
        $product = Product::factory()->create(['tenant_id' => $this->tenant->id, 'quantity_on_hand' => 10]);

        $response = $this->actingAs($frontDesk)->postToTenant("/products/{$product->id}/stock-adjustments", [
            'quantity_delta' => 5,
            'reason' => 'Restock',
        ]);

        $response->assertForbidden();
        $this->assertSame('10.00', $product->fresh()->quantity_on_hand);
    }
}
