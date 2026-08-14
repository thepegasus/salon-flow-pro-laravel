@extends('layouts.admin')

@section('title', 'Staff')

@section('content')
    <div class="sfp-page-header">
        <div>
            <h1 class="sfp-page-title">Staff &amp; roster</h1>
            <p class="sfp-page-subtitle">Approved leave is blocked from booking automatically</p>
        </div>
        @can('staff.create')
            <a href="{{ $tenantUrl->route('staff.create') }}" class="sfp-btn-pill-dark">+ Add staff</a>
        @endcan
    </div>

    <div class="sfp-table-wrap">
        <div class="sfp-table-head-row" style="grid-template-columns: 2fr 1.5fr 1.5fr 1fr 1.5fr;">
            <div>Name</div>
            <div>Job Title</div>
            <div>Phone</div>
            <div>Status</div>
            <div></div>
        </div>

        @foreach ($staff as $member)
            <div class="sfp-table-row" style="grid-template-columns: 2fr 1.5fr 1.5fr 1fr 1.5fr;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div class="sfp-avatar-chip">{{ strtoupper(substr($member->user->name, 0, 1)) }}</div>
                    <span>{{ $member->user->name }}</span>
                </div>
                <div>{{ $member->job_title }}</div>
                <div>{{ $member->phone }}</div>
                <div>
                    @if ($member->is_active)
                        <span class="sfp-pill sfp-pill-green">Active</span>
                    @else
                        <span class="sfp-pill sfp-pill-neutral">Inactive</span>
                    @endif
                </div>
                <div style="display: flex; gap: 12px; align-items: center;">
                    <a href="{{ $tenantUrl->route('staff.show', $member) }}" class="sfp-btn-outline">View</a>
                    @can('staff.edit')
                        <a href="{{ $tenantUrl->route('staff.edit', $member) }}" class="sfp-btn-outline">Edit</a>
                    @endcan
                    @can('staff.delete')
                        <form action="{{ $tenantUrl->route('staff.destroy', $member) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="sfp-btn-link-danger">Remove</button>
                        </form>
                    @endcan
                </div>
            </div>
        @endforeach
    </div>
@endsection
