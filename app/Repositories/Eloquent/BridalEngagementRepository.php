<?php

namespace App\Repositories\Eloquent;

use App\Models\BridalEngagement;
use App\Repositories\Contracts\BridalEngagementRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BridalEngagementRepository implements BridalEngagementRepositoryInterface
{
    public function __construct(private BridalEngagement $model) {}

    public function findById(int $id): ?BridalEngagement
    {
        return $this->model->find($id);
    }

    /** @return Collection<int, BridalEngagement> */
    public function getUpcoming(): Collection
    {
        return $this->model->where('event_date', '>=', now()->toDateString())
            ->with(['client', 'appointments', 'travelingStaff.user'])
            ->orderBy('event_date')
            ->get();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): BridalEngagement
    {
        return $this->model->create($data);
    }
}
