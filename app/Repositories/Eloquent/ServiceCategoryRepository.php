<?php

namespace App\Repositories\Eloquent;

use App\Models\ServiceCategory;
use App\Repositories\Contracts\ServiceCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ServiceCategoryRepository implements ServiceCategoryRepositoryInterface
{
    public function __construct(private ServiceCategory $model) {}

    public function findById(int $id): ?ServiceCategory
    {
        return $this->model->find($id);
    }

    /** @return Collection<int, ServiceCategory> */
    public function getAll(): Collection
    {
        return $this->model->orderBy('name')->get();
    }

    /** @return Collection<int, ServiceCategory> */
    public function getActive(): Collection
    {
        return $this->model->active()->orderBy('name')->get();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): ServiceCategory
    {
        return $this->model->create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(ServiceCategory $category, array $data): ServiceCategory
    {
        $category->update($data);

        return $category;
    }

    public function delete(ServiceCategory $category): bool
    {
        return (bool) $category->delete();
    }
}
