<?php

namespace App\Repositories\Contracts;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

interface AppointmentRepositoryInterface
{
    public function findById(int $id): ?Appointment;

    /** @return Collection<int, Appointment> */
    public function getForDate(Carbon $date): Collection;

    /** @return Collection<int, Appointment> */
    public function getOverlappingForStaff(int $staffProfileId, Carbon $start, Carbon $end, ?int $excludingAppointmentId = null): Collection;

    /** @param array<string, mixed> $data */
    public function create(array $data): Appointment;

    /** @param array<string, mixed> $data */
    public function update(Appointment $appointment, array $data): Appointment;
}
