@extends('layouts.admin')

@section('title', 'New Appointment')

@section('content')
    <div class="sfp-page-header">
        <div>
            <h1 class="sfp-page-title">New appointment</h1>
            <div class="sfp-page-subtitle">Book a client in for a service</div>
        </div>
    </div>

    <div class="sfp-card">
        <form action="{{ $tenantUrl->route('appointments.store') }}" method="POST">
            @csrf

            <div class="sfp-field">
                <label class="sfp-label">Client ID</label>
                <input type="number" name="client_id" class="sfp-input" value="{{ old('client_id') }}">
                @error('client_id')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-field">
                <label class="sfp-label">Staff ID</label>
                <input type="number" name="staff_profile_id" class="sfp-input" value="{{ old('staff_profile_id') }}">
                @error('staff_profile_id')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-field">
                <label class="sfp-label">Start at</label>
                <input type="datetime-local" name="start_at" class="sfp-input" value="{{ old('start_at') }}">
                @error('start_at')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-field">
                <label class="sfp-label">Service ID</label>
                <input type="number" name="services[0][service_id]" class="sfp-input" value="{{ old('services.0.service_id') }}">
                @error('services')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
                @error('services.0.service_id')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-field">
                <label class="sfp-label">Notes</label>
                <textarea name="notes" class="sfp-textarea">{{ old('notes') }}</textarea>
                @error('notes')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-form-actions">
                <button type="submit" class="sfp-btn-primary">Book</button>
            </div>
        </form>
    </div>
@endsection
