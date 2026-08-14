<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class TenantDashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    public function index(): View
    {
        return view('admin.dashboard', $this->dashboardService->summaryFor(Carbon::today()));
    }
}
