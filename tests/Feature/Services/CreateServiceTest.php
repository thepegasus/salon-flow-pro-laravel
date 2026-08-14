<?php

namespace Tests\Feature\Services;

use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class CreateServiceTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_owner_can_create_a_service(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        $category = ServiceCategory::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($owner)->postToTenant('/services', [
            'name' => 'Haircut',
            'category_id' => $category->id,
            'price' => 499,
            'duration_minutes' => 45,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('services', [
            'name' => 'Haircut',
            'price' => 499,
            'category_id' => $category->id,
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_creating_a_service_records_initial_price_history(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $this->actingAs($owner)->postToTenant('/services', [
            'name' => 'Manicure',
            'price' => 299,
            'duration_minutes' => 30,
        ]);

        $this->assertDatabaseHas('service_price_histories', [
            'price' => 299,
            'changed_by' => $owner->id,
        ]);
    }

    public function test_validation_rejects_negative_price(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->postToTenant('/services', [
            'name' => 'Haircut',
            'price' => -10,
            'duration_minutes' => 30,
        ]);

        $response->assertSessionHasErrors('price');
    }

    public function test_validation_rejects_missing_name(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->postToTenant('/services', [
            'price' => 499,
            'duration_minutes' => 30,
        ]);

        $response->assertSessionHasErrors('name');
    }
}
