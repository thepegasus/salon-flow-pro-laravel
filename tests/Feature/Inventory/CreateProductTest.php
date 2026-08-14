<?php

namespace Tests\Feature\Inventory;

use App\Models\InventoryCategory;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class CreateProductTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_owner_can_create_a_product(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        $category = InventoryCategory::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($owner)->postToTenant('/products', [
            'name' => 'Keratin Shampoo',
            'category_id' => $category->id,
            'sku' => 'SKU-100',
            'quantity_on_hand' => 20,
            'reorder_level' => 5,
            'unit' => 'ml',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'name' => 'Keratin Shampoo',
            'sku' => 'SKU-100',
            'category_id' => $category->id,
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_owner_can_create_a_product_via_the_slug_path(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->post($this->bySlugUrl('/products'), [
            'name' => 'Nail Polish',
            'quantity_on_hand' => 10,
            'reorder_level' => 2,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'name' => 'Nail Polish',
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_validation_rejects_missing_name(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->postToTenant('/products', [
            'quantity_on_hand' => 10,
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_validation_rejects_duplicate_sku_for_the_same_tenant(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        Product::factory()->create(['tenant_id' => $this->tenant->id, 'sku' => 'DUPE-1']);

        $response = $this->actingAs($owner)->postToTenant('/products', [
            'name' => 'Another product',
            'sku' => 'DUPE-1',
        ]);

        $response->assertSessionHasErrors('sku');
    }
}
