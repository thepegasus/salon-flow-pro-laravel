@extends('layouts.admin')

@section('title', 'Engagement')

@section('content')
    @php
        $statusPills = [
            \App\Models\BridalEngagement::StatusPlanned => 'sfp-pill-blue',
            \App\Models\BridalEngagement::StatusTrialCompleted => 'sfp-pill-amber',
            \App\Models\BridalEngagement::StatusCompleted => 'sfp-pill-green',
            \App\Models\BridalEngagement::StatusCancelled => 'sfp-pill-neutral',
        ];
        $trial = $engagement->trialAppointment();
        $eventDay = $engagement->eventDayAppointment();
        $pillClass = $statusPills[$engagement->status] ?? 'sfp-pill-neutral';
    @endphp

    <div class="sfp-page-header">
        <div>
            <h1 class="sfp-page-title">{{ $engagement->client->name }}</h1>
            <p class="sfp-page-subtitle">{{ $engagement->event_date->format('d M Y') }}@if ($engagement->venue) &middot; {{ $engagement->venue }} @endif</p>
        </div>
        <a href="{{ $tenantUrl->route('bridalEngagements.index') }}" class="sfp-btn-outline">Back to engagements</a>
    </div>

    <div class="sfp-card">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:18px">
            <div>
                <div class="sfp-heading" style="font-weight:600;font-size:23px;letter-spacing:-.01em">{{ $engagement->client->name }}</div>
                <div class="sfp-mono" style="font-size:11px;color:#94A19D;margin-top:4px">Engagement #{{ $engagement->id }}</div>
            </div>
            <div style="display:flex;align-items:center;gap:10px">
                @if ($eventDay && $eventDay->is_on_location)
                    <span class="sfp-pill sfp-pill-sage">On-location</span>
                @endif
                <span class="sfp-pill {{ $pillClass }}">{{ ucfirst(str_replace('_', ' ', $engagement->status)) }}</span>
            </div>
        </div>

        <div style="font-size:12.5px;color:#66736F;margin-bottom:16px">
            Traveling staff: {{ $engagement->travelingStaff->pluck('user.name')->join(', ') ?: 'None assigned' }}
        </div>

        @if ($engagement->notes)
            <div style="font-size:13px;color:#16201D;margin-bottom:16px">
                <strong>Notes:</strong> {{ $engagement->notes }}
            </div>
        @endif

        <h2 class="sfp-card-title">Linked appointments</h2>

        <div style="display:grid;gap:8px">
            <div style="display:flex;gap:12px;padding:15px 17px;border-radius:13px;background:#F3F6F5;border:1px solid #F0E7E1">
                <span class="sfp-mono" style="font-size:10px;color:#2E5F4C;width:48px;flex:none;padding-top:3px">TRIAL</span>
                <div style="font-size:13.5px;line-height:1.6">
                    @if ($trial)
                        <div>{{ $trial->start_at->format('d M Y, H:i') }}</div>
                        <div style="color:#66736F">Staff: {{ $trial->staffProfile->user->name }}</div>
                        <div style="color:#66736F">
                            {{ $trial->is_on_location ? 'On-location'.($trial->venue_address ? ' — '.$trial->venue_address : '') : 'In-studio' }}
                        </div>
                        <span class="sfp-pill sfp-pill-neutral" style="margin-top:4px">{{ ucfirst(str_replace('_', ' ', $trial->status)) }}</span>
                    @else
                        <span style="color:#94A19D">Not scheduled</span>
                    @endif
                </div>
            </div>

            <div style="display:flex;gap:12px;padding:15px 17px;border-radius:13px;background:#E2EDE7;border:1px solid #D8E8E0">
                <span class="sfp-mono" style="font-size:10px;color:#2E5F4C;width:48px;flex:none;padding-top:3px">EVENT</span>
                <div style="font-size:13.5px;line-height:1.6">
                    @if ($eventDay)
                        <div>{{ $eventDay->start_at->format('d M Y, H:i') }}</div>
                        <div style="color:#66736F">Staff: {{ $eventDay->staffProfile->user->name }}</div>
                        <div style="color:#66736F">
                            {{ $eventDay->is_on_location ? 'On-location'.($eventDay->venue_address ? ' — '.$eventDay->venue_address : '') : 'In-studio' }}
                        </div>
                        <span class="sfp-pill sfp-pill-neutral" style="margin-top:4px">{{ ucfirst(str_replace('_', ' ', $eventDay->status)) }}</span>
                    @else
                        <span style="color:#94A19D">Not scheduled</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
