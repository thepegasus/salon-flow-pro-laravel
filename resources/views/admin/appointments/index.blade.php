@extends('layouts.admin')

@section('title', 'Appointments')

@section('content')
    <div class="sfp-page-header">
        <div>
            <h1 class="sfp-page-title">{{ $date->format('D, j M') }}</h1>
            <div class="sfp-page-subtitle">{{ $appointments->count() }} booked &middot; {{ $date->format('d M Y') }}</div>
        </div>
        <div class="sfp-row">
            <form method="GET">
                <input type="date" name="date" value="{{ $date->toDateString() }}" class="sfp-input" style="max-width: 170px; margin-bottom: 0" onchange="this.form.submit()">
            </form>
            @can('appointments.create')
                <a href="{{ $tenantUrl->route('appointments.create') }}" class="sfp-btn-primary">+ New appointment</a>
            @endcan
        </div>
    </div>

    @php
        $statusPillClasses = [
            'booked' => 'sfp-pill-blue',
            'in_progress' => 'sfp-pill-amber',
            'completed' => 'sfp-pill-green',
            'no_show' => 'sfp-pill-red',
            'cancelled' => 'sfp-pill-neutral',
        ];
        $statusLegend = [
            'booked' => ['label' => 'Booked', 'color' => '#1B4B8F'],
            'in_progress' => ['label' => 'In progress', 'color' => '#8A5A1B'],
            'completed' => ['label' => 'Completed', 'color' => '#2F6849'],
            'no_show' => ['label' => 'No-show', 'color' => '#A8506B'],
            'cancelled' => ['label' => 'Cancelled', 'color' => '#788582'],
        ];
    @endphp

    <div class="sfp-row-wrap" style="margin-bottom: 14px">
        @foreach ($statusLegend as $legend)
            <div class="sfp-row" style="gap: 7px">
                <span style="width:10px;height:10px;border-radius:3px;background:{{ $legend['color'] }};display:inline-block"></span>
                <span style="font-size:12.5px;color:#66736F">{{ $legend['label'] }}</span>
            </div>
        @endforeach
    </div>

    <div class="sfp-table-wrap">
        <div class="sfp-table-head-row" style="grid-template-columns: 110px 1.4fr 1.2fr 1.6fr 130px 70px">
            <div>Time</div>
            <div>Client</div>
            <div>Staff</div>
            <div>Services</div>
            <div>Status</div>
            <div></div>
        </div>

        @foreach ($appointments as $appointment)
            <div class="sfp-table-row" style="grid-template-columns: 110px 1.4fr 1.2fr 1.6fr 130px 70px">
                <div class="sfp-mono">{{ $appointment->start_at->format('H:i') }}&ndash;{{ $appointment->end_at->format('H:i') }}</div>
                <div>{{ $appointment->client->name }}</div>
                <div>{{ $appointment->staffProfile->name }}</div>
                <div>{{ $appointment->services->pluck('name')->join(', ') }}</div>
                <div>
                    <span class="sfp-pill {{ $statusPillClasses[$appointment->status] ?? 'sfp-pill-neutral' }}">
                        {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
                    </span>
                </div>
                <div>
                    <a href="{{ $tenantUrl->route('appointments.show', $appointment) }}" class="sfp-action-link">View</a>
                </div>
            </div>
        @endforeach
    </div>
@endsection
