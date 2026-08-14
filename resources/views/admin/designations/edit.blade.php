@extends('layouts.admin')

@section('title', 'Edit Designation')

@section('content')
    <div class="sfp-page-header">
        <div>
            <h1 class="sfp-page-title">Edit {{ $designation->name }}</h1>
            <p class="sfp-page-subtitle">Renaming a designation updates it everywhere it's used.</p>
        </div>
    </div>

    <div class="sfp-card">
        <form action="{{ $tenantUrl->route('designations.update', $designation) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="sfp-field">
                <label class="sfp-label">Name</label>
                <input type="text" name="name" class="sfp-input" value="{{ old('name', $designation->name) }}">
                @error('name')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-field" style="display:flex;align-items:center;gap:10px;margin-bottom:18px">
                <input type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $designation->is_active))>
                <label class="sfp-label" for="is_active" style="margin-bottom:0">Active</label>
            </div>

            <div class="sfp-form-actions">
                <button type="submit" class="sfp-btn-primary">Save</button>
                <a href="{{ $tenantUrl->route('designations.index') }}" class="sfp-btn-outline">Cancel</a>
            </div>
        </form>
    </div>
@endsection
