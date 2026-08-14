<?php

namespace App\Http\Controllers;

use App\Http\Requests\Inventory\StoreProductRequest;
use App\Http\Requests\Inventory\UpdateProductRequest;
use App\Models\Product;
use App\Repositories\Contracts\InventoryCategoryRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\InventoryService;
use App\Services\TenantUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductsController extends Controller
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private InventoryCategoryRepositoryInterface $categoryRepository,
        private InventoryService $inventoryService,
        private TenantUrl $tenantUrl,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('inventory.view'), 403);

        $products = $this->productRepository->getAll();

        return view('admin.products.index', ['products' => $products]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->can('inventory.create'), 403);

        $categories = $this->categoryRepository->getActive();

        return view('admin.products.create', ['categories' => $categories]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        abort_unless($request->user()->can('inventory.create'), 403);

        $product = $this->inventoryService->createProduct($request->validated());

        return redirect($this->tenantUrl->route('products.show', ['product' => $product]))->with('status', 'Product created.');
    }

    public function show(Request $request, string $subdomain, Product $product): View
    {
        abort_unless($request->user()->can('inventory.view'), 403);

        return view('admin.products.show', ['product' => $product]);
    }

    public function edit(Request $request, string $subdomain, Product $product): View
    {
        abort_unless($request->user()->can('inventory.edit'), 403);

        $categories = $this->categoryRepository->getActive();

        return view('admin.products.edit', ['product' => $product, 'categories' => $categories]);
    }

    public function update(UpdateProductRequest $request, string $subdomain, Product $product): RedirectResponse
    {
        abort_unless($request->user()->can('inventory.edit'), 403);

        $this->inventoryService->updateProduct($product, $request->validated());

        return redirect($this->tenantUrl->route('products.show', ['product' => $product]))->with('status', 'Product updated.');
    }

    public function destroy(Request $request, string $subdomain, Product $product): RedirectResponse
    {
        abort_unless($request->user()->can('inventory.delete'), 403);

        $this->inventoryService->deleteProduct($product);

        return redirect($this->tenantUrl->route('products.index'))->with('status', 'Product removed.');
    }
}
