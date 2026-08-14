<?php

namespace App\Repositories\Eloquent;

use App\Models\TimeSlot;
use App\Repositories\Contracts\TimeSlotRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TimeSlotRepository implements TimeSlotRepositoryInterface
{
    public function __construct(private TimeSlot $model) {}

    public function findById(int $id): ?TimeSlot
    {
        return $this->model->find($id);
    }

    /** @return Collection<int, TimeSlot> */
    public function getAll(): Collection
    {
        return $this->model->orderBy('start_time')->get();
    }

    /** @return Collection<int, TimeSlot> */
    public function getActive(): Collection
    {
        return $this->model->active()->orderBy('start_time')->get();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): TimeSlot
    {
        return $this->model->create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(TimeSlot $timeSlot, array $data): TimeSlot
    {
        $timeSlot->update($data);

        return $timeSlot;
    }

    public function delete(TimeSlot $timeSlot): bool
    {
        return (bool) $timeSlot->delete();
    }
}
