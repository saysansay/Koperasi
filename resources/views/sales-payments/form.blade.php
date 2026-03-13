<div class="row g-4 cashier-form">
    <div class="col-lg-7">
        <div class="card panel h-100">
            <div class="card-body row g-3">
                <div class="col-12">
                    <div class="cashier-title">{{ __('app.sales_payment_input') }}</div>
                    <div class="text-muted small">{{ __('app.cashier_mode') }}</div>
                </div>
                <div class="col-12">
                    <label class="form-label">{{ __('app.member_barcode') }}</label>
                    <input
                        type="text"
                        class="form-control cashier-scan-input"
                        id="member_barcode"
                        placeholder="{{ __('app.member_barcode') }}"
                        autocomplete="off"
                        @if(!isset($payment)) autofocus @endif
                    >
                    <div class="small text-muted mt-1" id="member_scan_hint">{{ __('app.member_scan_hint') }}</div>
                </div>
                <div class="col-md-7">
                    <label class="form-label">{{ __('app.member') }}</label>
                    <select class="form-select" name="member_id" id="member_id" required>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}" data-member-code="{{ $member->member_id }}" @selected(old('member_id', $payment->member_id ?? '') == $member->id)>{{ $member->member_id }} - {{ $member->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">{{ __('app.invoice_number') }}</label>
                    <input class="form-control" name="invoice_number" value="{{ old('invoice_number', $payment->invoice_number ?? 'INV-'.now()->format('YmdHis')) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.sale_date') }}</label>
                    <input type="date" class="form-control" name="sale_date" value="{{ old('sale_date', isset($payment) ? $payment->sale_date?->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.payment_date') }}</label>
                    <input type="date" class="form-control" name="payment_date" value="{{ old('payment_date', isset($payment) ? $payment->payment_date?->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.sale_amount') }}</label>
                    <input type="number" step="0.01" class="form-control cashier-input" id="sale_amount" name="sale_amount" value="{{ old('sale_amount', $payment->sale_amount ?? '') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.payment_amount') }}</label>
                    <input type="number" step="0.01" class="form-control cashier-input" id="payment_amount" name="payment_amount" value="{{ old('payment_amount', $payment->payment_amount ?? '') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.payment_method') }}</label>
                    <select class="form-select" name="payment_method">
                        <option value="cash" @selected(old('payment_method', $payment->payment_method ?? 'cash') === 'cash')>{{ __('app.cash') }}</option>
                        <option value="transfer" @selected(old('payment_method', $payment->payment_method ?? '') === 'transfer')>{{ __('app.transfer') }}</option>
                        <option value="payroll" @selected(old('payment_method', $payment->payment_method ?? '') === 'payroll')>{{ __('app.payroll_deduction') }}</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.quick_payment') }}</label>
                    <div class="d-grid gap-2 quick-pay-grid">
                        <button type="button" class="btn btn-light quick-pay-btn" data-amount="50000">50.000</button>
                        <button type="button" class="btn btn-light quick-pay-btn" data-amount="100000">100.000</button>
                        <button type="button" class="btn btn-light quick-pay-btn" data-amount="250000">250.000</button>
                        <button type="button" class="btn btn-light quick-pay-btn" data-amount="full">{{ __('app.paid_off') }}</button>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">{{ __('app.notes') }}</label>
                    <textarea class="form-control" rows="3" name="notes">{{ old('notes', $payment->notes ?? '') }}</textarea>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-primary">{{ __('app.save_payment') }}</button>
                    <a class="btn btn-light" href="{{ route('sale-payments.index') }}">{{ __('app.cancel') }}</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card panel cashier-summary h-100">
            <div class="card-body">
                <div class="cashier-screen-label">{{ __('app.cashier_display') }}</div>
                <div class="cashier-screen-value" id="display_payment">Rp 0</div>
                <div class="cashier-meta">
                    <div class="cashier-meta-row">
                        <span>{{ __('app.total_sale') }}</span>
                        <strong id="display_sale">Rp 0</strong>
                    </div>
                    <div class="cashier-meta-row">
                        <span>{{ __('app.paid') }}</span>
                        <strong id="display_paid">Rp 0</strong>
                    </div>
                    <div class="cashier-meta-row outstanding">
                        <span>{{ __('app.remaining') }}</span>
                        <strong id="display_balance">Rp 0</strong>
                    </div>
                </div>
                <div class="cashier-hint mt-3">
                    {{ __('app.cashier_hint') }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (() => {
        const saleInput = document.getElementById('sale_amount');
        const paymentInput = document.getElementById('payment_amount');
        const memberSelect = document.getElementById('member_id');
        const memberBarcode = document.getElementById('member_barcode');
        const memberHint = document.getElementById('member_scan_hint');
        const displaySale = document.getElementById('display_sale');
        const displayPaid = document.getElementById('display_paid');
        const displayBalance = document.getElementById('display_balance');
        const displayPayment = document.getElementById('display_payment');
        const quickButtons = document.querySelectorAll('.quick-pay-btn');

        const toNumber = (value) => Number.parseFloat(value || 0) || 0;
        const toCurrency = (value) => 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
        const normalize = (value) => String(value || '').trim().toUpperCase();

        const updateDisplay = () => {
            const sale = toNumber(saleInput?.value);
            const paid = toNumber(paymentInput?.value);
            const balance = Math.max(sale - paid, 0);

            if (displaySale) displaySale.textContent = toCurrency(sale);
            if (displayPaid) displayPaid.textContent = toCurrency(paid);
            if (displayBalance) displayBalance.textContent = toCurrency(balance);
            if (displayPayment) displayPayment.textContent = toCurrency(paid);
        };

        const syncMemberByBarcode = () => {
            if (!memberSelect || !memberBarcode) return;

            const scanned = normalize(memberBarcode.value);
            if (!scanned) {
                if (memberHint) memberHint.textContent = @json(__('app.member_scan_hint'));
                return;
            }

            const option = Array.from(memberSelect.options).find((item) =>
                normalize(item.dataset.memberCode) === scanned
            );

            if (!option) {
                if (memberHint) memberHint.textContent = @json(__('app.member_not_found'));
                return;
            }

            memberSelect.value = option.value;
            if (memberHint) memberHint.textContent = @json(__('app.member_selected')) + ': ' + option.textContent;
        };

        saleInput?.addEventListener('input', updateDisplay);
        paymentInput?.addEventListener('input', updateDisplay);
        memberBarcode?.addEventListener('change', syncMemberByBarcode);
        memberBarcode?.addEventListener('keyup', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                syncMemberByBarcode();
                document.querySelector('input[name=\"invoice_number\"]')?.focus();
            }
        });

        quickButtons.forEach((button) => {
            button.addEventListener('click', () => {
                if (!paymentInput) return;
                paymentInput.value = button.dataset.amount === 'full'
                    ? toNumber(saleInput?.value)
                    : button.dataset.amount;
                updateDisplay();
                paymentInput.focus();
            });
        });

        updateDisplay();
        syncMemberByBarcode();
    })();
</script>
@endpush
