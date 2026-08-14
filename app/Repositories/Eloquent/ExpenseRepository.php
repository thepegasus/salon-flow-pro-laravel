<?php

namespace App\Repositories\Eloquent;

use App\Models\Expense;
use App\Repositories\Contracts\ExpenseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class ExpenseRepository implements ExpenseRepositoryInterface
{
    public function __construct(private Expense $model) {}

    public function findById(int $id): ?Expense
    {
        return $this->model->find($id);
    }

    /** @return Collection<int, Expense> */
    public function getAll(): Collection
    {
        return $this->model->with('category')->orderByDesc('expense_date')->get();
    }

    /** @return Collection<int, Expense> */
    public function getBetweenDates(Carbon $from, Carbon $to): Collection
    {
        return $this->model->betweenDates($from, $to)->with('category')->orderByDesc('expense_date')->get();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Expense
    {
        return $this->model->create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(Expense $expense, array $data): Expense
    {
        $expense->update($data);

        return $expense;
    }

    public function delete(Expense $expense): bool
    {
        return (bool) $expense->delete();
    }
}
