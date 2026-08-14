@extends('layouts.admin')

@section('title', 'Add Designation')

@section('content')
    <div class="sfp-page-header">
        <div>
            <h1 class="sfp-page-title">Add designation</h1>
            <p class="sfp-page-subtitle">New designations are available to pick when creating or editing a staff record.</p>
        </div>
    </div>

    <div class="sfp-card">
        <form action="{{ $tenantUrl->route('designations.store') }}" method="POST">
            @csrf

            <div class="sfp-field">
                <label class="sfp-label">Name</label>
                <input type="text" name="name" class="sfp-input" value="{{ old('name') }}" placeholder="e.g. Senior Stylist">
                @error('name')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-form-actions">
                <button type="submit" class="sfp-btn-primary">Save</button>
                <a href="{{ $tenantUrl->route('designations.index') }}" class="sfp-btn-outline">Cancel</a>
            </div>
        </form>
    </div>
@endsection
