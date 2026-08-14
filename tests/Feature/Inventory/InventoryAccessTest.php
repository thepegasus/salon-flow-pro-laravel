<?php

namespace Tests\Feature\Inventory;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class InventoryAccessTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_owner_can_access_product_index(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->getFromTenant('/products');

        $response->assertOk();
    }

    public function test_manager_can_access_product_index(): void
    {
        $manager = User::factory()->for($this->tenant)->create();
        $manager->assignRole('Manager');

        $response = $this->actingAs($manager)->getFromTenant('/products');

        $response->assertOk();
    }

    public function test_front_desk_can_view_but_not_create_products(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');

        $viewResponse = $this->actingAs($frontDesk)->getFromTenant('/products');
        $createResponse = $this->actingAs($frontDesk)->getFromTenant('/products/create');

        $viewResponse->assertOk();
        $createResponse->assertForbidden();
    }

    public function test_stylist_cannot_access_product_index(): void
    {
        $stylist = User::factory()->for($this->tenant)->create();
        $stylist->assignRole('Stylist');

        $response = $this->actingAs($stylist)->getFromTenant('/products');

        $response->assertForbidden();
    }

    public function test_front_desk_cannot_delete_a_product(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');
        $product = Product::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($frontDesk)->deleteFromTenant("/products/{$product->id}");

        $response->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->getFromTenant('/products');

        $response->assertRedirect($this->tenantUrl('/login'));
    }
}
