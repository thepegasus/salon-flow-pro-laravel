@extends('layouts.admin')

@section('title', 'Time Slots')

@section('content')
    <div style="margin-bottom:18px">
        <a href="{{ $tenantUrl->route('appointments.create') }}" class="sfp-action-link">&larr; Back to new appointment</a>
    </div>

    <div class="sfp-page-header">
        <div>
            <h1 class="sfp-page-title">Time slots</h1>
            <p class="sfp-page-subtitle">The slots staff appear when booking an appointment.</p>
        </div>
        @can('appointments.create')
            <a href="{{ $tenantUrl->route('timeSlots.create') }}" class="sfp-btn-pill-dark">+ Add time slot</a>
        @endcan
    </div>

    <div class="sfp-table-wrap">
        <div class="sfp-table-head-row" style="grid-template-columns:1fr 120px 140px">
            <span>Slot</span>
            <span>Status</span>
            <span></span>
        </div>

        @forelse ($timeSlots as $slot)
            <div class="sfp-table-row" style="grid-template-columns:1fr 120px 140px">
                <span class="sfp-mono" style="font-size:14.5px">{{ $slot->label() }}</span>
                <span>
                    @if ($slot->is_active)
                        <span class="sfp-pill sfp-pill-green">Active</span>
                    @else
                        <span class="sfp-pill sfp-pill-neutral">Disabled</span>
                    @endif
                </span>
                <div style="display:flex;align-items:center;justify-content:flex-end;gap:12px">
                    @can('appointments.edit')
                        <a href="{{ $tenantUrl->route('timeSlots.edit', $slot) }}" class="sfp-action-link">Edit</a>
                    @endcan
                    @can('appointments.delete')
                        <form action="{{ $tenantUrl->route('timeSlots.destroy', $slot) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="sfp-btn-link-danger">Remove</button>
                        </form>
                    @endcan
                </div>
            </div>
        @empty
            <div class="sfp-table-row" style="grid-template-columns:1fr">
                <p style="color:#66736F;margin:0">No time slots yet.</p>
            </div>
        @endforelse
    </div>
@endsection
