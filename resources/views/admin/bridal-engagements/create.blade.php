@extends('layouts.admin')

@section('title', 'New Engagement')

@section('content')
    <div class="sfp-page-header">
        <div>
            <h1 class="sfp-page-title">New bridal engagement</h1>
            <p class="sfp-page-subtitle">Trial and event day are booked as one linked engagement.</p>
        </div>
    </div>

    <div class="sfp-card">
        <form action="{{ $tenantUrl->route('bridalEngagements.store') }}" method="POST">
            @csrf

            <div class="sfp-field">
                <label class="sfp-label">Client ID</label>
                <input type="number" name="client_id" class="sfp-input" value="{{ old('client_id') }}">
                @error('client_id')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-split-2">
                <div class="sfp-field">
                    <label class="sfp-label">Event date</label>
                    <input type="date" name="event_date" class="sfp-input" value="{{ old('event_date') }}">
                    @error('event_date')
                        <span class="sfp-invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="sfp-field">
                    <label class="sfp-label">Venue</label>
                    <input type="text" name="venue" class="sfp-input" value="{{ old('venue') }}">
                    @error('venue')
                        <span class="sfp-invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <h2 class="sfp-card-title" style="margin-top:24px">Trial</h2>

            <div class="sfp-split-2">
                <div class="sfp-field">
                    <label class="sfp-label">Trial staff ID</label>
                    <input type="number" name="trial_staff_profile_id" class="sfp-input" value="{{ old('trial_staff_profile_id') }}">
                    @error('trial_staff_profile_id')
                        <span class="sfp-invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="sfp-field">
                    <label class="sfp-label">Trial date/time</label>
                    <input type="datetime-local" name="trial_start_at" class="sfp-input" value="{{ old('trial_start_at') }}">
                    @error('trial_start_at')
                        <span class="sfp-invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="sfp-field">
                <label class="sfp-label">Trial service ID</label>
                <input type="number" name="trial_services[0][service_id]" class="sfp-input" value="{{ old('trial_services.0.service_id') }}">
                @error('trial_services.0.service_id')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <h2 class="sfp-card-title" style="margin-top:24px">Event day</h2>

            <div class="sfp-split-2">
                <div class="sfp-field">
                    <label class="sfp-label">Event staff ID</label>
                    <input type="number" name="event_staff_profile_id" class="sfp-input" value="{{ old('event_staff_profile_id') }}">
                    @error('event_staff_profile_id')
                        <span class="sfp-invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="sfp-field">
                    <label class="sfp-label">Event date/time</label>
                    <input type="datetime-local" name="event_start_at" class="sfp-input" value="{{ old('event_start_at') }}">
                    @error('event_start_at')
                        <span class="sfp-invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="sfp-field">
                <label class="sfp-label">Event service ID</label>
                <input type="number" name="event_services[0][service_id]" class="sfp-input" value="{{ old('event_services.0.service_id') }}">
                @error('event_services.0.service_id')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-field">
                <label class="sfp-label" style="display:flex;align-items:center;gap:8px">
                    <input type="checkbox" name="event_is_on_location" value="1" checked>
                    Event day is on-location (stylist travels to venue)
                </label>
                @error('event_is_on_location')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-form-actions">
                <button type="submit" class="sfp-btn-primary">Create engagement</button>
                <a href="{{ $tenantUrl->route('bridalEngagements.index') }}" class="sfp-btn-outline">Cancel</a>
            </div>
        </form>
    </div>
@endsection
