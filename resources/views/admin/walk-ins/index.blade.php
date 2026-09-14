@extends('layouts.admin')

@section('title', 'Walk-in Queue')

@section('content')
    <div class="sfp-page-header">
        <div>
            <h1 class="sfp-page-title">Walk-in queue</h1>
            <div class="sfp-page-subtitle">{{ $walkIns->count() }} waiting</div>
        </div>
    </div>

    @can('appointments.create')
        <div style="padding:13px;border-radius:13px;background:#F3F6F5;border:1px solid #F0E7E1;margin-bottom:16px">
            <form action="{{ $tenantUrl->route('walkIns.store') }}" method="POST" style="display: flex; align-items: flex-end; gap: 10px; flex-wrap: wrap">
                @csrf
                <div class="sfp-field" style="margin-bottom: 0">
                    <label class="sfp-label">Name</label>
                    <input type="text" name="name" class="sfp-input" style="margin-bottom: 0" value="{{ old('name') }}" required>
                </div>
                <div class="sfp-field" style="margin-bottom: 0">
                    <label class="sfp-label">Phone</label>
                    <input type="text" name="phone" class="sfp-input" style="margin-bottom: 0" value="{{ old('phone') }}">
                </div>
                <div class="sfp-field" style="margin-bottom: 0">
                    <label class="sfp-label">Service</label>
                    <select name="service_id" class="sfp-input" style="margin-bottom: 0">
                        <option value="">None</option>
                        @foreach ($services as $service)
                            <option value="{{ $service->id }}" @selected(old('service_id') == $service->id)>{{ $service->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="sfp-btn-primary">Add to queue</button>
            </form>
            @error('name')
                <span class="sfp-invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    @endcan

    <div style="display: flex; flex-direction: column; gap: 10px">
        @foreach ($walkIns as $walkIn)
            <div style="padding:13px;border-radius:13px;background:#F3F6F5;border:1px solid #F0E7E1">
                <div style="display: flex; justify-content: space-between; align-items: baseline">
                    <span style="font-size:13.5px;font-weight:500">{{ $walkIn->name }}</span>
                    <span class="sfp-mono" style="font-size:11px;color:#2E5F4C">{{ $walkIn->joined_at->diffForHumans() }}</span>
                </div>
                <div style="font-size:12px;color:#66736F;margin-top:4px">{{ $walkIn->service?->name }}</div>
                <div style="font-size:12px;color:#66736F;margin-top:2px">{{ $walkIn->phone }}</div>

                @can('appointments.edit')
                    <form action="{{ $tenantUrl->route('walkIns.assign', $walkIn) }}" method="POST" style="display: flex; align-items: flex-end; gap: 10px; margin-top: 10px">
                        @csrf
                        @method('PUT')
                        <div class="sfp-field" style="margin-bottom: 0">
                            <label class="sfp-label">Staff ID</label>
                            <input type="number" name="staff_profile_id" class="sfp-input" style="max-width: 120px; margin-bottom: 0" required>
                        </div>
                        <div class="sfp-field" style="margin-bottom: 0">
                            <label class="sfp-label">Client ID</label>
                            <input type="number" name="client_id" class="sfp-input" style="max-width: 120px; margin-bottom: 0" required>
                        </div>
                        <button type="submit" class="sfp-btn-primary">Assign</button>
                    </form>
                @endcan
            </div>
        @endforeach
    </div>
@endsection
