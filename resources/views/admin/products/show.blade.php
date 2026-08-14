@extends('layouts.admin')

@section('title', $product->name)

@section('content')
    @php
        $isLowStock = $product->quantity_on_hand <= $product->reorder_level;
    @endphp

    <div class="sfp-page-header">
        <div>
            <h1 class="sfp-page-title">{{ $product->name }}</h1>
            <p class="sfp-page-subtitle">
                {{ $product->category?->name ?: 'Uncategorised' }}
                @if ($product->sku)
                    &middot; SKU <span class="sfp-mono">{{ $product->sku }}</span>
                @endif
            </p>
        </div>
        @can('inventory.edit')
            <a href="{{ $tenantUrl->route('products.edit', $product) }}" class="sfp-btn-outline">Edit product</a>
        @endcan
    </div>

    <div class="sfp-card" style="margin-bottom:16px">
        <div style="display:flex;align-items:center;gap:28px;flex-wrap:wrap">
            <div>
                <div class="sfp-label" style="margin-bottom:6px">On hand</div>
                <div class="sfp-mono" style="font-size:20px">
                    <span class="sfp-stock-dot {{ $isLowStock ? 'sfp-stock-dot-low' : '' }}"></span>{{ number_format($product->quantity_on_hand, 2) }} {{ $product->unit }}
                </div>
            </div>
            <div>
                <div class="sfp-label" style="margin-bottom:6px">Reorder at</div>
                <div class="sfp-mono" style="font-size:20px">{{ number_format($product->reorder_level, 2) }} {{ $product->unit }}</div>
            </div>
            <div>
                <div class="sfp-label" style="margin-bottom:6px">Status</div>
                @if ($product->is_active)
                    <span class="sfp-pill sfp-pill-green">Active</span>
                @else
                    <span class="sfp-pill sfp-pill-neutral">Disabled</span>
                @endif
            </div>
        </div>
    </div>

    @can('inventory.edit')
        <h2 class="sfp-card-title">Stock count / adjust stock</h2>
        <div class="sfp-card" style="margin-bottom:16px">
            <form action="{{ $tenantUrl->route('products.stockAdjustments.store', $product) }}" method="POST">
                @csrf

                <div class="sfp-split-2">
                    <div class="sfp-field">
                        <label class="sfp-label">Quantity change</label>
                        <input type="number" step="0.01" name="quantity_delta" class="sfp-input" value="{{ old('quantity_delta') }}" placeholder="e.g. -5 or 10">
                        @error('quantity_delta')
                            <span class="sfp-invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="sfp-field">
                        <label class="sfp-label">Reason</label>
                        <input type="text" name="reason" class="sfp-input" value="{{ old('reason') }}" placeholder="e.g. Stock count, Damaged, Restock">
                        @error('reason')
                            <span class="sfp-invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="sfp-form-actions">
                    <button type="submit" class="sfp-btn-primary">Adjust stock</button>
                </div>
            </form>
        </div>
    @endcan

    <h2 class="sfp-card-title">Adjustment history</h2>
    <div class="sfp-table-wrap">
        <div class="sfp-table-head-row" style="grid-template-columns:1fr 1fr 1fr 1fr">
            <span>Change</span>
            <span>Reason</span>
            <span>Adjusted by</span>
            <span>Date</span>
        </div>
        @forelse ($product->stockAdjustments()->latest()->get() as $adjustment)
            <div class="sfp-table-row" style="grid-template-columns:1fr 1fr 1fr 1fr">
                <span class="sfp-mono" style="font-size:13px">{{ $adjustment->quantity_delta > 0 ? '+' : '' }}{{ number_format($adjustment->quantity_delta, 2) }}</span>
                <span style="font-size:13.5px;color:#66736F">{{ $adjustment->reason }}</span>
                <span style="font-size:13.5px;color:#66736F">{{ $adjustment->adjustedBy?->name ?? '—' }}</span>
                <span style="font-size:13.5px;color:#66736F">{{ $adjustment->created_at->format('d M Y, H:i') }}</span>
            </div>
        @empty
            <div class="sfp-table-row" style="grid-template-columns:1fr">
                <span style="color:#94A19D;font-size:13.5px">No adjustments recorded.</span>
            </div>
        @endforelse
    </div>
@endsection
