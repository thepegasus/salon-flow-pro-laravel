@extends('layouts.admin')

@section('title', 'Add Product')

@section('content')
    <div class="sfp-page-header">
        <div>
            <h1 class="sfp-page-title">Add product</h1>
            <p class="sfp-page-subtitle">New products appear in inventory and can be linked to services.</p>
        </div>
    </div>

    <div class="sfp-card">
        <form action="{{ $tenantUrl->route('products.store') }}" method="POST">
            @csrf

            <div class="sfp-split-2">
                <div class="sfp-field">
                    <label class="sfp-label">Name</label>
                    <input type="text" name="name" class="sfp-input" value="{{ old('name') }}">
                    @error('name')
                        <span class="sfp-invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="sfp-field">
                    <label class="sfp-label">SKU</label>
                    <input type="text" name="sku" class="sfp-input" value="{{ old('sku') }}" placeholder="e.g. SKU-1001">
                    @error('sku')
                        <span class="sfp-invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="sfp-field">
                <label class="sfp-label">Category</label>
                <select name="category_id" class="sfp-select">
                    <option value="">No category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
                <a href="{{ $tenantUrl->route('productCategories.create') }}" class="sfp-action-link" style="font-size:12px">+ Add a new category</a>
            </div>

            <div class="sfp-split-2">
                <div class="sfp-field">
                    <label class="sfp-label">Quantity on hand</label>
                    <input type="number" step="0.01" min="0" name="quantity_on_hand" class="sfp-input" value="{{ old('quantity_on_hand', 0) }}">
                    @error('quantity_on_hand')
                        <span class="sfp-invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="sfp-field">
                    <label class="sfp-label">Reorder level</label>
                    <input type="number" step="0.01" min="0" name="reorder_level" class="sfp-input" value="{{ old('reorder_level', 0) }}">
                    @error('reorder_level')
                        <span class="sfp-invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="sfp-field">
                <label class="sfp-label">Unit</label>
                <input type="text" name="unit" class="sfp-input" value="{{ old('unit', 'pcs') }}" placeholder="e.g. ml, g, pcs">
                @error('unit')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-form-actions">
                <button type="submit" class="sfp-btn-primary">Save</button>
                <a href="{{ $tenantUrl->route('products.index') }}" class="sfp-btn-outline">Cancel</a>
            </div>
        </form>
    </div>
@endsection
