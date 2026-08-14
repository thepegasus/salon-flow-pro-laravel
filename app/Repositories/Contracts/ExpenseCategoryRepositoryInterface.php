<?php

namespace App\Repositories\Contracts;

use App\Models\ExpenseCategory;
use Illuminate\Database\Eloquent\Collection;

interface ExpenseCategoryRepositoryInterface
{
    public function findById(int $id): ?ExpenseCategory;

    /** @return Collection<int, ExpenseCategory> */
    public function getAll(): Collection;

    /** @return Collection<int, ExpenseCategory> */
    public function getActive(): Collection;

    /** @param array<string, mixed> $data */
    public function create(array $data): ExpenseCategory;

    /** @param array<string, mixed> $data */
    public function update(ExpenseCategory $category, array $data): ExpenseCategory;

    public function delete(ExpenseCategory $category): bool;
}
