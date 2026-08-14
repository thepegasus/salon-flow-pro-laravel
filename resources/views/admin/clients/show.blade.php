@extends('layouts.admin')

@section('title', $client->name)

@section('content')
    <div style="margin-bottom:18px">
        <a href="{{ route('clients.index') }}" class="sfp-action-link">&larr; Back to clients</a>
    </div>

    <div class="sfp-page-header">
        <div style="display:flex;align-items:flex-start;gap:20px;flex-wrap:wrap">
            <div style="width:68px;height:68px;border-radius:20px;background:#E2EDE7;display:flex;align-items:center;justify-content:center;font-family:'Bricolage Grotesque',Outfit,sans-serif;font-weight:600;font-size:28px;color:#2E5F4C;flex:none">
                {{ strtoupper(substr($client->name, 0, 1)) }}
            </div>
            <div>
                <h1 class="sfp-page-title">{{ $client->name }}</h1>
                <div style="font-size:14px;color:#66736F;margin-top:6px">
                    {{ $client->phone }}
                    @if ($client->email)
                        &middot; {{ $client->email }}
                    @endif
                </div>
                @if ($client->is_frequent_no_show)
                    <span class="sfp-pill sfp-pill-red" style="margin-top:10px">Frequent no-show</span>
                @endif
            </div>
        </div>
        @can('clients.edit')
            <a href="{{ route('clients.edit', $client) }}" class="sfp-btn-outline">Edit client</a>
        @endcan
    </div>

    @if ($client->is_frequent_no_show)
        <div class="sfp-alert-error" style="margin-bottom:20px">Flagged: frequent no-shows &mdash; confirm before booking.</div>
    @endif

    <div class="sfp-split-2" style="grid-template-columns: 1.4fr 1fr; align-items:start">
        <div>
            <div class="sfp-card-title">Visit history</div>
            <div class="sfp-table-wrap">
                <div class="sfp-table-head-row" style="grid-template-columns:160px 1fr 140px">
                    <span>Date</span>
                    <span>Services</span>
                    <span>Status</span>
                </div>

                @forelse ($client->appointments as $appointment)
                    <div class="sfp-table-row" style="grid-template-columns:160px 1fr 140px">
                        <span class="sfp-mono" style="font-size:13px;color:#66736F">{{ $appointment->start_at->format('d M Y, H:i') }}</span>
                        <span style="font-size:14px">{{ $appointment->services->pluck('name')->join(', ') }}</span>
                        <span class="sfp-pill sfp-pill-blue">{{ ucfirst(str_replace('_', ' ', $appointment->status)) }}</span>
                    </div>
                @empty
                    <div class="sfp-table-row" style="grid-template-columns:1fr">
                        <p style="color:#66736F;margin:0">No visits yet.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div>
            <div class="sfp-card-title">Notes &amp; family</div>
            @if ($client->family_link || $client->notes)
                <div class="sfp-card">
                    @if ($client->family_link)
                        <p style="font-size:14px;margin:0 0 14px">
                            <strong>Family link:</strong><br>{{ $client->family_link }}
                        </p>
                    @endif
                    @if ($client->notes)
                        <p style="font-size:14px;margin:0">
                            <strong>Notes:</strong><br>{{ $client->notes }}
                        </p>
                    @endif
                </div>
            @else
                <div class="sfp-card">
                    <p style="color:#94A19D;font-size:13.5px;margin:0">No notes on file.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
