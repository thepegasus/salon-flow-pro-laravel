<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\StaffProfileRepositoryInterface;
use App\Services\CommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class CommissionEarningsController extends Controller
{
    public function __construct(
        private StaffProfileRepositoryInterface $staffProfileRepository,
        private CommissionService $commissionService,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('commissions.view'), 403);

        $from = $request->filled('from') ? Carbon::parse($request->string('from')->toString()) : Carbon::now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->string('to')->toString()) : Carbon::now()->endOfMonth();

        $requestedStaffId = $request->integer('staff_profile_id') ?: null;
        $ownStaffProfile = $request->user()->staffProfile;

        if (! $request->user()->can('commissions.create') && ! $request->user()->can('commissions.edit')) {
            abort_unless($ownStaffProfile !== null, 403);
            abort_if($requestedStaffId !== null && $requestedStaffId !== $ownStaffProfile->id, 403);

            $requestedStaffId = $ownStaffProfile->id;
        }

        $staffList = $this->staffProfileRepository->getActive();

        $earnings = $staffList
            ->when($requestedStaffId, fn ($staff) => $staff->where('id', $requestedStaffId))
            ->map(fn ($staff) => [
                'staff' => $staff,
                ...$this->commissionService->earningsFor($staff, $from, $to),
            ]);

        return view('admin.commission.earnings.index', [
            'earnings' => $earnings,
            'from' => $from,
            'to' => $to,
            'staffList' => $staffList,
            'selectedStaffId' => $requestedStaffId,
        ]);
    }
}
