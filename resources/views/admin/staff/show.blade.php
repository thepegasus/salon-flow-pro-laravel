@extends('layouts.admin')

@section('title', $staff->user->name)

@section('content')
    <div class="sfp-page-header">
        <div>
            <h1 class="sfp-page-title">{{ $staff->user->name }}</h1>
            <p class="sfp-page-subtitle">{{ $staff->job_title }}</p>
        </div>
        <div class="sfp-form-actions">
            @can('staff.edit')
                <a href="{{ $tenantUrl->route('staff.edit', $staff) }}" class="sfp-btn-outline">Edit</a>
            @endcan
            @can('staff.delete')
                <form action="{{ $tenantUrl->route('staff.destroy', $staff) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="sfp-btn-link-danger">Remove</button>
                </form>
            @endcan
        </div>
    </div>

    <div class="sfp-card">
        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px;">
            <div class="sfp-avatar-chip" style="width: 48px; height: 48px; font-size: 20px;">{{ strtoupper(substr($staff->user->name, 0, 1)) }}</div>
            <div>
                <div style="font-size: 17px; font-weight: 600;">{{ $staff->user->name }}</div>
                <div class="sfp-page-subtitle" style="margin-top: 2px;">{{ $staff->job_title }}</div>
            </div>
            @if ($staff->is_active)
                <span class="sfp-pill sfp-pill-green">Active</span>
            @else
                <span class="sfp-pill sfp-pill-neutral">Inactive</span>
            @endif
        </div>

        <p style="color: #66736F; font-size: 13.5px;">{{ $staff->phone }}</p>

        <div class="sfp-card-title" style="margin-top: 20px;">Services</div>
        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
            @foreach ($staff->services as $service)
                <span class="sfp-pill sfp-pill-sage">{{ $service->name }}</span>
            @endforeach
        </div>
    </div>
@endsection
