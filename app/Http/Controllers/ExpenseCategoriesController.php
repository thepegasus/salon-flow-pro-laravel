<?php

namespace App\Http\Controllers;

use App\Http\Requests\Expenses\StoreExpenseCategoryRequest;
use App\Http\Requests\Expenses\UpdateExpenseCategoryRequest;
use App\Models\ExpenseCategory;
use App\Repositories\Contracts\ExpenseCategoryRepositoryInterface;
use App\Services\ExpenseService;
use App\Services\TenantUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseCategoriesController extends Controller
{
    public function __construct(
        private ExpenseCategoryRepositoryInterface $categoryRepository,
        private ExpenseService $expenseService,
        private TenantUrl $tenantUrl,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('expenses.view'), 403);

        $categories = $this->categoryRepository->getAll();

        return view('admin.expense-categories.index', ['categories' => $categories]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->can('expenses.create'), 403);

        return view('admin.expense-categories.create');
    }

    public function store(StoreExpenseCategoryRequest $request): RedirectResponse
    {
        abort_unless($request->user()->can('expenses.create'), 403);

        $this->expenseService->createCategory($request->validated());

        return redirect($this->tenantUrl->route('expenseCategories.index'))->with('status', 'Category created.');
    }

    public function edit(Request $request, string $subdomain, ExpenseCategory $category): View
    {
        abort_unless($request->user()->can('expenses.edit'), 403);

        return view('admin.expense-categories.edit', ['category' => $category]);
    }

    public function update(UpdateExpenseCategoryRequest $request, string $subdomain, ExpenseCategory $category): RedirectResponse
    {
        abort_unless($request->user()->can('expenses.edit'), 403);

        $this->expenseService->updateCategory($category, $request->validated());

        return redirect($this->tenantUrl->route('expenseCategories.index'))->with('status', 'Category updated.');
    }

    public function destroy(Request $request, string $subdomain, ExpenseCategory $category): RedirectResponse
    {
        abort_unless($request->user()->can('expenses.delete'), 403);

        $this->expenseService->deleteCategory($category);

        return redirect($this->tenantUrl->route('expenseCategories.index'))->with('status', 'Category removed.');
    }
}
