@extends('layouts.app')
@section('title', __('app.system_settings'))
@section('content')
<form method="POST" action="{{ route('settings.update') }}" class="row g-4">@csrf @method('PUT')
    <div class="col-lg-7"><div class="card panel"><div class="card-body row g-3">
        <div class="col-md-6"><label class="form-label">{{ __('app.cooperative_profile') }}</label><input class="form-control" name="name" value="{{ old('name', $setting->name ?? 'Koperasi Demo') }}"></div>
        <div class="col-md-6"><label class="form-label">{{ __('app.email') }}</label><input type="email" class="form-control" name="email" value="{{ old('email', $setting->email ?? '') }}"></div>
        <div class="col-md-6"><label class="form-label">{{ __('app.phone') }}</label><input class="form-control" name="phone" value="{{ old('phone', $setting->phone ?? '') }}"></div>
        <div class="col-md-6"><label class="form-label">{{ __('app.default_interest_rate') }}</label><input type="number" step="0.01" class="form-control" name="default_interest_rate" value="{{ old('default_interest_rate', $setting->default_interest_rate ?? 1.5) }}"></div>
        <div class="col-md-4"><label class="form-label">{{ __('app.currency') }}</label><input class="form-control" name="currency" value="{{ old('currency', $setting->currency ?? 'IDR') }}"></div>
        <div class="col-12"><label class="form-label">{{ __('app.address') }}</label><textarea class="form-control" rows="3" name="address">{{ old('address', $setting->address ?? '') }}</textarea></div>
        <div class="col-12"><button class="btn btn-primary">{{ __('app.save_settings') }}</button></div>
    </div></div></div>
    <div class="col-lg-5">
        <div class="card panel mb-4"><div class="card-body"><h5>{{ __('app.saving_types') }}</h5><ul class="list-group list-group-flush">@forelse($savingTypes as $type)<li class="list-group-item d-flex justify-content-between"><span>{{ $type->name }}</span><span>Rp {{ number_format($type->default_amount, 0) }}</span></li>@empty<li class="list-group-item text-muted">{{ __('app.no_saving_types') }}</li>@endforelse</ul></div></div>
        <div class="card panel"><div class="card-body"><h5>{{ __('app.loan_types') }}</h5><ul class="list-group list-group-flush">@forelse($loanTypes as $type)<li class="list-group-item d-flex justify-content-between"><span>{{ $type->name }}</span><span>{{ $type->default_interest_rate }}%</span></li>@empty<li class="list-group-item text-muted">{{ __('app.no_loan_types') }}</li>@endforelse</ul></div></div>
    </div>
</form>
@endsection
