<?php

namespace Tests\Feature\Staff;

use App\Models\Designation;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class CreateStaffTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_owner_can_create_staff_member_with_a_login(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        $service = Service::factory()->create(['tenant_id' => $this->tenant->id]);
        $designation = Designation::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Senior Stylist']);

        $response = $this->actingAs($owner)->postToTenant('/staff', [
            'name' => 'Priya Nair',
            'designation_id' => $designation->id,
            'create_login' => true,
            'username' => 'priya',
            'email' => 'priya@example.com',
            'password' => 'password123',
            'roles' => ['Stylist'],
            'service_ids' => [$service->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['username' => 'priya', 'tenant_id' => $this->tenant->id]);
        $this->assertDatabaseHas('staff_profiles', [
            'name' => 'Priya Nair',
            'designation_id' => $designation->id,
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_owner_can_create_staff_member_without_a_login(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->postToTenant('/staff', [
            'name' => 'Ravi Kumar',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('staff_profiles', [
            'name' => 'Ravi Kumar',
            'user_id' => null,
            'tenant_id' => $this->tenant->id,
        ]);
        $this->assertDatabaseMissing('users', ['name' => 'Ravi Kumar']);
    }

    public function test_validation_rejects_duplicate_username_within_tenant(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        User::factory()->for($this->tenant)->create(['username' => 'priya']);

        $response = $this->actingAs($owner)->postToTenant('/staff', [
            'name' => 'Priya Nair',
            'create_login' => true,
            'username' => 'priya',
            'password' => 'password123',
            'roles' => ['Stylist'],
        ]);

        $response->assertSessionHasErrors('username');
    }

    public function test_validation_rejects_unknown_role(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->postToTenant('/staff', [
            'name' => 'Priya Nair',
            'create_login' => true,
            'username' => 'priya',
            'password' => 'password123',
            'roles' => ['NotARole'],
        ]);

        $response->assertSessionHasErrors('roles.0');
    }

    public function test_login_fields_are_required_when_create_login_is_checked(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->postToTenant('/staff', [
            'name' => 'Priya Nair',
            'create_login' => '1',
        ]);

        $response->assertSessionHasErrors(['username', 'password']);
    }
}
