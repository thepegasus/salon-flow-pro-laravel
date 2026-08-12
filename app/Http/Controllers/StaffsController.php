<?php

namespace App\Http\Controllers;

use App\Http\Requests\Staff\StoreStaffRequest;
use App\Http\Requests\Staff\UpdateStaffRequest;
use App\Models\StaffProfile;
use App\Repositories\Contracts\StaffProfileRepositoryInterface;
use App\Services\StaffService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffsController extends Controller
{
    public function __construct(
        private StaffProfileRepositoryInterface $staffProfileRepository,
        private StaffService $staffService,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('staff.view'), 403);

        $staff = $this->staffProfileRepository->getActive();

        return view('admin.staff.index', ['staff' => $staff]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->can('staff.create'), 403);

        return view('admin.staff.create');
    }

    public function store(StoreStaffRequest $request): RedirectResponse
    {
        abort_unless($request->user()->can('staff.create'), 403);

        $staffProfile = $this->staffService->create($request->validated());

        return redirect()->route('staff.show', $staffProfile)->with('status', 'Staff member created.');
    }

    public function show(Request $request, StaffProfile $staff): View
    {
        abort_unless($request->user()->can('staff.view'), 403);

        return view('admin.staff.show', ['staff' => $staff]);
    }

    public function edit(Request $request, StaffProfile $staff): View
    {
        abort_unless($request->user()->can('staff.edit'), 403);

        return view('admin.staff.edit', ['staff' => $staff]);
    }

    public function update(UpdateStaffRequest $request, StaffProfile $staff): RedirectResponse
    {
        abort_unless($request->user()->can('staff.edit'), 403);

        $this->staffService->update($staff, $request->validated());

        return redirect()->route('staff.show', $staff)->with('status', 'Staff member updated.');
    }

    public function destroy(Request $request, StaffProfile $staff): RedirectResponse
    {
        abort_unless($request->user()->can('staff.delete'), 403);

        $this->staffProfileRepository->delete($staff);

        return redirect()->route('staff.index')->with('status', 'Staff member removed.');
    }
}
