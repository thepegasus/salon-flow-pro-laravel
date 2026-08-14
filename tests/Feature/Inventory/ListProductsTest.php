<?php

namespace Tests\Feature\Inventory;

use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class ListProductsTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_owner_can_list_products(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        Product::factory()->count(3)->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($owner)->getFromTenant('/products');

        $response->assertOk();
    }

    public function test_owner_can_list_products_via_the_slug_path(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        Product::factory()->count(2)->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($owner)->get($this->bySlugUrl('/products'));

        $response->assertOk();
    }

    public function test_products_from_other_tenants_are_not_listed(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        Product::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Mine']);

        $otherTenant = Tenant::factory()->create();
        Product::factory()->create(['tenant_id' => $otherTenant->id, 'name' => 'Not mine']);

        $response = $this->actingAs($owner)->getFromTenant('/products');

        $response->assertOk();
        $response->assertSee('Mine');
        $response->assertDontSee('Not mine');
    }
}
