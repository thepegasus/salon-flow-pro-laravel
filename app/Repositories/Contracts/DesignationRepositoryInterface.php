<?php

namespace App\Repositories\Contracts;

use App\Models\Designation;
use Illuminate\Database\Eloquent\Collection;

interface DesignationRepositoryInterface
{
    public function findById(int $id): ?Designation;

    /** @return Collection<int, Designation> */
    public function getAll(): Collection;

    /** @return Collection<int, Designation> */
    public function getActive(): Collection;

    /** @param array<string, mixed> $data */
    public function create(array $data): Designation;

    /** @param array<string, mixed> $data */
    public function update(Designation $designation, array $data): Designation;

    public function delete(Designation $designation): bool;
}
