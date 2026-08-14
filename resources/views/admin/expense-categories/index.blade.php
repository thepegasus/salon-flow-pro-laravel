@extends('layouts.admin')

@section('title', 'Expense Categories')

@section('content')
    <div style="margin-bottom:18px">
        <a href="{{ $tenantUrl->route('expenses.index') }}" class="sfp-action-link">&larr; Back to expenses</a>
    </div>

    <div class="sfp-page-header">
        <div>
            <h1 class="sfp-page-title">Expense categories</h1>
            <p class="sfp-page-subtitle">Manage the categories expenses can be grouped under.</p>
        </div>
        @can('expenses.create')
            <a href="{{ $tenantUrl->route('expenseCategories.create') }}" class="sfp-btn-pill-dark">+ Add category</a>
        @endcan
    </div>

    <div class="sfp-table-wrap">
        <div class="sfp-table-head-row" style="grid-template-columns:1fr 120px 140px">
            <span>Name</span>
            <span>Status</span>
            <span></span>
        </div>

        @forelse ($categories as $category)
            <div class="sfp-table-row" style="grid-template-columns:1fr 120px 140px">
                <span style="font-size:14.5px">{{ $category->name }}</span>
                <span>
                    @if ($category->is_active)
                        <span class="sfp-pill sfp-pill-green">Active</span>
                    @else
                        <span class="sfp-pill sfp-pill-neutral">Disabled</span>
                    @endif
                </span>
                <div style="display:flex;align-items:center;justify-content:flex-end;gap:12px">
                    @can('expenses.edit')
                        <a href="{{ $tenantUrl->route('expenseCategories.edit', $category) }}" class="sfp-action-link">Edit</a>
                    @endcan
                    @can('expenses.delete')
                        <form action="{{ $tenantUrl->route('expenseCategories.destroy', $category) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="sfp-btn-link-danger">Remove</button>
                        </form>
                    @endcan
                </div>
            </div>
        @empty
            <div class="sfp-table-row" style="grid-template-columns:1fr">
                <p style="color:#66736F;margin:0">No categories yet.</p>
            </div>
        @endforelse
    </div>
@endsection
