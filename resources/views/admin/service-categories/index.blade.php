@extends('layouts.admin')

@section('title', 'Service Categories')

@section('content')
    <div style="margin-bottom:18px">
        <a href="{{ $tenantUrl->route('services.index') }}" class="sfp-action-link">&larr; Back to services</a>
    </div>

    <div class="sfp-page-header">
        <div>
            <h1 class="sfp-page-title">Service categories</h1>
            <p class="sfp-page-subtitle">Manage the categories services can be grouped under.</p>
        </div>
        @can('services.create')
            <a href="{{ $tenantUrl->route('serviceCategories.create') }}" class="sfp-btn-pill-dark">+ Add category</a>
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
                    @can('services.edit')
                        <a href="{{ $tenantUrl->route('serviceCategories.edit', $category) }}" class="sfp-action-link">Edit</a>
                    @endcan
                    @can('services.delete')
                        <form action="{{ $tenantUrl->route('serviceCategories.destroy', $category) }}" method="POST">
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
