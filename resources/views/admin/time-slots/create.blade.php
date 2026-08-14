@extends('layouts.admin')

@section('title', 'Add Time Slot')

@section('content')
    <div class="sfp-page-header">
        <div>
            <h1 class="sfp-page-title">Add time slot</h1>
            <p class="sfp-page-subtitle">New slots appear immediately when booking appointments.</p>
        </div>
    </div>

    <div class="sfp-card">
        <form action="{{ $tenantUrl->route('timeSlots.store') }}" method="POST">
            @csrf

            <div class="sfp-field">
                <label class="sfp-label">Start time</label>
                <input type="time" name="start_time" class="sfp-input" value="{{ old('start_time') }}">
                @error('start_time')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-field">
                <label class="sfp-label">End time</label>
                <input type="time" name="end_time" class="sfp-input" value="{{ old('end_time') }}">
                @error('end_time')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-form-actions">
                <button type="submit" class="sfp-btn-primary">Save</button>
                <a href="{{ $tenantUrl->route('timeSlots.index') }}" class="sfp-btn-outline">Cancel</a>
            </div>
        </form>
    </div>
@endsection
