@extends('layouts.admin')

@section('title', 'Edit Staff')

@section('content')
    <div class="sfp-page-header">
        <div>
            <h1 class="sfp-page-title">Edit {{ $staff->user->name }}</h1>
            <p class="sfp-page-subtitle">Update job title, phone and availability</p>
        </div>
    </div>

    <div class="sfp-card">
        <form action="{{ route('staff.update', $staff) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="sfp-field">
                <label class="sfp-label">Job Title</label>
                <input type="text" name="job_title" class="sfp-input" value="{{ old('job_title', $staff->job_title) }}">
                @error('job_title')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-field">
                <label class="sfp-label">Phone</label>
                <input type="text" name="phone" class="sfp-input" value="{{ old('phone', $staff->phone) }}">
                @error('phone')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-field form-check">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $staff->is_active))>
                <label class="form-check-label sfp-label" style="display: inline; text-transform: none; letter-spacing: normal;">Active</label>
                @error('is_active')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-form-actions">
                <button type="submit" class="sfp-btn-primary">Save</button>
            </div>
        </form>
    </div>
@endsection
