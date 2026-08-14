<?php

namespace App\Services;

use App\Models\BridalEngagement;
use App\Repositories\Contracts\BridalEngagementRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BridalEngagementService
{
    public function __construct(
        private BridalEngagementRepositoryInterface $bridalEngagementRepository,
        private AppointmentService $appointmentService,
        private TenantContext $tenantContext,
    ) {}

    /**
     * @param  array<int, array{service_id: int}>  $trialLineItems
     * @param  array<int, array{service_id: int}>  $eventLineItems
     * @param  array<int, int>  $travelingStaffProfileIds
     */
    public function createEngagement(
        int $clientId,
        Carbon $eventDate,
        ?string $venue,
        int $trialStaffProfileId,
        Carbon $trialStartAt,
        array $trialLineItems,
        int $eventStaffProfileId,
        Carbon $eventStartAt,
        array $eventLineItems,
        array $travelingStaffProfileIds = [],
        bool $eventIsOnLocation = true,
    ): BridalEngagement {
        $tenant = $this->tenantContext->get();

        return DB::transaction(function () use (
            $tenant, $clientId, $eventDate, $venue,
            $trialStaffProfileId, $trialStartAt, $trialLineItems,
            $eventStaffProfileId, $eventStartAt, $eventLineItems,
            $travelingStaffProfileIds, $eventIsOnLocation,
        ): BridalEngagement {
            $engagement = $this->bridalEngagementRepository->create([
                'tenant_id' => $tenant->id,
                'client_id' => $clientId,
                'event_date' => $eventDate->toDateString(),
                'venue' => $venue,
                'status' => BridalEngagement::StatusPlanned,
            ]);

            $trialAppointment = $this->appointmentService->book(
                $clientId, $trialStaffProfileId, $trialStartAt, $trialLineItems,
            );
            $trialAppointment->update([
                'bridal_engagement_id' => $engagement->id,
                'engagement_role' => BridalEngagement::RoleTrial,
            ]);

            $eventAppointment = $this->appointmentService->book(
                $clientId, $eventStaffProfileId, $eventStartAt, $eventLineItems,
            );
            $eventAppointment->update([
                'bridal_engagement_id' => $engagement->id,
                'engagement_role' => BridalEngagement::RoleEventDay,
                'is_on_location' => $eventIsOnLocation,
                'venue_address' => $eventIsOnLocation ? $venue : null,
            ]);

            if ($travelingStaffProfileIds !== []) {
                $engagement->travelingStaff()->sync($travelingStaffProfileIds);
            }

            return $engagement->refresh()->load(['appointments', 'travelingStaff']);
        });
    }

    public function markTrialCompleted(BridalEngagement $engagement): BridalEngagement
    {
        $engagement->update(['status' => BridalEngagement::StatusTrialCompleted]);

        return $engagement->refresh();
    }

    public function complete(BridalEngagement $engagement): BridalEngagement
    {
        $engagement->update(['status' => BridalEngagement::StatusCompleted]);

        return $engagement->refresh();
    }
}
