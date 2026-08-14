<?php

namespace App\Repositories\Eloquent;

use App\Models\Designation;
use App\Repositories\Contracts\DesignationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class DesignationRepository implements DesignationRepositoryInterface
{
    public function __construct(private Designation $model) {}

    public function findById(int $id): ?Designation
    {
        return $this->model->find($id);
    }

    /** @return Collection<int, Designation> */
    public function getAll(): Collection
    {
        return $this->model->orderBy('name')->get();
    }

    /** @return Collection<int, Designation> */
    public function getActive(): Collection
    {
        return $this->model->active()->orderBy('name')->get();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Designation
    {
        return $this->model->create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(Designation $designation, array $data): Designation
    {
        $designation->update($data);

        return $designation;
    }

    public function delete(Designation $designation): bool
    {
        return (bool) $designation->delete();
    }
}
