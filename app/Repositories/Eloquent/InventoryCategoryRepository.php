<?php

namespace App\Repositories\Eloquent;

use App\Models\InventoryCategory;
use App\Repositories\Contracts\InventoryCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class InventoryCategoryRepository implements InventoryCategoryRepositoryInterface
{
    public function __construct(private InventoryCategory $model) {}

    public function findById(int $id): ?InventoryCategory
    {
        return $this->model->find($id);
    }

    /** @return Collection<int, InventoryCategory> */
    public function getAll(): Collection
    {
        return $this->model->orderBy('name')->get();
    }

    /** @return Collection<int, InventoryCategory> */
    public function getActive(): Collection
    {
        return $this->model->active()->orderBy('name')->get();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): InventoryCategory
    {
        return $this->model->create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(InventoryCategory $category, array $data): InventoryCategory
    {
        $category->update($data);

        return $category;
    }

    public function delete(InventoryCategory $category): bool
    {
        return (bool) $category->delete();
    }
}
