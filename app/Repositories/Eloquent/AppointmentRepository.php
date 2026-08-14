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
            ->with(['client', 'staffProfile.user', 'services'])
            ->orderBy('start_at')
            ->get();
    }

    /** @return Collection<int, Appointment> */
    public function getOverlappingForStaff(int $staffProfileId, Carbon $start, Carbon $end, ?int $excludingAppointmentId = null): Collection
    {
        return $this->model
            ->where('staff_profile_id', $staffProfileId)
            ->whereNotIn('status', [Appointment::StatusCancelled, Appointment::StatusNoShow])
            ->where('start_at', '<', $end)
            ->where('end_at', '>', $start)
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
