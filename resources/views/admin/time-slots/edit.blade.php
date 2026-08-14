@extends('layouts.admin')

@section('title', 'Edit Time Slot')

@section('content')
    <div class="sfp-page-header">
        <div>
            <h1 class="sfp-page-title">Edit {{ $timeSlot->label() }}</h1>
            <p class="sfp-page-subtitle">Changing a slot's time doesn't move existing appointments.</p>
        </div>
    </div>

    <div class="sfp-card">
        <form action="{{ $tenantUrl->route('timeSlots.update', $timeSlot) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="sfp-field">
                <label class="sfp-label">Start time</label>
                <input type="time" name="start_time" class="sfp-input" value="{{ old('start_time', substr($timeSlot->start_time, 0, 5)) }}">
                @error('start_time')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-field">
                <label class="sfp-label">End time</label>
                <input type="time" name="end_time" class="sfp-input" value="{{ old('end_time', substr($timeSlot->end_time, 0, 5)) }}">
                @error('end_time')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-field" style="display:flex;align-items:center;gap:10px;margin-bottom:18px">
                <input type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $timeSlot->is_active))>
                <label class="sfp-label" for="is_active" style="margin-bottom:0">Active</label>
            </div>

            <div class="sfp-form-actions">
                <button type="submit" class="sfp-btn-primary">Save</button>
                <a href="{{ $tenantUrl->route('timeSlots.index') }}" class="sfp-btn-outline">Cancel</a>
            </div>
        </form>
    </div>
@endsection
