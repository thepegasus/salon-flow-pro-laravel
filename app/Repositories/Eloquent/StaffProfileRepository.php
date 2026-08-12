<?php

namespace App\Repositories\Eloquent;

use App\Models\StaffProfile;
use App\Repositories\Contracts\StaffProfileRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class StaffProfileRepository implements StaffProfileRepositoryInterface
{
    public function __construct(private StaffProfile $model) {}

    public function findById(int $id): ?StaffProfile
    {
        return $this->model->find($id);
    }

    /** @return Collection<int, StaffProfile> */
    public function getActive(): Collection
    {
        return $this->model->active()->get();
    }

    /** @return Collection<int, StaffProfile> */
    public function getByService(int $serviceId): Collection
    {
        return $this->model->active()->whereHas('services', function ($query) use ($serviceId): void {
            $query->where('services.id', $serviceId);
        })->get();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): StaffProfile
    {
        return $this->model->create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(StaffProfile $staffProfile, array $data): StaffProfile
    {
        $staffProfile->update($data);

        return $staffProfile;
    }

    public function delete(StaffProfile $staffProfile): bool
    {
        return (bool) $staffProfile->delete();
    }
}
