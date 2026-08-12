<?php

namespace App\Repositories\Contracts;

use App\Models\Tenant;

interface TenantRepositoryInterface
{
    public function findActiveByCustomDomain(string $domain): ?Tenant;

    public function findActiveBySubdomain(string $subdomain): ?Tenant;

    public function findActiveBySlug(string $slug): ?Tenant;
}
