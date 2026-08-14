@extends('layouts.admin')

@section('title', 'New Appointment')

@section('content')
    <div class="sfp-page-header">
        <div>
            <h1 class="sfp-page-title">New appointment</h1>
            <div class="sfp-page-subtitle">Book a client in for a service</div>
        </div>
        @can('appointments.view')
            <a href="{{ $tenantUrl->route('timeSlots.index') }}" class="sfp-btn-outline">Manage time slots</a>
        @endcan
    </div>

    <div class="sfp-card">
        <form action="{{ $tenantUrl->route('appointments.store') }}" method="POST" id="appt-form">
            @csrf

            <div class="sfp-field">
                <label class="sfp-label">Client</label>
                <input type="text" id="appt-client-search" class="sfp-input" autocomplete="off"
                       placeholder="Search by name or mobile number" value="{{ old('client_search') }}">
                <input type="hidden" name="client_id" id="appt-client-id" value="{{ old('client_id') }}">
                <div id="appt-client-results" class="sfp-autosuggest-list" style="display:none"></div>
                <div id="appt-client-selected" style="display:none;margin-top:8px" class="sfp-pill sfp-pill-blue"></div>
                @error('client_id')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror

                <div id="appt-new-client" style="display:none;margin-top:14px;padding:14px;border:1px solid #E3EAE8;border-radius:12px;background:#FDFAF8">
                    <div style="font-size:13px;color:#66736F;margin-bottom:10px">No client found. Add a new one:</div>
                    <div class="sfp-field">
                        <label class="sfp-label">Name</label>
                        <input type="text" id="appt-new-name" class="sfp-input">
                    </div>
                    <div class="sfp-field">
                        <label class="sfp-label">Phone</label>
                        <input type="text" id="appt-new-phone" class="sfp-input">
                    </div>
                    <div id="appt-new-client-feedback" style="font-size:12.5px;color:#A8506B;min-height:16px"></div>
                    <button type="button" id="appt-new-client-save" class="sfp-btn-outline">Create client</button>
                </div>
            </div>

            <div class="sfp-field">
                <label class="sfp-label">Staff</label>
                <select name="staff_profile_id" class="sfp-input">
                    <option value="">Select staff&hellip;</option>
                    @foreach ($staff as $member)
                        <option value="{{ $member->id }}" @selected(old('staff_profile_id') == $member->id)>{{ $member->name }}</option>
                    @endforeach
                </select>
                @error('staff_profile_id')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-field">
                <label class="sfp-label">Services</label>
                <div style="display:grid;gap:8px">
                    @foreach ($services as $service)
                        <div class="form-check">
                            <input type="checkbox" name="services[{{ $loop->index }}][service_id]" value="{{ $service->id }}"
                                   id="svc-{{ $service->id }}" class="form-check-input"
                                   @checked(collect(old('services', []))->pluck('service_id')->contains($service->id))>
                            <label class="form-check-label" for="svc-{{ $service->id }}">
                                {{ $service->name }}
                                <span class="sfp-mono" style="color:#94A19D;font-size:12.5px">&mdash; {{ $service->duration_minutes }} min &middot; &#8377;{{ number_format((float) $service->price, 2) }}</span>
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('services')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-field">
                <label class="sfp-label">Date</label>
                <input type="date" id="appt-date" class="sfp-input" value="{{ old('appt_date', now()->toDateString()) }}">
            </div>

            <div class="sfp-field">
                <label class="sfp-label">Time slot</label>
                <select id="appt-time-slot" class="sfp-input">
                    <option value="">Select a time slot&hellip;</option>
                    @foreach ($timeSlots as $slot)
                        <option value="{{ substr($slot->start_time, 0, 5) }}">{{ $slot->label() }}</option>
                    @endforeach
                </select>
                @if ($timeSlots->isEmpty())
                    <p style="font-size:12.5px;color:#94A19D;margin-top:6px">
                        No time slots configured yet.
                        @can('appointments.create')
                            <a href="{{ $tenantUrl->route('timeSlots.create') }}" class="sfp-action-link">Add one</a>
                        @endcan
                    </p>
                @endif
                <input type="hidden" name="start_at" id="appt-start-at" value="{{ old('start_at') }}">
                @error('start_at')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-field">
                <label class="sfp-label">Notes</label>
                <textarea name="notes" class="sfp-textarea">{{ old('notes') }}</textarea>
                @error('notes')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-form-actions">
                <button type="submit" class="sfp-btn-primary">Book</button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const searchInput = document.getElementById('appt-client-search');
    const resultsBox = document.getElementById('appt-client-results');
    const clientIdInput = document.getElementById('appt-client-id');
    const selectedBox = document.getElementById('appt-client-selected');
    const newClientBox = document.getElementById('appt-new-client');
    const newNameInput = document.getElementById('appt-new-name');
    const newPhoneInput = document.getElementById('appt-new-phone');
    const newClientFeedback = document.getElementById('appt-new-client-feedback');
    const newClientSave = document.getElementById('appt-new-client-save');
    const dateInput = document.getElementById('appt-date');
    const slotSelect = document.getElementById('appt-time-slot');
    const startAtInput = document.getElementById('appt-start-at');

    let searchTimer = null;

    function selectClient(client) {
        clientIdInput.value = client.id;
        selectedBox.textContent = client.name + (client.phone ? ' · ' + client.phone : '');
        selectedBox.style.display = 'inline-block';
        resultsBox.style.display = 'none';
        newClientBox.style.display = 'none';
        searchInput.value = client.name;
    }

    function clearSelection() {
        clientIdInput.value = '';
        selectedBox.style.display = 'none';
    }

    function renderResults(clients) {
        resultsBox.innerHTML = '';

        if (clients.length === 0) {
            newPhoneInput.value = /^[0-9+ -]{4,}$/.test(searchInput.value.trim()) ? searchInput.value.trim() : '';
            newNameInput.value = /^[0-9+ -]{4,}$/.test(searchInput.value.trim()) ? '' : searchInput.value.trim();
            newClientBox.style.display = 'block';
            resultsBox.style.display = 'none';
            return;
        }

        newClientBox.style.display = 'none';
        clients.forEach((client) => {
            const row = document.createElement('div');
            row.className = 'sfp-autosuggest-item';
            row.textContent = client.name + (client.phone ? ' · ' + client.phone : '');
            row.addEventListener('click', () => selectClient(client));
            resultsBox.appendChild(row);
        });
        resultsBox.style.display = 'block';
    }

    async function search(term) {
        const response = await fetch('{{ url("/appointments/clients/search") }}?q=' + encodeURIComponent(term), {
            headers: { 'Accept': 'application/json' },
        });

        if (!response.ok) {
            return;
        }

        const data = await response.json();
        renderResults(data.clients || []);
    }

    searchInput.addEventListener('input', () => {
        clearSelection();
        const term = searchInput.value.trim();

        clearTimeout(searchTimer);

        if (term.length < 2) {
            resultsBox.style.display = 'none';
            newClientBox.style.display = 'none';
            return;
        }

        searchTimer = setTimeout(() => search(term), 250);
    });

    newClientSave.addEventListener('click', async () => {
        newClientFeedback.textContent = '';

        if (!newNameInput.value.trim() || !newPhoneInput.value.trim()) {
            newClientFeedback.textContent = 'Name and phone are both required.';
            return;
        }

        newClientSave.disabled = true;

        try {
            const response = await fetch('{{ url("/appointments/clients/quick-create") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ name: newNameInput.value.trim(), phone: newPhoneInput.value.trim() }),
            });

            const data = await response.json();

            if (!response.ok) {
                newClientFeedback.textContent = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Could not create client.');
                newClientSave.disabled = false;
                return;
            }

            selectClient(data);
        } catch (error) {
            newClientFeedback.textContent = 'Network error creating client.';
            newClientSave.disabled = false;
        }
    });

    function updateStartAt() {
        if (dateInput.value && slotSelect.value) {
            startAtInput.value = dateInput.value + 'T' + slotSelect.value;
        } else {
            startAtInput.value = '';
        }
    }

    dateInput.addEventListener('change', updateStartAt);
    slotSelect.addEventListener('change', updateStartAt);
    updateStartAt();

    document.addEventListener('click', (event) => {
        if (!resultsBox.contains(event.target) && event.target !== searchInput) {
            resultsBox.style.display = 'none';
        }
    });
})();
</script>
@endsection
