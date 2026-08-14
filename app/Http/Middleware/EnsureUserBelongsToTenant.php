<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Session cookies are shared across the whole domain (SESSION_DOMAIN), so the
 * same cookie is sent to every tenant subdomain and the main domain. If a
 * logged-in user's tenant_id doesn't match the tenant resolved for the
 * current request's host, their session must not be honoured here — log
 * them out and send them to this subdomain's own login instead of silently
 * exposing another tenant's data or throwing on the mismatched query.
 */
class EnsureUserBelongsToTenant
{
    public function __construct(private TenantContext $tenantContext) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $tenant = $this->tenantContext->get();

        if ($user && $tenant && $user->tenant_id !== $tenant->id) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $slugPrefix = $request->attributes->get('tenant_slug_prefix');
            $loginPath = $slugPrefix ? "/{$slugPrefix}/login" : '/login';

            return redirect()->to($request->getSchemeAndHttpHost().$loginPath);
        }

        return $next($request);
    }
}
