<?php

namespace App\Repositories\Contracts;

use App\Models\TimeSlot;
use Illuminate\Database\Eloquent\Collection;

interface TimeSlotRepositoryInterface
{
    public function findById(int $id): ?TimeSlot;

    /** @return Collection<int, TimeSlot> */
    public function getAll(): Collection;

    /** @return Collection<int, TimeSlot> */
    public function getActive(): Collection;

    /** @param array<string, mixed> $data */
    public function create(array $data): TimeSlot;

    /** @param array<string, mixed> $data */
    public function update(TimeSlot $timeSlot, array $data): TimeSlot;

    public function delete(TimeSlot $timeSlot): bool;
}
