@extends('layouts.admin')

@section('title', 'Commission Earnings')

@section('content')
    <div class="sfp-page-header">
        <div>
            <h1 class="sfp-page-title">Commission earnings</h1>
            <p class="sfp-page-subtitle">Computed from paid bills for {{ $from->format('d M Y') }} &ndash; {{ $to->format('d M Y') }}.</p>
        </div>
        <div class="sfp-row">
            @can('commissions.create')
                <a href="{{ $tenantUrl->route('commissionRates.index') }}" class="sfp-btn-outline">Manage rates</a>
                <a href="{{ $tenantUrl->route('staffIncentives.create') }}" class="sfp-btn-pill-dark">+ Award incentive</a>
            @endcan
        </div>
    </div>

    <div class="sfp-card">
        <form method="GET" class="sfp-split-2" style="align-items:end">
            <div class="sfp-field">
                <label class="sfp-label">From</label>
                <input type="date" name="from" class="sfp-input" value="{{ $from->format('Y-m-d') }}">
            </div>
            <div class="sfp-field">
                <label class="sfp-label">To</label>
                <input type="date" name="to" class="sfp-input" value="{{ $to->format('Y-m-d') }}">
            </div>
            @if ($selectedStaffId === null)
                <div class="sfp-field" style="grid-column:span 2">
                    <label class="sfp-label">Staff member</label>
                    <select name="staff_profile_id" class="sfp-select">
                        <option value="">All staff</option>
                        @foreach ($staffList as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="sfp-form-actions" style="grid-column:span 2">
                <button type="submit" class="sfp-btn-primary">Apply</button>
            </div>
        </form>
    </div>

    <div class="sfp-table-wrap">
        <div class="sfp-table-head-row" style="grid-template-columns:1fr 140px 140px 140px 90px">
            <span>Staff</span>
            <span>Commission</span>
            <span>Incentives</span>
            <span>Total</span>
            <span>Line items</span>
        </div>

        @forelse ($earnings as $row)
            <div class="sfp-table-row" style="grid-template-columns:1fr 140px 140px 140px 90px">
                <span>{{ $row['staff']->name }}</span>
                <span class="sfp-mono">&#8377;{{ number_format((float) $row['commissionEarned'], 2) }}</span>
                <span class="sfp-mono">&#8377;{{ number_format((float) $row['incentivesEarned'], 2) }}</span>
                <span class="sfp-mono" style="font-weight:600">&#8377;{{ number_format((float) $row['totalEarned'], 2) }}</span>
                <span class="sfp-mono">{{ $row['lineItemCount'] }}</span>
            </div>
        @empty
            <div class="sfp-table-row">
                <span style="color:#66736F">No earnings for this period.</span>
            </div>
        @endforelse
    </div>
@endsection
