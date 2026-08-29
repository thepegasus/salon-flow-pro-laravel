<?php

namespace App\Http\Controllers;

use App\Models\StaffProfile;
use App\Services\StaffService;
use App\Services\TenantUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class StaffLoginController extends Controller
{
    public function __construct(
        private StaffService $staffService,
        private TenantUrl $tenantUrl,
    ) {}

    public function disable(Request $request, string $subdomain, StaffProfile $staff): RedirectResponse
    {
        abort_unless($request->user()->can('staff.edit'), 403);

        try {
            $this->staffService->disableLogin($staff);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['login' => $exception->getMessage()]);
        }

        return redirect($this->tenantUrl->route('staff.index'))->with('status', 'Login disabled.');
    }

    public function enable(Request $request, string $subdomain, StaffProfile $staff): RedirectResponse
    {
        abort_unless($request->user()->can('staff.edit'), 403);

        try {
            $this->staffService->enableLogin($staff);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['login' => $exception->getMessage()]);
        }

        return redirect($this->tenantUrl->route('staff.index'))->with('status', 'Login enabled.');
    }
}
