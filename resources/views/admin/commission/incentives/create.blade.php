@extends('layouts.admin')

@section('title', 'Award Incentive')

@section('content')
    <div class="sfp-page-header">
        <div>
            <h1 class="sfp-page-title">Award incentive</h1>
            <p class="sfp-page-subtitle">A one-off bonus, separate from commission. To correct an award, add a new entry with a negative amount.</p>
        </div>
    </div>

    <div class="sfp-card">
        <form action="{{ $tenantUrl->route('staffIncentives.store') }}" method="POST">
            @csrf

            <div class="sfp-field">
                <label class="sfp-label">Staff member</label>
                <select name="staff_profile_id" class="sfp-select">
                    <option value="">Select staff member</option>
                    @foreach ($staff as $member)
                        <option value="{{ $member->id }}" @selected(old('staff_profile_id') == $member->id)>{{ $member->user->name }}</option>
                    @endforeach
                </select>
                @error('staff_profile_id')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-split-2">
                <div class="sfp-field">
                    <label class="sfp-label">Amount</label>
                    <input type="number" step="0.01" name="amount" class="sfp-input" value="{{ old('amount') }}">
                    @error('amount')
                        <span class="sfp-invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="sfp-field">
                    <label class="sfp-label">Awarded date</label>
                    <input type="date" name="awarded_date" class="sfp-input" value="{{ old('awarded_date') }}">
                    @error('awarded_date')
                        <span class="sfp-invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="sfp-field">
                <label class="sfp-label">Reason</label>
                <input type="text" name="reason" class="sfp-input" value="{{ old('reason') }}">
                @error('reason')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-form-actions">
                <button type="submit" class="sfp-btn-primary">Award incentive</button>
                <a href="{{ $tenantUrl->route('commissionEarnings.index') }}" class="sfp-btn-outline">Cancel</a>
            </div>
        </form>
    </div>
@endsection
