<?php

namespace App\Repositories\Eloquent;

use App\Models\StaffLeaveRequest;
use App\Repositories\Contracts\StaffLeaveRequestRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class StaffLeaveRequestRepository implements StaffLeaveRequestRepositoryInterface
{
    public function __construct(private StaffLeaveRequest $model) {}

    public function findById(int $id): ?StaffLeaveRequest
    {
        return $this->model->find($id);
    }

    /** @return Collection<int, StaffLeaveRequest> */
    public function getPending(): Collection
    {
        return $this->model->pending()->get();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): StaffLeaveRequest
    {
        return $this->model->create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(StaffLeaveRequest $leaveRequest, array $data): StaffLeaveRequest
    {
        $leaveRequest->update($data);

        return $leaveRequest;
    }
}
