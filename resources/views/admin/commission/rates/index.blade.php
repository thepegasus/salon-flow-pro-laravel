@extends('layouts.admin')

@section('title', 'Commission Rates')

@section('content')
    <div class="sfp-page-header">
        <div>
            <h1 class="sfp-page-title">Commission rates</h1>
            <p class="sfp-page-subtitle">Rates apply to paid work only. The most specific rate for a staff member and category wins.</p>
        </div>
        <div class="sfp-row">
            @can('commissions.view')
                <a href="{{ $tenantUrl->route('commissionEarnings.index') }}" class="sfp-btn-outline">View earnings</a>
            @endcan
            @can('commissions.create')
                <a href="{{ $tenantUrl->route('commissionRates.create') }}" class="sfp-btn-pill-dark">+ Add rate</a>
            @endcan
        </div>
    </div>

    <div class="sfp-table-wrap">
        <div class="sfp-table-head-row" style="grid-template-columns:1fr 1fr 90px 120px 140px">
            <span>Staff</span>
            <span>Category</span>
            <span>Rate</span>
            <span>Effective from</span>
            <span></span>
        </div>

        @forelse ($rates as $rate)
            <div class="sfp-table-row" style="grid-template-columns:1fr 1fr 90px 120px 140px">
                <span>{{ $rate->staffProfile?->user?->name ?? 'All staff' }}</span>
                <span>{{ $rate->serviceCategory?->name ?? 'All categories' }}</span>
                <span class="sfp-mono">{{ number_format($rate->rate_percent, 2) }}%</span>
                <span class="sfp-mono">{{ $rate->effective_from->format('d M Y') }}</span>
                <div style="display:flex;align-items:center;justify-content:flex-end;gap:12px">
                    @can('commissions.edit')
                        <a href="{{ $tenantUrl->route('commissionRates.edit', $rate) }}" style="font-size:12.5px;color:#1B4B8F">Edit</a>
                    @endcan
                    @can('commissions.delete')
                        <form action="{{ $tenantUrl->route('commissionRates.destroy', $rate) }}" method="POST" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="sfp-btn-link-danger">Remove</button>
                        </form>
                    @endcan
                </div>
            </div>
        @empty
            <div class="sfp-table-row">
                <span style="color:#66736F">No commission rates configured yet.</span>
            </div>
        @endforelse
    </div>
@endsection
