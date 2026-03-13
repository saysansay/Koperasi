<div class="card panel"><div class="card-body row g-3">
    <div class="col-md-6"><label class="form-label">{{ __('app.loans') }}</label><select class="form-select" name="loan_id">@foreach($loans as $loanOption)<option value="{{ $loanOption->id }}" @selected(old('loan_id', $installment->loan_id ?? '') == $loanOption->id)>{{ $loanOption->application_number }} - {{ $loanOption->member->name }}</option>@endforeach</select></div>
    <div class="col-md-3"><label class="form-label">{{ __('app.payment_date') }}</label><input type="date" class="form-control" name="payment_date" value="{{ old('payment_date', isset($installment) ? $installment->payment_date?->format('Y-m-d') : now()->format('Y-m-d')) }}" required></div>
    <div class="col-md-3"><label class="form-label">{{ __('app.payment_amount') }}</label><input type="number" step="0.01" class="form-control" name="payment_amount" value="{{ old('payment_amount', $installment->payment_amount ?? '') }}" required></div>
    <div class="col-12"><label class="form-label">{{ __('app.notes') }}</label><textarea class="form-control" rows="3" name="notes">{{ old('notes', $installment->notes ?? '') }}</textarea></div>
    <div class="col-12 d-flex gap-2"><button class="btn btn-primary">{{ __('app.save') }}</button><a class="btn btn-light" href="{{ route('installments.index') }}">{{ __('app.cancel') }}</a></div>
</div></div>
