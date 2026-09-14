<?php

namespace App\Repositories\Eloquent;

use App\Models\Appointment;
use App\Repositories\Contracts\AppointmentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class AppointmentRepository implements AppointmentRepositoryInterface
{
    public function __construct(private Appointment $model) {}

    public function findById(int $id): ?Appointment
    {
        return $this->model->find($id);
    }

    /** @return Collection<int, Appointment> */
    public function getForDate(Carbon $date): Collection
    {
        return $this->model->onDate($date->toDateString())
            ->with(['client', 'services', 'staffProfiles'])
            ->orderBy('start_at')
            ->get();
    }

    /** @return Collection<int, Appointment> */
    public function getOverlappingForStaff(int $staffProfileId, Carbon $start, Carbon $end, ?int $excludingAppointmentId = null): Collection
    {
        return $this->model
            ->whereNotIn('status', [Appointment::StatusCancelled, Appointment::StatusNoShow])
            ->whereHas('services', function ($query) use ($staffProfileId, $start, $end): void {
                $query->where('appointment_service.staff_profile_id', $staffProfileId)
                    ->where('appointment_service.start_at', '<', $end)
                    ->where('appointment_service.end_at', '>', $start);
            })
            ->when($excludingAppointmentId, fn ($query) => $query->where('id', '!=', $excludingAppointmentId))
            ->get();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Appointment
    {
        return $this->model->create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(Appointment $appointment, array $data): Appointment
    {
        $appointment->update($data);

        return $appointment;
    }
}
