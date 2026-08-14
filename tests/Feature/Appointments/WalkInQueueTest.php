<?php

namespace Tests\Feature\Appointments;

use App\Models\Client;
use App\Models\StaffProfile;
use App\Models\User;
use App\Models\WalkIn;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class WalkInQueueTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_front_desk_can_add_a_walk_in_to_the_queue(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');

        $response = $this->actingAs($frontDesk)->postToTenant('/walk-ins', [
            'name' => 'Imran',
            'phone' => '9999999999',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('walk_ins', ['name' => 'Imran', 'status' => 'waiting']);
    }

    public function test_front_desk_can_assign_a_walk_in_to_a_stylist(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');
        $staffProfile = StaffProfile::factory()->create(['tenant_id' => $this->tenant->id]);
        $client = Client::factory()->create(['tenant_id' => $this->tenant->id]);
        $walkIn = WalkIn::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($frontDesk)->putToTenant("/walk-ins/{$walkIn->id}/assign", [
            'staff_profile_id' => $staffProfile->id,
            'client_id' => $client->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('walk_ins', ['id' => $walkIn->id, 'status' => 'assigned']);
    }
}
