<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceCategories\StoreServiceCategoryRequest;
use App\Http\Requests\ServiceCategories\UpdateServiceCategoryRequest;
use App\Models\ServiceCategory;
use App\Repositories\Contracts\ServiceCategoryRepositoryInterface;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceCategoriesController extends Controller
{
    public function __construct(
        private ServiceCategoryRepositoryInterface $categoryRepository,
        private TenantContext $tenantContext,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('services.view'), 403);

        $categories = $this->categoryRepository->getAll();

        return view('admin.service-categories.index', ['categories' => $categories]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->can('services.create'), 403);

        return view('admin.service-categories.create');
    }

    public function store(StoreServiceCategoryRequest $request): RedirectResponse
    {
        abort_unless($request->user()->can('services.create'), 403);

        $this->categoryRepository->create([
            ...$request->validated(),
            'tenant_id' => $this->tenantContext->get()->id,
        ]);

        return redirect()->route('serviceCategories.index')->with('status', 'Category created.');
    }

    public function edit(Request $request, string $subdomain, ServiceCategory $category): View
    {
        abort_unless($request->user()->can('services.edit'), 403);

        return view('admin.service-categories.edit', ['category' => $category]);
    }

    public function update(UpdateServiceCategoryRequest $request, string $subdomain, ServiceCategory $category): RedirectResponse
    {
        abort_unless($request->user()->can('services.edit'), 403);

        $this->categoryRepository->update($category, $request->validated());

        return redirect()->route('serviceCategories.index')->with('status', 'Category updated.');
    }

    public function destroy(Request $request, string $subdomain, ServiceCategory $category): RedirectResponse
    {
        abort_unless($request->user()->can('services.delete'), 403);

        $this->categoryRepository->delete($category);

        return redirect()->route('serviceCategories.index')->with('status', 'Category removed.');
    }
}
