<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdminRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->attributes->get('is_super_admin_request')) {
            abort(404);
        }

        return $next($request);
    }
}
