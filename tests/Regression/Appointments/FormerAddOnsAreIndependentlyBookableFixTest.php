<?php

namespace Tests\Regression\Appointments;

use App\Models\Client;
use App\Models\Service;
use App\Models\StaffProfile;
use App\Models\StaffShift;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class FormerAddOnsAreIndependentlyBookableFixTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    /**
     * Bug: services that only made sense as an "add-on" (e.g. a head
     * massage or beard trim) could never be booked or billed on their own —
     * AppointmentService::resolveLineItems() required an add-on to always
     * ride along with a distinct base service_id, throwing otherwise. In
     * practice there was no such thing as an "add-on only" service, so this
     * was a real limitation. Add-ons were merged into plain, standalone
     * services; this proves one can now be booked completely by itself.
     */
    public function test_a_service_can_be_booked_entirely_on_its_own(): void
    {
        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);

        $owner = User::factory()->for($this->tenant)->create();
        $owner->assignRole('Owner');
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
        $headMassage = Service::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Head massage', 'price' => 200]);

        $response = $this->actingAs($owner)->postToTenant('/appointments', [
            'client_id' => $client->id,
            'staff_profile_id' => $staffProfile->id,
            'start_at' => $start->toDateTimeString(),
            'services' => [
                ['service_id' => $headMassage->id],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('appointment_service', [
            'service_id' => $headMassage->id,
        ]);
    }
}
