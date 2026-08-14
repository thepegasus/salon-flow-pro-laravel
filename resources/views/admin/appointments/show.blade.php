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
            <h1 class="sfp-page-title">{{ $appointment->client->name }} with {{ $appointment->staffProfile->name }}</h1>
            <div class="sfp-page-subtitle sfp-mono">{{ $appointment->start_at->format('d M Y, H:i') }} &ndash; {{ $appointment->end_at->format('H:i') }}</div>
        </div>
        <span class="sfp-pill {{ $statusPillClasses[$appointment->status] ?? 'sfp-pill-neutral' }}">
            {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
        </span>
    </div>

    <div class="sfp-card">
        <div class="sfp-card-title">Details</div>

        <p><strong>Services:</strong> {{ $appointment->services->pluck('name')->join(', ') }}</p>

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
