<?php

namespace App\Http\Controllers;

use App\Services\TenantContext;
use Illuminate\Http\Response;

class TenantDashboardController extends Controller
{
    public function __construct(private TenantContext $tenantContext) {}

    public function index(): Response
    {
        return response("Tenant dashboard for: {$this->tenantContext->get()->name}");
    }
}
