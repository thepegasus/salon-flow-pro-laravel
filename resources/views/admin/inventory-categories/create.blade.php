@extends('layouts.admin')

@section('title', 'Add Category')

@section('content')
    <div class="sfp-page-header">
        <div>
            <h1 class="sfp-page-title">Add category</h1>
            <p class="sfp-page-subtitle">New categories are available to pick when creating or editing a product.</p>
        </div>
    </div>

    <div class="sfp-card">
        <form action="{{ $tenantUrl->route('productCategories.store') }}" method="POST">
            @csrf

            <div class="sfp-field">
                <label class="sfp-label">Name</label>
                <input type="text" name="name" class="sfp-input" value="{{ old('name') }}" placeholder="e.g. Hair Colour">
                @error('name')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-form-actions">
                <button type="submit" class="sfp-btn-primary">Save</button>
                <a href="{{ $tenantUrl->route('productCategories.index') }}" class="sfp-btn-outline">Cancel</a>
            </div>
        </form>
    </div>
@endsection
