<?php

namespace Tests\Feature\TimeSlots;

use App\Models\TimeSlot;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class TimeSlotAccessTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_owner_can_view_time_slot_index(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->getFromTenant('/appointments/time-slots');

        $response->assertOk();
    }

    public function test_stylist_cannot_create_a_time_slot(): void
    {
        $stylist = User::factory()->for($this->tenant)->create();
        $stylist->assignRole('Stylist');

        $response = $this->actingAs($stylist)->getFromTenant('/appointments/time-slots/create');

        $response->assertForbidden();
    }

    public function test_stylist_cannot_delete_a_time_slot(): void
    {
        $stylist = User::factory()->for($this->tenant)->create();
        $stylist->assignRole('Stylist');
        $slot = TimeSlot::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($stylist)->deleteFromTenant("/appointments/time-slots/{$slot->id}");

        $response->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->getFromTenant('/appointments/time-slots');

        $response->assertRedirect($this->tenantUrl('/login'));
    }

    public function test_owner_can_create_a_time_slot(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->postToTenant('/appointments/time-slots', [
            'start_time' => '09:00',
            'end_time' => '09:30',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('time_slots', [
            'tenant_id' => $this->tenant->id,
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
        ]);
    }

    public function test_end_time_must_be_after_start_time(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->postToTenant('/appointments/time-slots', [
            'start_time' => '10:00',
            'end_time' => '09:30',
        ]);

        $response->assertSessionHasErrors('end_time');
    }

    public function test_owner_can_update_a_time_slot(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        $slot = TimeSlot::factory()->create(['tenant_id' => $this->tenant->id, 'start_time' => '09:00:00', 'end_time' => '09:30:00']);

        $response = $this->actingAs($owner)->putToTenant("/appointments/time-slots/{$slot->id}", [
            'start_time' => '09:15',
            'end_time' => '09:45',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('time_slots', [
            'id' => $slot->id,
            'start_time' => '09:15:00',
            'end_time' => '09:45:00',
        ]);
    }

    public function test_owner_can_delete_a_time_slot(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        $slot = TimeSlot::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($owner)->deleteFromTenant("/appointments/time-slots/{$slot->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('time_slots', ['id' => $slot->id]);
    }
}
