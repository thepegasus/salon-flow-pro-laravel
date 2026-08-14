<?php

namespace Tests\Unit\Appointments;

use App\Exceptions\StaffUnavailableException;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Service;
use App\Models\StaffProfile;
use App\Models\StaffShift;
use App\Models\Tenant;
use App\Models\User;
use App\Services\AppointmentService;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AppointmentServiceTest extends TestCase
{
    use RefreshDatabase;

    private function setUpWorkingStaff(Tenant $tenant, Carbon $at): StaffProfile
    {
        $staffProfile = StaffProfile::factory()->create(['tenant_id' => $tenant->id]);

        StaffShift::factory()->create([
            'tenant_id' => $tenant->id,
            'staff_profile_id' => $staffProfile->id,
            'day_of_week' => $at->dayOfWeek,
            'start_time' => '09:00',
            'end_time' => '18:00',
            'is_working' => true,
        ]);

        return $staffProfile;
    }

    public function test_book_creates_appointment_with_computed_duration_and_price(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);

        $start = now()->next(1)->setTime(10, 0);
        $staffProfile = $this->setUpWorkingStaff($tenant, $start);
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $service = Service::factory()->create(['tenant_id' => $tenant->id, 'price' => 500, 'duration_minutes' => 45]);

        $appointment = app(AppointmentService::class)->book(
            $client->id,
            $staffProfile->id,
            $start,
            [['service_id' => $service->id]],
        );

        $this->assertSame(45.0, $start->diffInMinutes($appointment->end_at));
        $this->assertSame('500', (string) $appointment->services->first()->pivot->price_at_booking);
    }

    public function test_book_accepts_multiple_standalone_services_with_no_parent_child_relationship(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);

        $start = now()->next(1)->setTime(10, 0);
        $staffProfile = $this->setUpWorkingStaff($tenant, $start);
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $haircut = Service::factory()->create(['tenant_id' => $tenant->id, 'price' => 500, 'duration_minutes' => 30]);
        $beardTrim = Service::factory()->create(['tenant_id' => $tenant->id, 'price' => 150, 'duration_minutes' => 15]);

        $appointment = app(AppointmentService::class)->book(
            $client->id,
            $staffProfile->id,
            $start,
            [['service_id' => $haircut->id], ['service_id' => $beardTrim->id]],
        );

        $this->assertSame(45.0, $start->diffInMinutes($appointment->end_at));
        $this->assertSame(2, $appointment->services->count());
        $this->assertTrue($appointment->services->pluck('id')->contains($beardTrim->id));
    }

    public function test_book_throws_when_staff_is_unavailable(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);

        $start = now()->next(1)->setTime(20, 0);
        $staffProfile = $this->setUpWorkingStaff($tenant, $start);
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $service = Service::factory()->create(['tenant_id' => $tenant->id]);

        $this->expectException(StaffUnavailableException::class);

        app(AppointmentService::class)->book($client->id, $staffProfile->id, $start, [['service_id' => $service->id]]);
    }

    public function test_cancel_records_reason_and_cancels_pending_reminders(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);

        $start = now()->next(1)->setTime(10, 0);
        $staffProfile = $this->setUpWorkingStaff($tenant, $start);
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $service = Service::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->for($tenant)->create();

        $appointment = app(AppointmentService::class)->book($client->id, $staffProfile->id, $start, [['service_id' => $service->id]]);

        app(AppointmentService::class)->cancel($appointment, 'client_requested', $user->id);

        $this->assertSame(Appointment::StatusCancelled, $appointment->fresh()->status);
        $this->assertSame('client_requested', $appointment->fresh()->cancellation_reason);
        $this->assertSame(0, $appointment->reminders()->where('status', 'pending')->count());
    }

    public function test_mark_no_show_flags_client_after_two_no_shows(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);

        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->for($tenant)->create();

        Appointment::factory()->create(['tenant_id' => $tenant->id, 'client_id' => $client->id, 'status' => Appointment::StatusNoShow]);
        $secondAppointment = Appointment::factory()->create(['tenant_id' => $tenant->id, 'client_id' => $client->id]);

        app(AppointmentService::class)->markNoShow($secondAppointment, $user->id);

        $this->assertTrue($client->fresh()->is_frequent_no_show);
    }
}
