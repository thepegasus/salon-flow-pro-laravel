<?php

namespace Tests\Feature\Inventory;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class UpdateProductTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_owner_can_update_a_product(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        $product = Product::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Old name']);

        $response = $this->actingAs($owner)->putToTenant("/products/{$product->id}", [
            'name' => 'New name',
        ]);

        $response->assertRedirect();
        $this->assertSame('New name', $product->fresh()->name);
    }

    public function test_validation_rejects_sku_already_used_by_another_product(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        Product::factory()->create(['tenant_id' => $this->tenant->id, 'sku' => 'TAKEN']);
        $product = Product::factory()->create(['tenant_id' => $this->tenant->id, 'sku' => 'FREE']);

        $response = $this->actingAs($owner)->putToTenant("/products/{$product->id}", [
            'sku' => 'TAKEN',
        ]);

        $response->assertSessionHasErrors('sku');
    }

    public function test_front_desk_cannot_update_a_product(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');
        $product = Product::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($frontDesk)->putToTenant("/products/{$product->id}", [
            'name' => 'Should not save',
        ]);

        $response->assertForbidden();
    }
}
