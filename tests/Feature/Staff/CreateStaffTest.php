<?php

namespace Tests\Feature\Staff;

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

    public function test_owner_can_create_staff_member(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        $service = Service::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($owner)->postToTenant('/staff', [
            'name' => 'Priya Nair',
            'username' => 'priya',
            'email' => 'priya@example.com',
            'password' => 'password123',
            'role' => 'Stylist',
            'job_title' => 'Senior Stylist',
            'service_ids' => [$service->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['username' => 'priya', 'tenant_id' => $this->tenant->id]);
        $this->assertDatabaseHas('staff_profiles', ['job_title' => 'Senior Stylist', 'tenant_id' => $this->tenant->id]);
    }

    public function test_validation_rejects_duplicate_username_within_tenant(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        User::factory()->for($this->tenant)->create(['username' => 'priya']);

        $response = $this->actingAs($owner)->postToTenant('/staff', [
            'name' => 'Priya Nair',
            'username' => 'priya',
            'password' => 'password123',
            'role' => 'Stylist',
            'job_title' => 'Senior Stylist',
        ]);

        $response->assertSessionHasErrors('username');
    }

    public function test_validation_rejects_unknown_role(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->postToTenant('/staff', [
            'name' => 'Priya Nair',
            'username' => 'priya',
            'password' => 'password123',
            'role' => 'NotARole',
            'job_title' => 'Senior Stylist',
        ]);

        $response->assertSessionHasErrors('role');
    }
}
