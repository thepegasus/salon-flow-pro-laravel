<?php

namespace App\Repositories\Contracts;

use App\Models\InventoryCategory;
use Illuminate\Database\Eloquent\Collection;

interface InventoryCategoryRepositoryInterface
{
    public function findById(int $id): ?InventoryCategory;

    /** @return Collection<int, InventoryCategory> */
    public function getAll(): Collection;

    /** @return Collection<int, InventoryCategory> */
    public function getActive(): Collection;

    /** @param array<string, mixed> $data */
    public function create(array $data): InventoryCategory;

    /** @param array<string, mixed> $data */
    public function update(InventoryCategory $category, array $data): InventoryCategory;

    public function delete(InventoryCategory $category): bool;
}
