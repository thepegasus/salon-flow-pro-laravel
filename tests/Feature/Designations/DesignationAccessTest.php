<?php

namespace Tests\Feature\Designations;

use App\Models\Designation;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class DesignationAccessTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_owner_can_view_designation_index(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->getFromTenant('/staff/designations');

        $response->assertOk();
    }

    public function test_stylist_cannot_create_a_designation(): void
    {
        $stylist = User::factory()->for($this->tenant)->create();
        $stylist->assignRole('Stylist');

        $response = $this->actingAs($stylist)->getFromTenant('/staff/designations/create');

        $response->assertForbidden();
    }

    public function test_front_desk_cannot_delete_a_designation(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');
        $designation = Designation::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($frontDesk)->deleteFromTenant("/staff/designations/{$designation->id}");

        $response->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->getFromTenant('/staff/designations');

        $response->assertRedirect($this->tenantUrl('/login'));
    }

    public function test_owner_can_create_a_designation(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->postToTenant('/staff/designations', [
            'name' => 'Senior Stylist',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('designations', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Senior Stylist',
        ]);
    }

    public function test_creating_a_duplicate_designation_name_fails_validation(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        Designation::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Senior Stylist']);

        $response = $this->actingAs($owner)->postToTenant('/staff/designations', [
            'name' => 'Senior Stylist',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_owner_can_update_a_designation(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        $designation = Designation::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Stylist']);

        $response = $this->actingAs($owner)->putToTenant("/staff/designations/{$designation->id}", [
            'name' => 'Senior Stylist',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('designations', [
            'id' => $designation->id,
            'name' => 'Senior Stylist',
        ]);
    }

    public function test_owner_can_delete_a_designation(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        $designation = Designation::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($owner)->deleteFromTenant("/staff/designations/{$designation->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('designations', ['id' => $designation->id]);
    }
}
