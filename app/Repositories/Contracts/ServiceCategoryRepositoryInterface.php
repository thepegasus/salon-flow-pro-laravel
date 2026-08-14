<?php

namespace App\Repositories\Contracts;

use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Collection;

interface ServiceCategoryRepositoryInterface
{
    public function findById(int $id): ?ServiceCategory;

    /** @return Collection<int, ServiceCategory> */
    public function getAll(): Collection;

    /** @return Collection<int, ServiceCategory> */
    public function getActive(): Collection;

    /** @param array<string, mixed> $data */
    public function create(array $data): ServiceCategory;

    /** @param array<string, mixed> $data */
    public function update(ServiceCategory $category, array $data): ServiceCategory;

    public function delete(ServiceCategory $category): bool;
}
