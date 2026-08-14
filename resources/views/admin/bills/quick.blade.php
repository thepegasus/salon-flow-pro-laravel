@extends('layouts.admin')

@section('title', 'Quick Bill')

@section('content')
    <div class="sfp-page-header">
        <div>
            <h1 class="sfp-page-title">Quick bill</h1>
            <p class="sfp-page-subtitle">Type a service code and press Enter. No mouse needed &mdash; press Esc at any time to reset.</p>
        </div>
    </div>

    <div class="sfp-split-2" style="grid-template-columns: 1.4fr 1fr; align-items:start">
        <div class="sfp-card">
            <div class="sfp-field">
                <label class="sfp-label" for="qb-code">Service code</label>
                <input type="text" id="qb-code" class="sfp-input" inputmode="numeric" autocomplete="off" placeholder="Type code, press Enter" autofocus>
                <div id="qb-code-feedback" style="font-size:12.5px;margin-top:-12px;margin-bottom:14px;min-height:16px"></div>
            </div>

            <div class="sfp-table-wrap" style="margin-top:8px">
                <div class="sfp-table-head-row" style="grid-template-columns:70px 1fr 96px">
                    <span>Code</span>
                    <span>Service</span>
                    <span style="text-align:right">Price</span>
                </div>
                <div id="qb-lines"></div>
                <div id="qb-empty" class="sfp-table-row" style="grid-template-columns:1fr">
                    <span style="color:#94A19D;font-size:13.5px">No items added yet.</span>
                </div>
            </div>

            <div style="display:flex;justify-content:space-between;align-items:baseline;padding-top:16px;margin-top:16px;border-top:1px solid #E3EAE8">
                <span style="font-size:15px">Total</span>
                <span id="qb-total" class="sfp-heading" style="font-size:30px">&#8377;0.00</span>
            </div>
        </div>

        <div class="sfp-card">
            <div class="sfp-card-title">Settle</div>

            <div class="sfp-field">
                <label class="sfp-label" for="qb-phone">Client phone (blank = walk-in)</label>
                <input type="text" id="qb-phone" class="sfp-input" inputmode="numeric" autocomplete="off">
                <div id="qb-phone-feedback" style="font-size:12.5px;margin-top:-12px;margin-bottom:14px;min-height:16px"></div>
            </div>

            <div class="sfp-field">
                <label class="sfp-label">Payment method &mdash; press 1, 2, or 3</label>
                <div style="display:flex;gap:8px" id="qb-methods">
                    <button type="button" class="sfp-btn-outline qb-method active" data-method="cash" style="flex:1">1 &middot; Cash</button>
                    <button type="button" class="sfp-btn-outline qb-method" data-method="card" style="flex:1">2 &middot; Card</button>
                    <button type="button" class="sfp-btn-outline qb-method" data-method="upi" style="flex:1">3 &middot; UPI</button>
                </div>
            </div>

            <div id="qb-settle-feedback" style="font-size:13px;margin:8px 0;min-height:18px"></div>

            <div class="sfp-form-actions">
                <button type="button" id="qb-settle" class="sfp-btn-primary" style="width:100%">Settle &amp; print (Enter)</button>
            </div>

            <p style="font-size:12px;color:#94A19D;margin-top:16px;line-height:1.6">
                Flow: type code &rarr; Enter (repeats) &middot; blank code + Enter moves to phone &middot; Enter on phone moves to payment &middot; 1/2/3 picks method &middot; Enter settles.
            </p>
        </div>
    </div>
@endsection

@section('scripts')
<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const codeInput = document.getElementById('qb-code');
    const codeFeedback = document.getElementById('qb-code-feedback');
    const linesBody = document.getElementById('qb-lines');
    const emptyRow = document.getElementById('qb-empty');
    const totalEl = document.getElementById('qb-total');
    const phoneInput = document.getElementById('qb-phone');
    const phoneFeedback = document.getElementById('qb-phone-feedback');
    const settleBtn = document.getElementById('qb-settle');
    const settleFeedback = document.getElementById('qb-settle-feedback');
    const methodButtons = [...document.querySelectorAll('.qb-method')];

    let lines = [];
    let paymentMethod = 'cash';

    const money = (n) => '₹' + Number(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    function renderLines() {
        linesBody.innerHTML = '';
        emptyRow.style.display = lines.length ? 'none' : 'grid';

        lines.forEach((line, index) => {
            const row = document.createElement('div');
            row.className = 'sfp-table-row';
            row.style.gridTemplateColumns = '70px 1fr 96px';
            row.innerHTML = `
                <span class="sfp-mono" style="font-size:12.5px;color:#94A19D">${line.code}</span>
                <span style="font-size:14px">${line.name}</span>
                <span class="sfp-mono" style="text-align:right;font-size:13.5px">${money(line.price)}</span>
            `;
            linesBody.appendChild(row);
        });

        const total = lines.reduce((sum, l) => sum + Number(l.price), 0);
        totalEl.textContent = money(total);
    }

    function setCodeFeedback(message, isError) {
        codeFeedback.textContent = message || '';
        codeFeedback.style.color = isError ? '#A8506B' : '#2F6849';
    }

    function setPhoneFeedback(message, isError) {
        phoneFeedback.textContent = message || '';
        phoneFeedback.style.color = isError ? '#A8506B' : '#2F6849';
    }

    function setSettleFeedback(message, isError) {
        settleFeedback.textContent = message || '';
        settleFeedback.style.color = isError ? '#A8506B' : '#66736F';
    }

    function selectMethod(method) {
        paymentMethod = method;
        methodButtons.forEach((btn) => {
            btn.classList.toggle('active', btn.dataset.method === method);
        });
    }

    async function lookupService(code) {
        const response = await fetch('{{ url("/bills/quick/services") }}/' + encodeURIComponent(code), {
            headers: { 'Accept': 'application/json' },
        });

        if (!response.ok) {
            return null;
        }

        return response.json();
    }

    async function lookupClient(phone) {
        const response = await fetch('{{ url("/bills/quick/clients") }}/' + encodeURIComponent(phone), {
            headers: { 'Accept': 'application/json' },
        });

        if (!response.ok) {
            return null;
        }

        return response.json();
    }

    async function handleCodeEnter() {
        const code = codeInput.value.trim();

        if (code === '') {
            phoneInput.focus();
            phoneInput.select();
            return;
        }

        const service = await lookupService(code);

        if (!service || !service.found) {
            setCodeFeedback('No active service for code "' + code + '".', true);
            codeInput.select();
            return;
        }

        lines.push({ code: service.code, name: service.name, price: service.price });
        renderLines();
        setCodeFeedback(service.name + ' added.', false);
        codeInput.value = '';
        codeInput.focus();
    }

    async function handlePhoneEnter() {
        const phone = phoneInput.value.trim();

        if (phone === '') {
            setPhoneFeedback('Walk-in customer.', false);
        } else {
            const client = await lookupClient(phone);

            if (!client || !client.found) {
                setPhoneFeedback('No client found for that phone number.', true);
                phoneInput.select();
                return;
            }

            setPhoneFeedback(client.name, false);
        }

        methodButtons[0].focus();
    }

    async function settle() {
        if (lines.length === 0) {
            setSettleFeedback('Add at least one service before settling.', true);
            codeInput.focus();
            return;
        }

        settleBtn.disabled = true;
        setSettleFeedback('Settling…', false);

        try {
            const response = await fetch('{{ route("bills.quick.settle") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    codes: lines.map((l) => l.code),
                    client_phone: phoneInput.value.trim() || null,
                    payment_method: paymentMethod,
                }),
            });

            const data = await response.json();

            if (!response.ok) {
                setSettleFeedback(data.message || 'Could not settle the bill.', true);
                settleBtn.disabled = false;
                return;
            }

            setSettleFeedback('Bill ' + data.bill_number + ' settled. Redirecting…', false);
            window.location.href = data.redirect;
        } catch (error) {
            setSettleFeedback('Network error settling the bill.', true);
            settleBtn.disabled = false;
        }
    }

    codeInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            handleCodeEnter();
        }
        if (event.key === 'Escape') {
            codeInput.value = '';
            setCodeFeedback('', false);
        }
    });

    phoneInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            handlePhoneEnter();
        }
    });

    methodButtons.forEach((btn) => {
        btn.addEventListener('click', () => selectMethod(btn.dataset.method));
        btn.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                settle();
            }
            if (event.key === '1') { selectMethod('cash'); }
            if (event.key === '2') { selectMethod('card'); }
            if (event.key === '3') { selectMethod('upi'); }
        });
    });

    settleBtn.addEventListener('click', settle);

    document.addEventListener('keydown', (event) => {
        const active = document.activeElement;
        const inFormField = active === codeInput || active === phoneInput;

        if (!inFormField && (event.key === '1' || event.key === '2' || event.key === '3')) {
            const map = { '1': 'cash', '2': 'card', '3': 'upi' };
            selectMethod(map[event.key]);
        }
    });

    renderLines();
})();
</script>
@endsection
