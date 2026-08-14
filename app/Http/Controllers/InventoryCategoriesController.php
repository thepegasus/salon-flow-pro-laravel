<?php

namespace App\Http\Controllers;

use App\Http\Requests\Inventory\StoreInventoryCategoryRequest;
use App\Http\Requests\Inventory\UpdateInventoryCategoryRequest;
use App\Models\InventoryCategory;
use App\Repositories\Contracts\InventoryCategoryRepositoryInterface;
use App\Services\InventoryService;
use App\Services\TenantUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryCategoriesController extends Controller
{
    public function __construct(
        private InventoryCategoryRepositoryInterface $categoryRepository,
        private InventoryService $inventoryService,
        private TenantUrl $tenantUrl,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('inventory.view'), 403);

        $categories = $this->categoryRepository->getAll();

        return view('admin.inventory-categories.index', ['categories' => $categories]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->can('inventory.create'), 403);

        return view('admin.inventory-categories.create');
    }

    public function store(StoreInventoryCategoryRequest $request): RedirectResponse
    {
        abort_unless($request->user()->can('inventory.create'), 403);

        $this->inventoryService->createCategory($request->validated());

        return redirect($this->tenantUrl->route('productCategories.index'))->with('status', 'Category created.');
    }

    public function edit(Request $request, string $subdomain, InventoryCategory $category): View
    {
        abort_unless($request->user()->can('inventory.edit'), 403);

        return view('admin.inventory-categories.edit', ['category' => $category]);
    }

    public function update(UpdateInventoryCategoryRequest $request, string $subdomain, InventoryCategory $category): RedirectResponse
    {
        abort_unless($request->user()->can('inventory.edit'), 403);

        $this->inventoryService->updateCategory($category, $request->validated());

        return redirect($this->tenantUrl->route('productCategories.index'))->with('status', 'Category updated.');
    }

    public function destroy(Request $request, string $subdomain, InventoryCategory $category): RedirectResponse
    {
        abort_unless($request->user()->can('inventory.delete'), 403);

        $this->inventoryService->deleteCategory($category);

        return redirect($this->tenantUrl->route('productCategories.index'))->with('status', 'Category removed.');
    }
}
