@extends('layouts.admin')

@section('title', 'New Bill')

@section('content')
    <div class="sfp-page-header">
        <div>
            <h1 class="sfp-page-title">New bill</h1>
            <p class="sfp-page-subtitle">Add services and assign the staff member who performed each one.</p>
        </div>
    </div>

    <div class="sfp-card">
        <form action="{{ $tenantUrl->route('bills.storeManual') }}" method="POST" id="bill-form">
            @csrf

            <div class="sfp-field">
                <label class="sfp-label">Client</label>
                <select name="client_id" class="sfp-select">
                    <option value="">Select client&hellip;</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>{{ $client->name }} @if($client->phone) &middot; {{ $client->phone }} @endif</option>
                    @endforeach
                </select>
                @error('client_id')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-field">
                <label class="sfp-label">Line items</label>
                <div id="bill-items"></div>
                <button type="button" id="bill-add-item" class="sfp-btn-outline" style="margin-top:10px">+ Add line item</button>
                @error('items')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-form-actions">
                <button type="submit" class="sfp-btn-primary">Create bill</button>
                <a href="{{ $tenantUrl->route('bills.index') }}" class="sfp-btn-outline">Cancel</a>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
<script>
(function () {
    const services = @json($services->map(fn ($service) => [
        'id' => $service->id,
        'name' => $service->name,
        'price' => (float) $service->price,
    ])->values());

    const itemsBox = document.getElementById('bill-items');
    const addItemBtn = document.getElementById('bill-add-item');
    let itemIndex = 0;

    async function fetchEligibleStaff(serviceId) {
        if (!serviceId) {
            return [];
        }

        const response = await fetch('{{ url("/services") }}/' + encodeURIComponent(serviceId) + '/eligible-staff', {
            headers: { 'Accept': 'application/json' },
        });

        if (!response.ok) {
            return [];
        }

        const data = await response.json();
        return data.staff || [];
    }

    function renderStaffOptions(select, staff, selectedId) {
        select.innerHTML = '<option value="">Select staff&hellip;</option>';

        staff.forEach((member) => {
            const option = document.createElement('option');
            option.value = member.id;
            option.textContent = member.name;
            option.selected = String(member.id) === String(selectedId || '');
            select.appendChild(option);
        });
    }

    function addItemRow() {
        const index = itemIndex++;
        const row = document.createElement('div');
        row.className = 'sfp-row';
        row.style.cssText = 'display:grid;grid-template-columns:1.6fr 1.2fr 70px 110px auto;gap:10px;align-items:end;padding:12px 0;border-bottom:1px solid #EDF1F0';

        row.innerHTML = `
            <div class="sfp-field" style="margin-bottom:0">
                <label class="sfp-label">Service</label>
                <select name="items[${index}][service_id]" class="sfp-select bill-item-service">
                    <option value="">Manual item&hellip;</option>
                    ${services.map((s) => `<option value="${s.id}" data-price="${s.price}">${s.name}</option>`).join('')}
                </select>
            </div>
            <div class="sfp-field" style="margin-bottom:0">
                <label class="sfp-label">Staff</label>
                <select name="items[${index}][staff_profile_id]" class="sfp-select bill-item-staff">
                    <option value="">Select a service first</option>
                </select>
            </div>
            <div class="sfp-field" style="margin-bottom:0">
                <label class="sfp-label">Qty</label>
                <input type="number" min="1" value="1" name="items[${index}][quantity]" class="sfp-input">
            </div>
            <div class="sfp-field" style="margin-bottom:0">
                <label class="sfp-label">Price</label>
                <input type="number" step="0.01" min="0" name="items[${index}][unit_price]" class="sfp-input bill-item-price">
            </div>
            <button type="button" class="sfp-btn-outline bill-item-remove" style="height:38px">Remove</button>
        `;

        const descriptionInput = document.createElement('input');
        descriptionInput.type = 'hidden';
        descriptionInput.name = `items[${index}][description]`;
        row.appendChild(descriptionInput);

        const serviceSelect = row.querySelector('.bill-item-service');
        const staffSelect = row.querySelector('.bill-item-staff');
        const priceInput = row.querySelector('.bill-item-price');
        const removeBtn = row.querySelector('.bill-item-remove');

        serviceSelect.addEventListener('change', async () => {
            const selectedOption = serviceSelect.selectedOptions[0];
            descriptionInput.value = selectedOption.value ? selectedOption.textContent : '';
            priceInput.value = selectedOption.dataset.price || priceInput.value;

            staffSelect.innerHTML = '<option value="">Loading&hellip;</option>';
            const staff = await fetchEligibleStaff(selectedOption.value);
            renderStaffOptions(staffSelect, staff);
        });

        removeBtn.addEventListener('click', () => row.remove());

        itemsBox.appendChild(row);
    }

    addItemBtn.addEventListener('click', addItemRow);
    addItemRow();
})();
</script>
@endsection
