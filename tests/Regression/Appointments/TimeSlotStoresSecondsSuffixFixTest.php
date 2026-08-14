<?php

namespace Tests\Regression\Appointments;

use App\Models\TimeSlot;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class TimeSlotStoresSecondsSuffixFixTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    /**
     * Bug risk: the <input type="time"> form field submits "09:00" (H:i),
     * but the time_slots.start_time/end_time columns are `time` columns
     * that Postgres stores and compares as H:i:s. Storing the raw H:i
     * value directly would break ordering/equality once seconds precision
     * mattered, so the controller appends ":00" before persisting.
     */
    public function test_time_slot_is_persisted_with_seconds_precision(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');

        $this->actingAs($owner)->postToTenant('/appointments/time-slots', [
            'start_time' => '09:00',
            'end_time' => '09:30',
        ]);

        $slot = TimeSlot::where('tenant_id', $this->tenant->id)->firstOrFail();

        $this->assertSame('09:00:00', $slot->start_time);
        $this->assertSame('09:30:00', $slot->end_time);
    }

    public function test_editing_a_time_slot_form_shows_the_time_without_seconds(): void
    {
        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
        $slot = TimeSlot::factory()->create([
            'tenant_id' => $this->tenant->id,
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
        ]);

        $response = $this->actingAs($owner)->getFromTenant("/appointments/time-slots/{$slot->id}/edit");

        $response->assertOk();
        $response->assertSee('value="09:00"', false);
        $response->assertSee('value="09:30"', false);
    }
}
