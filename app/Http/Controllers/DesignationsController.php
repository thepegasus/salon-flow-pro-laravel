<?php

namespace App\Http\Controllers;

use App\Http\Requests\Staff\StoreDesignationRequest;
use App\Http\Requests\Staff\UpdateDesignationRequest;
use App\Models\Designation;
use App\Repositories\Contracts\DesignationRepositoryInterface;
use App\Services\TenantContext;
use App\Services\TenantUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DesignationsController extends Controller
{
    public function __construct(
        private DesignationRepositoryInterface $designationRepository,
        private TenantContext $tenantContext,
        private TenantUrl $tenantUrl,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('staff.view'), 403);

        $designations = $this->designationRepository->getAll();

        return view('admin.designations.index', ['designations' => $designations]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->can('staff.create'), 403);

        return view('admin.designations.create');
    }

    public function store(StoreDesignationRequest $request): RedirectResponse
    {
        abort_unless($request->user()->can('staff.create'), 403);

        $this->designationRepository->create([
            ...$request->validated(),
            'tenant_id' => $this->tenantContext->get()->id,
        ]);

        return redirect($this->tenantUrl->route('designations.index'))->with('status', 'Designation created.');
    }

    public function edit(Request $request, string $subdomain, Designation $designation): View
    {
        abort_unless($request->user()->can('staff.edit'), 403);

        return view('admin.designations.edit', ['designation' => $designation]);
    }

    public function update(UpdateDesignationRequest $request, string $subdomain, Designation $designation): RedirectResponse
    {
        abort_unless($request->user()->can('staff.edit'), 403);

        $this->designationRepository->update($designation, $request->validated());

        return redirect($this->tenantUrl->route('designations.index'))->with('status', 'Designation updated.');
    }

    public function destroy(Request $request, string $subdomain, Designation $designation): RedirectResponse
    {
        abort_unless($request->user()->can('staff.delete'), 403);

        $this->designationRepository->delete($designation);

        return redirect($this->tenantUrl->route('designations.index'))->with('status', 'Designation removed.');
    }
}
