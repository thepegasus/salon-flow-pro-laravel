@extends('layouts.admin')

@section('title', 'Add Commission Rate')

@section('content')
    <div class="sfp-page-header">
        <div>
            <h1 class="sfp-page-title">Add commission rate</h1>
            <p class="sfp-page-subtitle">Leave staff or category blank to set a default that applies broadly.</p>
        </div>
    </div>

    <div class="sfp-card">
        <form action="{{ $tenantUrl->route('commissionRates.store') }}" method="POST">
            @csrf

            <div class="sfp-split-2">
                <div class="sfp-field">
                    <label class="sfp-label">Staff member</label>
                    <select name="staff_profile_id" class="sfp-select">
                        <option value="">All staff (default)</option>
                        @foreach ($staff as $member)
                            <option value="{{ $member->id }}" @selected(old('staff_profile_id') == $member->id)>{{ $member->user->name }}</option>
                        @endforeach
                    </select>
                    @error('staff_profile_id')
                        <span class="sfp-invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="sfp-field">
                    <label class="sfp-label">Service category</label>
                    <select name="service_category_id" class="sfp-select">
                        <option value="">All categories (default)</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('service_category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('service_category_id')
                        <span class="sfp-invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="sfp-split-2">
                <div class="sfp-field">
                    <label class="sfp-label">Rate (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="rate_percent" class="sfp-input" value="{{ old('rate_percent') }}">
                    @error('rate_percent')
                        <span class="sfp-invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="sfp-field">
                    <label class="sfp-label">Effective from</label>
                    <input type="date" name="effective_from" class="sfp-input" value="{{ old('effective_from') }}">
                    @error('effective_from')
                        <span class="sfp-invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="sfp-form-actions">
                <button type="submit" class="sfp-btn-primary">Save</button>
                <a href="{{ $tenantUrl->route('commissionRates.index') }}" class="sfp-btn-outline">Cancel</a>
            </div>
        </form>
    </div>
@endsection
