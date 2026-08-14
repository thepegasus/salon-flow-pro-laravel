@extends('layouts.admin')

@section('title', 'Bridal & On-Site Events')

@section('content')
    <div class="sfp-page-header">
        <div>
            <h1 class="sfp-page-title">Bridal &amp; events</h1>
            <p class="sfp-page-subtitle">{{ $engagements->count() }} live engagements</p>
        </div>
        @can('appointments.create')
            <a href="{{ $tenantUrl->route('bridalEngagements.create') }}" class="sfp-btn-pill-dark">+ New engagement</a>
        @endcan
    </div>

    @php
        $statusPills = [
            \App\Models\BridalEngagement::StatusPlanned => 'sfp-pill-blue',
            \App\Models\BridalEngagement::StatusTrialCompleted => 'sfp-pill-amber',
            \App\Models\BridalEngagement::StatusCompleted => 'sfp-pill-green',
            \App\Models\BridalEngagement::StatusCancelled => 'sfp-pill-neutral',
        ];
    @endphp

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:12px">
        @forelse ($engagements as $engagement)
            @php
                $trial = $engagement->trialAppointment();
                $eventDay = $engagement->eventDayAppointment();
                $pillClass = $statusPills[$engagement->status] ?? 'sfp-pill-neutral';
            @endphp
            <div class="sfp-card">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:18px">
                    <div>
                        <div class="sfp-heading" style="font-weight:600;font-size:23px;letter-spacing:-.01em">{{ $engagement->client->name }}</div>
                        <div class="sfp-mono" style="font-size:11px;color:#94A19D;margin-top:4px">Engagement #{{ $engagement->id }} &middot; {{ $engagement->event_date->format('d M Y') }}</div>
                    </div>
                    @if ($eventDay && $eventDay->is_on_location)
                        <span class="sfp-pill sfp-pill-sage">On-location</span>
                    @endif
                </div>

                <div style="display:grid;gap:8px;margin-bottom:16px">
                    <div style="display:flex;gap:12px;padding:13px 15px;border-radius:13px;background:#F3F6F5;border:1px solid #F0E7E1">
                        <span class="sfp-mono" style="font-size:10px;color:#2E5F4C;width:42px;flex:none;padding-top:3px">TRIAL</span>
                        <div style="font-size:13px;line-height:1.5">
                            @if ($trial)
                                {{ $trial->start_at->format('d M Y, H:i') }}
                                @if ($trial->is_on_location && $trial->venue_address)
                                    &middot; {{ $trial->venue_address }}
                                @endif
                            @else
                                Not scheduled
                            @endif
                        </div>
                    </div>
                    <div style="display:flex;gap:12px;padding:13px 15px;border-radius:13px;background:#E2EDE7;border:1px solid #D8E8E0">
                        <span class="sfp-mono" style="font-size:10px;color:#2E5F4C;width:42px;flex:none;padding-top:3px">EVENT</span>
                        <div style="font-size:13px;line-height:1.5">
                            @if ($eventDay)
                                {{ $eventDay->start_at->format('d M Y, H:i') }}
                                @if ($engagement->venue)
                                    &middot; {{ $engagement->venue }}
                                @endif
                            @else
                                Not scheduled
                            @endif
                        </div>
                    </div>
                </div>

                <div style="display:flex;align-items:center;justify-content:space-between;padding-top:14px;border-top:1px solid #EDF1F0">
                    <div style="font-size:12.5px;color:#66736F">
                        {{ $engagement->travelingStaff->pluck('user.name')->join(', ') ?: 'No traveling staff' }}
                    </div>
                    <div style="display:flex;align-items:center;gap:12px">
                        <span class="sfp-pill {{ $pillClass }}">{{ ucfirst(str_replace('_', ' ', $engagement->status)) }}</span>
                        <a href="{{ $tenantUrl->route('bridalEngagements.show', $engagement) }}" style="font-size:12.5px;color:#1B4B8F">View</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="sfp-card">
                <p style="color:#66736F;margin:0">No bridal engagements yet.</p>
            </div>
        @endforelse
    </div>
@endsection
