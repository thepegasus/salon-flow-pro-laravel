@extends('layouts.admin')

@section('title', $staff->name)

@section('content')
    <div class="sfp-page-header">
        <div>
            <h1 class="sfp-page-title">{{ $staff->name }}</h1>
            <p class="sfp-page-subtitle">{{ $staff->designation?->name ?? 'No designation set' }}</p>
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
            <div class="sfp-avatar-chip" style="width: 48px; height: 48px; font-size: 20px;">{{ strtoupper(substr($staff->name, 0, 1)) }}</div>
            <div>
                <div style="font-size: 17px; font-weight: 600;">{{ $staff->name }}</div>
                <div class="sfp-page-subtitle" style="margin-top: 2px;">{{ $staff->designation?->name ?? 'No designation set' }}</div>
            </div>
            @if ($staff->is_active)
                <span class="sfp-pill sfp-pill-green">Active</span>
            @else
                <span class="sfp-pill sfp-pill-neutral">Inactive</span>
            @endif
            @if ($staff->hasLogin())
                <span class="sfp-pill sfp-pill-blue">Login: {{ $staff->user->username }}</span>
            @else
                <span class="sfp-pill sfp-pill-neutral">No login</span>
            @endif
        </div>

        <p style="color: #66736F; font-size: 13.5px;">{{ $staff->phone }} @if($staff->email) &middot; {{ $staff->email }} @endif</p>

        @if ($staff->hasLogin())
            <div class="sfp-card-title" style="margin-top: 20px;">Roles</div>
            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                @foreach ($staff->user->getRoleNames() as $role)
                    <span class="sfp-pill sfp-pill-purple">{{ $role }}</span>
                @endforeach
            </div>
        @endif

        <div class="sfp-card-title" style="margin-top: 20px;">Services</div>
        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
            @foreach ($staff->services as $service)
                <span class="sfp-pill sfp-pill-sage">{{ $service->name }}</span>
            @endforeach
        </div>

        @if ($staff->employee_code || $staff->date_of_joining || $staff->employment_type || $staff->reportingManager)
            <div class="sfp-card-title" style="margin-top: 20px;">Employment</div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; font-size: 13.5px; color: #66736F;">
                @if ($staff->employee_code)
                    <div><strong>Employee code:</strong> {{ $staff->employee_code }}</div>
                @endif
                @if ($staff->date_of_joining)
                    <div><strong>Joined:</strong> {{ $staff->date_of_joining->format('d M Y') }}</div>
                @endif
                @if ($staff->employment_type)
                    <div><strong>Type:</strong> {{ $staff->employment_type }}</div>
                @endif
                @if ($staff->reportingManager)
                    <div><strong>Reports to:</strong> {{ $staff->reportingManager->name }}</div>
                @endif
            </div>
        @endif

        @if ($staff->date_of_birth || $staff->gender || $staff->address || $staff->emergency_contact_name)
            <div class="sfp-card-title" style="margin-top: 20px;">Personal</div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; font-size: 13.5px; color: #66736F;">
                @if ($staff->date_of_birth)
                    <div><strong>Date of birth:</strong> {{ $staff->date_of_birth->format('d M Y') }}</div>
                @endif
                @if ($staff->gender)
                    <div><strong>Gender:</strong> {{ $staff->gender }}</div>
                @endif
                @if ($staff->address)
                    <div><strong>Address:</strong> {{ $staff->address }}</div>
                @endif
                @if ($staff->emergency_contact_name)
                    <div><strong>Emergency contact:</strong> {{ $staff->emergency_contact_name }} {{ $staff->emergency_contact_phone }}</div>
                @endif
            </div>
        @endif

        @if ($staff->base_salary || $staff->bank_account_number || $staff->government_id_number)
            <div class="sfp-card-title" style="margin-top: 20px;">Compensation &amp; documents</div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; font-size: 13.5px; color: #66736F;">
                @if ($staff->base_salary)
                    <div><strong>Base salary:</strong> &#8377;{{ number_format((float) $staff->base_salary, 2) }}</div>
                @endif
                @if ($staff->bank_account_number)
                    <div><strong>Bank account:</strong> {{ $staff->bank_account_number }} ({{ $staff->bank_ifsc }})</div>
                @endif
                @if ($staff->government_id_number)
                    <div><strong>Government ID:</strong> {{ $staff->government_id_number }}</div>
                @endif
            </div>
        @endif
    </div>
@endsection
