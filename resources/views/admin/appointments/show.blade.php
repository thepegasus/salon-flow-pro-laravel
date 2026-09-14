@extends('layouts.admin')

@section('title', 'Appointment')

@section('content')
    @php
        $statusPillClasses = [
            'booked' => 'sfp-pill-blue',
            'in_progress' => 'sfp-pill-amber',
            'completed' => 'sfp-pill-green',
            'no_show' => 'sfp-pill-red',
            'cancelled' => 'sfp-pill-neutral',
        ];
    @endphp

    <div class="sfp-page-header">
        <div>
            <h1 class="sfp-page-title">{{ $appointment->client->name }} with {{ $appointment->staffProfiles->pluck('name')->join(', ') }}</h1>
            <div class="sfp-page-subtitle sfp-mono">{{ $appointment->start_at->format('d M Y, H:i') }} &ndash; {{ $appointment->end_at->format('H:i') }}</div>
        </div>
        <span class="sfp-pill {{ $statusPillClasses[$appointment->status] ?? 'sfp-pill-neutral' }}">
            {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
        </span>
    </div>

    <div class="sfp-card">
        <div class="sfp-card-title">Details</div>

        <p><strong>Services:</strong></p>
        <div style="display:grid;gap:8px;margin-bottom:12px">
            @foreach ($appointment->services as $service)
                @php $staffMember = $appointment->staffProfiles->firstWhere('id', $service->pivot->staff_profile_id); @endphp
                <div style="font-size:13.5px">
                    {{ $service->name }}
                    <span class="sfp-mono" style="color:#94A19D;font-size:12.5px">
                        &mdash; {{ \Illuminate\Support\Carbon::parse($service->pivot->start_at)->format('H:i') }}&ndash;{{ \Illuminate\Support\Carbon::parse($service->pivot->end_at)->format('H:i') }}
                        &middot; {{ $staffMember->name ?? 'Unassigned' }}
                    </span>
                </div>
            @endforeach
        </div>

        @if ($appointment->notes)
            <p><strong>Notes:</strong> {{ $appointment->notes }}</p>
        @endif

        @can('appointments.edit')
            <div class="sfp-form-actions" style="margin-top: 18px">
                <form action="{{ $tenantUrl->route('appointments.noShow', $appointment) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="sfp-btn-link-danger">Mark no-show</button>
                </form>

                <form action="{{ $tenantUrl->route('appointments.cancel', $appointment) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="reason" value="client_requested">
                    <button type="submit" class="sfp-btn-outline">Cancel</button>
                </form>
            </div>
        @endcan
    </div>

    <div class="sfp-card" style="margin-top: 16px">
        <div class="sfp-card-title">History</div>
        @foreach ($appointment->statusHistories()->latest()->get() as $history)
            <p style="margin: 0 0 10px; font-size: 13.5px; color: #66736F">{{ $history->from_status }} &rarr; {{ $history->to_status }} ({{ $history->reason ?? 'no reason given' }})</p>
        @endforeach
    </div>
@endsection
