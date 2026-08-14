@extends('layouts.admin')

@section('title', 'Designations')

@section('content')
    <div style="margin-bottom:18px">
        <a href="{{ $tenantUrl->route('staff.index') }}" class="sfp-action-link">&larr; Back to staff</a>
    </div>

    <div class="sfp-page-header">
        <div>
            <h1 class="sfp-page-title">Designations</h1>
            <p class="sfp-page-subtitle">Manage the job titles staff can be assigned.</p>
        </div>
        @can('staff.create')
            <a href="{{ $tenantUrl->route('designations.create') }}" class="sfp-btn-pill-dark">+ Add designation</a>
        @endcan
    </div>

    <div class="sfp-table-wrap">
        <div class="sfp-table-head-row" style="grid-template-columns:1fr 120px 140px">
            <span>Name</span>
            <span>Status</span>
            <span></span>
        </div>

        @forelse ($designations as $designation)
            <div class="sfp-table-row" style="grid-template-columns:1fr 120px 140px">
                <span style="font-size:14.5px">{{ $designation->name }}</span>
                <span>
                    @if ($designation->is_active)
                        <span class="sfp-pill sfp-pill-green">Active</span>
                    @else
                        <span class="sfp-pill sfp-pill-neutral">Disabled</span>
                    @endif
                </span>
                <div style="display:flex;align-items:center;justify-content:flex-end;gap:12px">
                    @can('staff.edit')
                        <a href="{{ $tenantUrl->route('designations.edit', $designation) }}" class="sfp-action-link">Edit</a>
                    @endcan
                    @can('staff.delete')
                        <form action="{{ $tenantUrl->route('designations.destroy', $designation) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="sfp-btn-link-danger">Remove</button>
                        </form>
                    @endcan
                </div>
            </div>
        @empty
            <div class="sfp-table-row" style="grid-template-columns:1fr">
                <p style="color:#66736F;margin:0">No designations yet.</p>
            </div>
        @endforelse
    </div>
@endsection
