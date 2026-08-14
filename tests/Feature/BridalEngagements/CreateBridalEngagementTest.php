<?php

namespace Tests\Feature\BridalEngagements;

use App\Models\Client;
use App\Models\Service;
use App\Models\StaffProfile;
use App\Models\StaffShift;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class CreateBridalEngagementTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
        $this->seed(PermissionSeeder::class);
    }

    public function test_front_desk_can_create_a_bridal_engagement_with_trial_and_event_day(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');

        $client = Client::factory()->create(['tenant_id' => $this->tenant->id]);
        $trialStart = now()->next(1)->setTime(10, 0);
        $eventStart = now()->next(1)->addWeeks(2)->setTime(9, 0);

        $trialStaff = StaffProfile::factory()->create(['tenant_id' => $this->tenant->id]);
        StaffShift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'staff_profile_id' => $trialStaff->id,
            'day_of_week' => $trialStart->dayOfWeek,
            'start_time' => '09:00',
            'end_time' => '20:00',
            'is_working' => true,
        ]);

        $eventStaff = StaffProfile::factory()->create(['tenant_id' => $this->tenant->id]);
        StaffShift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'staff_profile_id' => $eventStaff->id,
            'day_of_week' => $eventStart->dayOfWeek,
            'start_time' => '09:00',
            'end_time' => '20:00',
            'is_working' => true,
        ]);

        $service = Service::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($frontDesk)->postToTenant('/bridal-engagements', [
            'client_id' => $client->id,
            'event_date' => $eventStart->toDateString(),
            'venue' => 'The Grand Ballroom',
            'trial_staff_profile_id' => $trialStaff->id,
            'trial_start_at' => $trialStart->toDateTimeString(),
            'trial_services' => [['service_id' => $service->id]],
            'event_staff_profile_id' => $eventStaff->id,
            'event_start_at' => $eventStart->toDateTimeString(),
            'event_services' => [['service_id' => $service->id]],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bridal_engagements', ['client_id' => $client->id, 'venue' => 'The Grand Ballroom']);
        $this->assertDatabaseHas('appointments', ['client_id' => $client->id, 'engagement_role' => 'trial']);
        $this->assertDatabaseHas('appointments', ['client_id' => $client->id, 'engagement_role' => 'event_day']);
    }

    public function test_validation_rejects_missing_event_services(): void
    {
        $frontDesk = User::factory()->for($this->tenant)->create();
        $frontDesk->assignRole('FrontDesk');
        $client = Client::factory()->create(['tenant_id' => $this->tenant->id]);
        $staffProfile = StaffProfile::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($frontDesk)->postToTenant('/bridal-engagements', [
            'client_id' => $client->id,
            'event_date' => now()->addMonth()->toDateString(),
            'trial_staff_profile_id' => $staffProfile->id,
            'trial_start_at' => now()->addDay()->toDateTimeString(),
            'trial_services' => [['service_id' => 1]],
            'event_staff_profile_id' => $staffProfile->id,
            'event_start_at' => now()->addWeeks(2)->toDateTimeString(),
        ]);

        $response->assertSessionHasErrors('event_services');
    }
}
