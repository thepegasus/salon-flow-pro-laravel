<?php

namespace App\Services;

use App\Models\Tenant;

class TenantContext
{
    private ?Tenant $tenant = null;

    private bool $bypassed = false;

    private bool $noneResolved = false;

    public function set(Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function get(): ?Tenant
    {
        return $this->tenant;
    }

    public function has(): bool
    {
        return $this->tenant !== null;
    }

    /**
     * Explicitly bypass tenant scoping for a deliberate, admin-only context.
     * Per project tenant isolation rules, this must never be the default path.
     */
    public function isBypassed(): bool
    {
        return $this->bypassed;
    }

    public function bypass(): void
    {
        $this->bypassed = true;
    }

    /**
     * Marks that tenant resolution genuinely ran and found no tenant for this
     * request (e.g. the bare main domain's public pages) — distinct from a
     * bug where TenantContext::set() was simply never called. Tenant-scoped
     * queries return empty instead of throwing, so a stray cookie from a
     * tenant subdomain is treated as "not logged in" here rather than
     * crashing or, via bypass(), leaking data across every tenant.
     */
    public function markNoneResolved(): void
    {
        $this->noneResolved = true;
    }

    public function isNoneResolved(): bool
    {
        return $this->noneResolved;
    }
}
