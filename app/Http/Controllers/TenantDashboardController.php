<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\DashboardService;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class TenantDashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
        private ProductRepositoryInterface $productRepository,
    ) {}

    public function index(): View
    {
        return view('admin.dashboard', [
            ...$this->dashboardService->summaryFor(Carbon::today()),
            'lowStockCount' => $this->productRepository->getLowStock()->count(),
        ]);
    }
}
