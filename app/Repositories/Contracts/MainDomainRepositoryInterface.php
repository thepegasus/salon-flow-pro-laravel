<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface MainDomainRepositoryInterface
{
    /** @return Collection<int, string> */
    public function activeDomains(): Collection;
}
