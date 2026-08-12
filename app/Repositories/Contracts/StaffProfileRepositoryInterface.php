<?php

namespace App\Repositories\Contracts;

use App\Models\StaffProfile;
use Illuminate\Database\Eloquent\Collection;

interface StaffProfileRepositoryInterface
{
    public function findById(int $id): ?StaffProfile;

    /** @return Collection<int, StaffProfile> */
    public function getActive(): Collection;

    /** @return Collection<int, StaffProfile> */
    public function getByService(int $serviceId): Collection;

    /** @param array<string, mixed> $data */
    public function create(array $data): StaffProfile;

    /** @param array<string, mixed> $data */
    public function update(StaffProfile $staffProfile, array $data): StaffProfile;

    public function delete(StaffProfile $staffProfile): bool;
}
