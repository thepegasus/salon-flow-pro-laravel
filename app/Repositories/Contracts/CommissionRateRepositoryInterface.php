<?php

namespace App\Repositories\Contracts;

use App\Models\CommissionRate;
use Illuminate\Database\Eloquent\Collection;

interface CommissionRateRepositoryInterface
{
    public function findById(int $id): ?CommissionRate;

    /** @return Collection<int, CommissionRate> */
    public function getAll(): Collection;

    /** @param array<string, mixed> $data */
    public function create(array $data): CommissionRate;

    /** @param array<string, mixed> $data */
    public function update(CommissionRate $rate, array $data): CommissionRate;

    public function delete(CommissionRate $rate): bool;
}
