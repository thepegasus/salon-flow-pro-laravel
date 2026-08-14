<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantRequest
{
    public function __construct(private TenantContext $tenantContext) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->tenantContext->has()) {
            abort(404);
        }

        URL::defaults(['subdomain' => $this->tenantContext->get()->subdomain]);

        return $next($request);
    }
}
