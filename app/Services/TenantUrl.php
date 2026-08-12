<?php

namespace App\Services;

class TenantUrl
{
    public function __construct(private TenantContext $tenantContext) {}

    /**
     * Route to a tenant-scoped named route, picking the subdomain or
     * slug-prefixed variant depending on how the current request arrived,
     * so callers never need to know which mode is active.
     *
     * @param  array<string, mixed>  $parameters
     */
    public function route(string $name, array $parameters = []): string
    {
        $tenant = $this->tenantContext->get();
        $slugPrefix = request()->attributes->get('tenant_slug_prefix');

        if ($slugPrefix && $tenant) {
            return route($name.'.bySlug', ['slug' => $tenant->slug, ...$parameters]);
        }

        return route($name, $parameters);
    }
}
