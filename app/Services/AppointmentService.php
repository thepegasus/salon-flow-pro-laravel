<?php

namespace App\Services;

use App\Exceptions\StaffUnavailableException;
use App\Models\Appointment;
use App\Models\AppointmentReminder;
use App\Models\Service;
use App\Models\StaffProfile;
use App\Repositories\Contracts\AppointmentRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AppointmentService
{
    public function __construct(
        private AppointmentRepositoryInterface $appointmentRepository,
        private StaffAvailabilityService $availabilityService,
        private TenantContext $tenantContext,
    ) {}

    /**
     * @param  array<int, array{service_id: int}>  $lineItems
     */
    public function book(int $clientId, int $staffProfileId, Carbon $startAt, array $lineItems, ?string $notes = null): Appointment
    {
        $tenant = $this->tenantContext->get();
        $staffProfile = StaffProfile::findOrFail($staffProfileId);

        [$totalMinutes, $resolvedLineItems] = $this->resolveLineItems($lineItems);
        $endAt = $startAt->copy()->addMinutes($totalMinutes);

        if (! $this->availabilityService->isAvailable($staffProfile, $startAt, $endAt)) {
            throw new StaffUnavailableException('The selected staff member is not available for this time slot.');
        }

        return DB::transaction(function () use ($tenant, $clientId, $staffProfileId, $startAt, $endAt, $resolvedLineItems, $notes): Appointment {
            $appointment = $this->appointmentRepository->create([
                'tenant_id' => $tenant->id,
                'client_id' => $clientId,
                'staff_profile_id' => $staffProfileId,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'status' => Appointment::StatusBooked,
                'notes' => $notes,
            ]);

            foreach ($resolvedLineItems as $item) {
                $appointment->services()->attach($item['service_id'], [
                    'price_at_booking' => $item['price'],
                    'duration_minutes_at_booking' => $item['duration_minutes'],
                ]);
            }

            $this->scheduleReminders($appointment);

            return $appointment->load('services');
        });
    }

    public function reschedule(Appointment $appointment, Carbon $newStartAt, ?string $reason, int $changedBy): Appointment
    {
        $durationMinutes = $appointment->start_at->diffInMinutes($appointment->end_at);
        $newEndAt = $newStartAt->copy()->addMinutes($durationMinutes);

        if (! $this->availabilityService->isAvailable($appointment->staffProfile, $newStartAt, $newEndAt, $appointment->id)) {
            throw new StaffUnavailableException('The selected staff member is not available for this time slot.');
        }

        return DB::transaction(function () use ($appointment, $newStartAt, $newEndAt, $reason, $changedBy): Appointment {
            $this->appointmentRepository->update($appointment, [
                'start_at' => $newStartAt,
                'end_at' => $newEndAt,
            ]);

            $this->recordStatusChange($appointment, $appointment->status, $appointment->status, $reason, $changedBy);
            $this->scheduleReminders($appointment);

            return $appointment->refresh();
        });
    }

    public function cancel(Appointment $appointment, string $reason, int $changedBy): Appointment
    {
        return DB::transaction(function () use ($appointment, $reason, $changedBy): Appointment {
            $fromStatus = $appointment->status;

            $this->appointmentRepository->update($appointment, [
                'status' => Appointment::StatusCancelled,
                'cancellation_reason' => $reason,
            ]);

            $this->recordStatusChange($appointment, $fromStatus, Appointment::StatusCancelled, $reason, $changedBy);
            $appointment->reminders()->where('status', AppointmentReminder::StatusPending)
                ->update(['status' => AppointmentReminder::StatusCancelled]);

            return $appointment->refresh();
        });
    }

    public function markNoShow(Appointment $appointment, int $changedBy): Appointment
    {
        return DB::transaction(function () use ($appointment, $changedBy): Appointment {
            $fromStatus = $appointment->status;

            $this->appointmentRepository->update($appointment, ['status' => Appointment::StatusNoShow]);
            $this->recordStatusChange($appointment, $fromStatus, Appointment::StatusNoShow, null, $changedBy);

            $this->flagClientIfRepeatedNoShow($appointment);

            return $appointment->refresh();
        });
    }

    /**
     * @param  array<int, array{service_id: int}>  $lineItems
     * @return array{0: int, 1: array<int, array{service_id: int, price: float, duration_minutes: int}>}
     */
    private function resolveLineItems(array $lineItems): array
    {
        $totalMinutes = 0;
        $resolved = [];

        foreach ($lineItems as $item) {
            $service = Service::findOrFail($item['service_id']);

            $totalMinutes += $service->duration_minutes;
            $resolved[] = [
                'service_id' => $service->id,
                'price' => (float) $service->price,
                'duration_minutes' => $service->duration_minutes,
            ];
        }

        return [$totalMinutes, $resolved];
    }

    private function recordStatusChange(Appointment $appointment, string $fromStatus, string $toStatus, ?string $reason, int $changedBy): void
    {
        $appointment->statusHistories()->create([
            'tenant_id' => $appointment->tenant_id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'reason' => $reason,
            'changed_by' => $changedBy,
        ]);
    }

    private function scheduleReminders(Appointment $appointment): void
    {
        $appointment->reminders()->where('status', AppointmentReminder::StatusPending)->delete();

        $appointment->reminders()->create([
            'tenant_id' => $appointment->tenant_id,
            'type' => AppointmentReminder::TypeConfirmation,
            'channel' => 'whatsapp',
            'scheduled_for' => now(),
            'status' => AppointmentReminder::StatusPending,
        ]);

        $appointment->reminders()->create([
            'tenant_id' => $appointment->tenant_id,
            'type' => AppointmentReminder::TypeReminder,
            'channel' => 'whatsapp',
            'scheduled_for' => $appointment->start_at->copy()->subHours(2),
            'status' => AppointmentReminder::StatusPending,
        ]);
    }

    private function flagClientIfRepeatedNoShow(Appointment $appointment): void
    {
        $noShowCount = Appointment::where('client_id', $appointment->client_id)
            ->where('status', Appointment::StatusNoShow)
            ->count();

        if ($noShowCount >= 2) {
            $appointment->client->update(['is_frequent_no_show' => true]);
        }
    }
}
