<?php

namespace Tests\Regression\Staff;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class LoginFieldsRequiredWhenCreateLoginCheckedFixTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    /**
     * Bug: StoreStaffRequest listed 'nullable' alongside 'required_if' on the
     * username/password rules. Laravel's 'nullable' short-circuits remaining
     * rules when a field is absent, so 'required_if:create_login,1' never
     * fired — submitting create_login=1 with no username/password passed
     * validation and then crashed StaffService::create() on undefined array
     * keys. Fixed by dropping 'nullable' from those two fields.
     */
    public function test_submitting_create_login_without_credentials_fails_validation(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->postToTenant('/staff', [
            'name' => 'Priya Nair',
            'create_login' => '1',
        ]);

        $response->assertSessionHasErrors(['username', 'password']);
        $this->assertDatabaseMissing('staff_profiles', ['name' => 'Priya Nair']);
    }

    public function test_submitting_without_create_login_does_not_require_credentials(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->postToTenant('/staff', [
            'name' => 'Ravi Kumar',
        ]);

        $response->assertSessionDoesntHaveErrors(['username', 'password']);
        $this->assertDatabaseHas('staff_profiles', ['name' => 'Ravi Kumar', 'user_id' => null]);
    }
}
