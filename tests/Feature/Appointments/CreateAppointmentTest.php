<?php

namespace Tests\Feature\Appointments;

use App\Models\Client;
use App\Models\Service;
use App\Models\StaffProfile;
use App\Models\StaffShift;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class CreateAppointmentTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_front_desk_can_book_an_appointment(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');

        $start = now()->next(1)->setTime(10, 0);
        $staffProfile = StaffProfile::factory()->create(['tenant_id' => $this->tenant->id]);
        StaffShift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'staff_profile_id' => $staffProfile->id,
            'day_of_week' => $start->dayOfWeek,
            'start_time' => '09:00',
            'end_time' => '18:00',
            'is_working' => true,
        ]);
        $client = Client::factory()->create(['tenant_id' => $this->tenant->id]);
        $service = Service::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($frontDesk)->postToTenant('/appointments', [
            'client_id' => $client->id,
            'staff_profile_id' => $staffProfile->id,
            'start_at' => $start->toDateTimeString(),
            'services' => [['service_id' => $service->id]],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('appointments', [
            'client_id' => $client->id,
            'staff_profile_id' => $staffProfile->id,
        ]);
    }

    public function test_booking_outside_working_hours_fails_with_validation_error(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');

        $start = now()->next(1)->setTime(22, 0);
        $staffProfile = StaffProfile::factory()->create(['tenant_id' => $this->tenant->id]);
        StaffShift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'staff_profile_id' => $staffProfile->id,
            'day_of_week' => $start->dayOfWeek,
            'start_time' => '09:00',
            'end_time' => '18:00',
            'is_working' => true,
        ]);
        $client = Client::factory()->create(['tenant_id' => $this->tenant->id]);
        $service = Service::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($frontDesk)->postToTenant('/appointments', [
            'client_id' => $client->id,
            'staff_profile_id' => $staffProfile->id,
            'start_at' => $start->toDateTimeString(),
            'services' => [['service_id' => $service->id]],
        ]);

        $response->assertSessionHasErrors('staff_profile_id');
    }

    public function test_validation_rejects_missing_services(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');
        $staffProfile = StaffProfile::factory()->create(['tenant_id' => $this->tenant->id]);
        $client = Client::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($frontDesk)->postToTenant('/appointments', [
            'client_id' => $client->id,
            'staff_profile_id' => $staffProfile->id,
            'start_at' => now()->addDay()->toDateTimeString(),
        ]);

        $response->assertSessionHasErrors('services');
    }
}
