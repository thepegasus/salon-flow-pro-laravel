<?php

namespace App\Services;

use App\Models\Tenant;

class TenantContext
{
    private ?Tenant $tenant = null;

    private bool $bypassed = false;

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
}
