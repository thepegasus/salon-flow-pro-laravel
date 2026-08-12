<?php

namespace App\Repositories\Contracts;

use App\Models\StaffLeaveRequest;
use Illuminate\Database\Eloquent\Collection;

interface StaffLeaveRequestRepositoryInterface
{
    public function findById(int $id): ?StaffLeaveRequest;

    /** @return Collection<int, StaffLeaveRequest> */
    public function getPending(): Collection;

    /** @param array<string, mixed> $data */
    public function create(array $data): StaffLeaveRequest;

    /** @param array<string, mixed> $data */
    public function update(StaffLeaveRequest $leaveRequest, array $data): StaffLeaveRequest;
}
