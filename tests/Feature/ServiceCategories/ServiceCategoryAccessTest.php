<?php

namespace Tests\Feature\ServiceCategories;

use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class ServiceCategoryAccessTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_owner_can_view_category_index(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->getFromTenant('/services/categories');

        $response->assertOk();
    }

    public function test_stylist_cannot_create_a_category(): void
    {
        $stylist = User::factory()->for($this->tenant)->create();
        $stylist->assignRole('Stylist');

        $response = $this->actingAs($stylist)->getFromTenant('/services/categories/create');

        $response->assertForbidden();
    }

    public function test_front_desk_cannot_delete_a_category(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');
        $category = ServiceCategory::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($frontDesk)->deleteFromTenant("/services/categories/{$category->id}");

        $response->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->getFromTenant('/services/categories');

        $response->assertRedirect($this->tenantUrl('/login'));
    }

    public function test_owner_can_create_a_category(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->postToTenant('/services/categories', [
            'name' => 'Hair',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('service_categories', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Hair',
        ]);
    }

    public function test_creating_a_duplicate_category_name_fails_validation(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        ServiceCategory::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Hair']);

        $response = $this->actingAs($owner)->postToTenant('/services/categories', [
            'name' => 'Hair',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_owner_can_update_a_category(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        $category = ServiceCategory::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Hair']);

        $response = $this->actingAs($owner)->putToTenant("/services/categories/{$category->id}", [
            'name' => 'Hair Care',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('service_categories', [
            'id' => $category->id,
            'name' => 'Hair Care',
        ]);
    }

    public function test_owner_can_delete_a_category(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        $category = ServiceCategory::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($owner)->deleteFromTenant("/services/categories/{$category->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('service_categories', ['id' => $category->id]);
    }
}
