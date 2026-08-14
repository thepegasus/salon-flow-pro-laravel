<?php

namespace Tests\Feature\Appointments;

use App\Models\Appointment;
use App\Models\StaffProfile;
use App\Models\StaffShift;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class RescheduleCancelAppointmentTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_front_desk_can_reschedule_an_appointment(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');

        $originalStart = now()->next(1)->setTime(10, 0);
        $newStart = now()->next(1)->setTime(14, 0);
        $staffProfile = StaffProfile::factory()->create(['tenant_id' => $this->tenant->id]);
        StaffShift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'staff_profile_id' => $staffProfile->id,
            'day_of_week' => $originalStart->dayOfWeek,
            'start_time' => '09:00',
            'end_time' => '18:00',
            'is_working' => true,
        ]);
        $appointment = Appointment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'staff_profile_id' => $staffProfile->id,
            'start_at' => $originalStart,
            'end_at' => $originalStart->copy()->addMinutes(45),
        ]);

        $response = $this->actingAs($frontDesk)->putToTenant("/appointments/{$appointment->id}/reschedule", [
            'start_at' => $newStart->toDateTimeString(),
            'reason' => 'client_requested',
        ]);

        $response->assertRedirect();
        $this->assertTrue($appointment->fresh()->start_at->equalTo($newStart));
        $this->assertDatabaseHas('appointment_status_histories', [
            'appointment_id' => $appointment->id,
            'reason' => 'client_requested',
        ]);
    }

    public function test_front_desk_can_cancel_an_appointment_with_a_reason(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');
        $appointment = Appointment::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($frontDesk)->putToTenant("/appointments/{$appointment->id}/cancel", [
            'reason' => 'no_availability',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'cancelled',
            'cancellation_reason' => 'no_availability',
        ]);
    }

    public function test_stylist_cannot_cancel_an_appointment(): void
    {
        $stylist = User::factory()->for($this->tenant)->create();
        $stylist->assignRole('Stylist');
        $appointment = Appointment::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($stylist)->putToTenant("/appointments/{$appointment->id}/cancel", [
            'reason' => 'client_requested',
        ]);

        $response->assertForbidden();
    }

    public function test_front_desk_can_mark_an_appointment_as_no_show(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');
        $appointment = Appointment::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($frontDesk)->putToTenant("/appointments/{$appointment->id}/no-show", []);

        $response->assertRedirect();
        $this->assertDatabaseHas('appointments', ['id' => $appointment->id, 'status' => 'no_show']);
    }
}
