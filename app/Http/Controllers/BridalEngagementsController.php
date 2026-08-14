<?php

namespace App\Http\Controllers;

use App\Exceptions\StaffUnavailableException;
use App\Http\Requests\BridalEngagements\StoreBridalEngagementRequest;
use App\Models\BridalEngagement;
use App\Repositories\Contracts\BridalEngagementRepositoryInterface;
use App\Services\BridalEngagementService;
use App\Services\TenantUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use InvalidArgumentException;

class BridalEngagementsController extends Controller
{
    public function __construct(
        private BridalEngagementRepositoryInterface $bridalEngagementRepository,
        private BridalEngagementService $bridalEngagementService,
        private TenantUrl $tenantUrl,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('appointments.view'), 403);

        $engagements = $this->bridalEngagementRepository->getUpcoming();

        return view('admin.bridal-engagements.index', ['engagements' => $engagements]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->can('appointments.create'), 403);

        return view('admin.bridal-engagements.create');
    }

    public function store(StoreBridalEngagementRequest $request): RedirectResponse
    {
        abort_unless($request->user()->can('appointments.create'), 403);

        $data = $request->validated();

        try {
            $engagement = $this->bridalEngagementService->createEngagement(
                clientId: $data['client_id'],
                eventDate: Carbon::parse($data['event_date']),
                venue: $data['venue'] ?? null,
                trialStaffProfileId: $data['trial_staff_profile_id'],
                trialStartAt: Carbon::parse($data['trial_start_at']),
                trialLineItems: $data['trial_services'],
                eventStaffProfileId: $data['event_staff_profile_id'],
                eventStartAt: Carbon::parse($data['event_start_at']),
                eventLineItems: $data['event_services'],
                travelingStaffProfileIds: $data['traveling_staff_profile_ids'] ?? [],
                eventIsOnLocation: $data['event_is_on_location'] ?? true,
            );
        } catch (StaffUnavailableException|InvalidArgumentException $exception) {
            return back()->withErrors(['trial_staff_profile_id' => $exception->getMessage()])->withInput();
        }

        return redirect($this->tenantUrl->route('bridalEngagements.show', ['bridalEngagement' => $engagement]))->with('status', 'Bridal engagement created.');
    }

    public function show(Request $request, string $subdomain, BridalEngagement $bridalEngagement): View
    {
        abort_unless($request->user()->can('appointments.view'), 403);

        $bridalEngagement->load(['client', 'appointments.staffProfile', 'travelingStaff']);

        return view('admin.bridal-engagements.show', ['engagement' => $bridalEngagement]);
    }
}
