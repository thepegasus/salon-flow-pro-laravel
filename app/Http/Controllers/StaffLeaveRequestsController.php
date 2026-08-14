<?php

namespace App\Http\Controllers;

use App\Http\Requests\Staff\DecideLeaveRequestRequest;
use App\Http\Requests\Staff\StoreLeaveRequestRequest;
use App\Models\StaffLeaveRequest;
use App\Repositories\Contracts\StaffLeaveRequestRepositoryInterface;
use App\Services\LeaveRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffLeaveRequestsController extends Controller
{
    public function __construct(
        private StaffLeaveRequestRepositoryInterface $leaveRequestRepository,
        private LeaveRequestService $leaveRequestService,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('staff.view'), 403);

        $leaveRequests = $this->leaveRequestRepository->getPending();

        return view('admin.staff.leave-requests.index', ['leaveRequests' => $leaveRequests]);
    }

    public function store(StoreLeaveRequestRequest $request): RedirectResponse
    {
        abort_unless($request->user()->can('staff.edit'), 403);

        $this->leaveRequestService->request($request->validated());

        return redirect()->route('staff.leaveRequests.index')->with('status', 'Leave request submitted.');
    }

    public function update(DecideLeaveRequestRequest $request, string $subdomain, StaffLeaveRequest $leaveRequest): RedirectResponse
    {
        abort_unless($request->user()->can('staff.edit'), 403);

        $data = $request->validated();

        if ($data['status'] === 'approved') {
            $this->leaveRequestService->approve($leaveRequest, $request->user()->id, $data['decision_note'] ?? null);
        } else {
            $this->leaveRequestService->reject($leaveRequest, $request->user()->id, $data['decision_note'] ?? null);
        }

        return redirect()->route('staff.leaveRequests.index')->with('status', 'Leave request updated.');
    }
}
