<?php

namespace App\Services;

use App\Models\WalkIn;
use Illuminate\Support\Facades\DB;

class WalkInService
{
    public function __construct(
        private AppointmentService $appointmentService,
        private TenantContext $tenantContext,
    ) {}

    /** @param array<string, mixed> $data */
    public function join(array $data): WalkIn
    {
        $tenant = $this->tenantContext->get();

        return WalkIn::create([
            'tenant_id' => $tenant->id,
            'client_id' => $data['client_id'] ?? null,
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'service_id' => $data['service_id'] ?? null,
            'status' => WalkIn::StatusWaiting,
            'joined_at' => now(),
        ]);
    }

    public function assign(WalkIn $walkIn, int $staffProfileId, int $clientId): WalkIn
    {
        return DB::transaction(function () use ($walkIn, $staffProfileId, $clientId): WalkIn {
            $lineItems = $walkIn->service_id ? [['service_id' => $walkIn->service_id]] : [];

            $appointment = $lineItems !== []
                ? $this->appointmentService->book($clientId, $staffProfileId, now(), $lineItems)
                : null;

            $walkIn->update([
                'client_id' => $clientId,
                'assigned_staff_profile_id' => $staffProfileId,
                'appointment_id' => $appointment?->id,
                'status' => WalkIn::StatusAssigned,
            ]);

            return $walkIn->refresh();
        });
    }
}
