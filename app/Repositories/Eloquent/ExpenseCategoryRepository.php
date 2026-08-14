<?php

namespace App\Repositories\Eloquent;

use App\Models\ExpenseCategory;
use App\Repositories\Contracts\ExpenseCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ExpenseCategoryRepository implements ExpenseCategoryRepositoryInterface
{
    public function __construct(private ExpenseCategory $model) {}

    public function findById(int $id): ?ExpenseCategory
    {
        return $this->model->find($id);
    }

    /** @return Collection<int, ExpenseCategory> */
    public function getAll(): Collection
    {
        return $this->model->orderBy('name')->get();
    }

    /** @return Collection<int, ExpenseCategory> */
    public function getActive(): Collection
    {
        return $this->model->active()->orderBy('name')->get();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): ExpenseCategory
    {
        return $this->model->create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(ExpenseCategory $category, array $data): ExpenseCategory
    {
        $category->update($data);

        return $category;
    }

    public function delete(ExpenseCategory $category): bool
    {
        return (bool) $category->delete();
    }
}
