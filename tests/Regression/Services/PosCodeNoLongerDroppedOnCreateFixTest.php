<?php

namespace Tests\Regression\Services;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class PosCodeNoLongerDroppedOnCreateFixTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    /**
     * Bug: ServiceCatalogService::create()/update() built an explicit
     * whitelisted array for the repository and never included 'code', so a
     * validated POS code from the create/edit form was silently discarded
     * before it ever reached the database — the field appeared to save
     * (no error) but was always null.
     */
    public function test_pos_code_submitted_on_the_create_form_is_persisted(): void
    {
        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->postToTenant('/services', [
            'name' => 'Haircut',
            'code' => '101',
            'price' => 499,
            'duration_minutes' => 45,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('services', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Haircut',
            'code' => '101',
        ]);
    }
}
