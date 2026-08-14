<?php

namespace App\Http\Controllers;

use App\Http\Requests\Services\StoreServiceRequest;
use App\Http\Requests\Services\UpdateServiceRequest;
use App\Models\Service;
use App\Repositories\Contracts\ServiceCategoryRepositoryInterface;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use App\Services\ServiceCatalogService;
use App\Services\TenantUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServicesController extends Controller
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepository,
        private ServiceCategoryRepositoryInterface $categoryRepository,
        private ServiceCatalogService $serviceCatalogService,
        private TenantUrl $tenantUrl,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('services.view'), 403);

        $services = $this->serviceRepository->getActive();

        return view('admin.services.index', ['services' => $services]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->can('services.create'), 403);

        $categories = $this->categoryRepository->getActive();

        return view('admin.services.create', ['categories' => $categories]);
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        abort_unless($request->user()->can('services.create'), 403);

        $service = $this->serviceCatalogService->create($request->validated(), $request->user()->id);

        return redirect($this->tenantUrl->route('services.show', ['service' => $service]))->with('status', 'Service created.');
    }

    public function show(Request $request, string $subdomain, Service $service): View
    {
        abort_unless($request->user()->can('services.view'), 403);

        return view('admin.services.show', ['service' => $service]);
    }

    public function edit(Request $request, string $subdomain, Service $service): View
    {
        abort_unless($request->user()->can('services.edit'), 403);

        $categories = $this->categoryRepository->getActive();

        return view('admin.services.edit', ['service' => $service, 'categories' => $categories]);
    }

    public function update(UpdateServiceRequest $request, string $subdomain, Service $service): RedirectResponse
    {
        abort_unless($request->user()->can('services.edit'), 403);

        $this->serviceCatalogService->update($service, $request->validated(), $request->user()->id);

        return redirect($this->tenantUrl->route('services.show', ['service' => $service]))->with('status', 'Service updated.');
    }

    public function destroy(Request $request, string $subdomain, Service $service): RedirectResponse
    {
        abort_unless($request->user()->can('services.delete'), 403);

        $this->serviceCatalogService->deactivate($service);

        return redirect($this->tenantUrl->route('services.index'))->with('status', 'Service disabled.');
    }
}
