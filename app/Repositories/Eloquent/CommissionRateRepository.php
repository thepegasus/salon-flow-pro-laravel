<?php

namespace App\Repositories\Eloquent;

use App\Models\CommissionRate;
use App\Repositories\Contracts\CommissionRateRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CommissionRateRepository implements CommissionRateRepositoryInterface
{
    public function __construct(private CommissionRate $model) {}

    public function findById(int $id): ?CommissionRate
    {
        return $this->model->find($id);
    }

    /** @return Collection<int, CommissionRate> */
    public function getAll(): Collection
    {
        return $this->model->with(['staffProfile.user', 'serviceCategory'])
            ->orderByDesc('effective_from')
            ->get();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): CommissionRate
    {
        return $this->model->create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(CommissionRate $rate, array $data): CommissionRate
    {
        $rate->update($data);

        return $rate;
    }

    public function delete(CommissionRate $rate): bool
    {
        return (bool) $rate->delete();
    }
}
