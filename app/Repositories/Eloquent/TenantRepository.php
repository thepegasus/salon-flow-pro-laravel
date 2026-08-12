<?php

namespace App\Repositories\Eloquent;

use App\Models\Tenant;
use App\Repositories\Contracts\TenantRepositoryInterface;

class TenantRepository implements TenantRepositoryInterface
{
    public function __construct(private Tenant $model) {}

    public function findActiveByCustomDomain(string $domain): ?Tenant
    {
        return $this->model->active()->where('custom_domain', $domain)->first();
    }

    public function findActiveBySubdomain(string $subdomain): ?Tenant
    {
        return $this->model->active()->where('subdomain', $subdomain)->first();
    }

    public function findActiveBySlug(string $slug): ?Tenant
    {
        return $this->model->active()->where('slug', $slug)->first();
    }
}
