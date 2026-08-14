<?php

namespace App\Http\Controllers;

use App\Exceptions\StaffUnavailableException;
use App\Http\Requests\Appointments\AssignWalkInRequest;
use App\Http\Requests\Appointments\StoreWalkInRequest;
use App\Models\WalkIn;
use App\Services\TenantUrl;
use App\Services\WalkInService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalkInsController extends Controller
{
    public function __construct(
        private WalkInService $walkInService,
        private TenantUrl $tenantUrl,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('appointments.view'), 403);

        $walkIns = WalkIn::waiting()->get();

        return view('admin.walk-ins.index', ['walkIns' => $walkIns]);
    }

    public function store(StoreWalkInRequest $request): RedirectResponse
    {
        abort_unless($request->user()->can('appointments.create'), 403);

        $this->walkInService->join($request->validated());

        return redirect($this->tenantUrl->route('walkIns.index'))->with('status', 'Walk-in added to the queue.');
    }

    public function assign(AssignWalkInRequest $request, string $subdomain, WalkIn $walkIn): RedirectResponse
    {
        abort_unless($request->user()->can('appointments.edit'), 403);

        $data = $request->validated();

        try {
            $this->walkInService->assign($walkIn, $data['staff_profile_id'], $data['client_id']);
        } catch (StaffUnavailableException $exception) {
            return back()->withErrors(['staff_profile_id' => $exception->getMessage()]);
        }

        return redirect($this->tenantUrl->route('walkIns.index'))->with('status', 'Walk-in assigned.');
    }
}
