@extends('layouts.admin')

@section('title', 'Edit Product')

@section('content')
    <div class="sfp-page-header">
        <div>
            <h1 class="sfp-page-title">Edit {{ $product->name }}</h1>
            <p class="sfp-page-subtitle">Use stock adjustments to change quantity on hand.</p>
        </div>
    </div>

    <div class="sfp-card">
        <form action="{{ $tenantUrl->route('products.update', $product) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="sfp-split-2">
                <div class="sfp-field">
                    <label class="sfp-label">Name</label>
                    <input type="text" name="name" class="sfp-input" value="{{ old('name', $product->name) }}">
                    @error('name')
                        <span class="sfp-invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="sfp-field">
                    <label class="sfp-label">SKU</label>
                    <input type="text" name="sku" class="sfp-input" value="{{ old('sku', $product->sku) }}" placeholder="e.g. SKU-1001">
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
                        <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
                <a href="{{ $tenantUrl->route('productCategories.create') }}" class="sfp-action-link" style="font-size:12px">+ Add a new category</a>
            </div>

            <div class="sfp-split-2">
                <div class="sfp-field">
                    <label class="sfp-label">Reorder level</label>
                    <input type="number" step="0.01" min="0" name="reorder_level" class="sfp-input" value="{{ old('reorder_level', $product->reorder_level) }}">
                    @error('reorder_level')
                        <span class="sfp-invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="sfp-field">
                    <label class="sfp-label">Unit</label>
                    <input type="text" name="unit" class="sfp-input" value="{{ old('unit', $product->unit) }}" placeholder="e.g. ml, g, pcs">
                    @error('unit')
                        <span class="sfp-invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="sfp-field" style="display:flex;align-items:center;gap:10px;margin-bottom:18px">
                <input type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $product->is_active))>
                <label class="sfp-label" for="is_active" style="margin-bottom:0">Active</label>
                @error('is_active')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-form-actions">
                <button type="submit" class="sfp-btn-primary">Save</button>
                <a href="{{ $tenantUrl->route('products.show', $product) }}" class="sfp-btn-outline">Cancel</a>
            </div>
        </form>
    </div>
@endsection
