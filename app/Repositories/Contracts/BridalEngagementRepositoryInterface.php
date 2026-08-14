<?php

namespace App\Repositories\Contracts;

use App\Models\BridalEngagement;
use Illuminate\Database\Eloquent\Collection;

interface BridalEngagementRepositoryInterface
{
    public function findById(int $id): ?BridalEngagement;

    /** @return Collection<int, BridalEngagement> */
    public function getUpcoming(): Collection;

    /** @param array<string, mixed> $data */
    public function create(array $data): BridalEngagement;
}
