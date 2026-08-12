<?php

namespace App\Repositories\Eloquent;

use App\Models\MainDomain;
use App\Repositories\Contracts\MainDomainRepositoryInterface;
use Illuminate\Support\Collection;

class MainDomainRepository implements MainDomainRepositoryInterface
{
    public function __construct(private MainDomain $model) {}

    /** @return Collection<int, string> */
    public function activeDomains(): Collection
    {
        return $this->model->active()->pluck('domain');
    }
}
