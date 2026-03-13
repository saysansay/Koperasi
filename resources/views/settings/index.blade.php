@extends('layouts.app')
@section('title', __('app.system_settings'))
@section('content')
<div class="row g-4">
    <div class="col-xl-7">
        <form method="POST" action="{{ route('settings.update') }}">
            @csrf
            @method('PUT')
            <div class="card panel">
                <div class="card-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ __('app.cooperative_profile') }}</label>
                        <input class="form-control" name="name" value="{{ old('name', $setting->name ?? 'Koperasi Demo') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('app.email') }}</label>
                        <input type="email" class="form-control" name="email" value="{{ old('email', $setting->email ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('app.phone') }}</label>
                        <input class="form-control" name="phone" value="{{ old('phone', $setting->phone ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('app.default_interest_rate') }}</label>
                        <input type="number" step="0.01" class="form-control" name="default_interest_rate" value="{{ old('default_interest_rate', $setting->default_interest_rate ?? 1.5) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('app.currency') }}</label>
                        <input class="form-control" name="currency" value="{{ old('currency', $setting->currency ?? 'IDR') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __('app.address') }}</label>
                        <textarea class="form-control" rows="3" name="address">{{ old('address', $setting->address ?? '') }}</textarea>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary">{{ __('app.save_settings') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="col-xl-5">
        <div class="card panel mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">{{ __('app.saving_types') }}</h5>
                    <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#savingTypeCreateBox" aria-expanded="false" aria-controls="savingTypeCreateBox">
                        {{ __('app.new_entry') }}
                    </button>
                </div>

                <div class="collapse mb-3" id="savingTypeCreateBox">
                    <div class="border rounded p-3 bg-light">
                        <form method="POST" action="{{ route('settings.saving-types.store') }}" class="row g-2">
                            @csrf
                            <div class="col-md-8">
                                <label class="form-label">{{ __('app.name') }}</label>
                                <input class="form-control" name="name" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('app.code') }}</label>
                                <input class="form-control" name="code" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ __('app.description') }}</label>
                                <textarea class="form-control" rows="2" name="description"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('app.default_amount') }}</label>
                                <input type="number" step="0.01" class="form-control" name="default_amount" value="0" required>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="savingTypeCreateActive" checked>
                                    <label class="form-check-label" for="savingTypeCreateActive">{{ __('app.active') }}</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary">{{ __('app.save') }}</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="d-flex flex-column gap-3">
                    @forelse($savingTypes as $type)
                        <form method="POST" action="{{ route('settings.saving-types.update', $type) }}" class="border rounded p-3 bg-light row g-2">
                            @csrf
                            @method('PUT')
                            <div class="col-md-8">
                                <label class="form-label">{{ __('app.name') }}</label>
                                <input class="form-control" name="name" value="{{ $type->name }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('app.code') }}</label>
                                <input class="form-control" name="code" value="{{ $type->code }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ __('app.description') }}</label>
                                <textarea class="form-control" rows="2" name="description">{{ $type->description }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('app.default_amount') }}</label>
                                <input type="number" step="0.01" class="form-control" name="default_amount" value="{{ $type->default_amount }}" required>
                            </div>
                            <div class="col-md-6 d-flex align-items-end justify-content-between">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="savingTypeActive{{ $type->id }}" @checked($type->is_active)>
                                    <label class="form-check-label" for="savingTypeActive{{ $type->id }}">{{ __('app.active') }}</label>
                                </div>
                                <button class="btn btn-sm btn-outline-primary">{{ __('app.edit') }}</button>
                            </div>
                        </form>
                    @empty
                        <div class="text-muted">{{ __('app.no_saving_types') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="card panel">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">{{ __('app.loan_types') }}</h5>
                    <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#loanTypeCreateBox" aria-expanded="false" aria-controls="loanTypeCreateBox">
                        {{ __('app.new_entry') }}
                    </button>
                </div>

                <div class="collapse mb-3" id="loanTypeCreateBox">
                    <div class="border rounded p-3 bg-light">
                        <form method="POST" action="{{ route('settings.loan-types.store') }}" class="row g-2">
                            @csrf
                            <div class="col-md-8">
                                <label class="form-label">{{ __('app.name') }}</label>
                                <input class="form-control" name="name" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('app.code') }}</label>
                                <input class="form-control" name="code" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('app.default_interest_rate') }}</label>
                                <input type="number" step="0.01" class="form-control" name="default_interest_rate" value="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('app.default_period_months') }}</label>
                                <input type="number" class="form-control" name="default_period_months" value="12" min="1" required>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="loanTypeCreateActive" checked>
                                    <label class="form-check-label" for="loanTypeCreateActive">{{ __('app.active') }}</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary">{{ __('app.save') }}</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="d-flex flex-column gap-3">
                    @forelse($loanTypes as $type)
                        <form method="POST" action="{{ route('settings.loan-types.update', $type) }}" class="border rounded p-3 bg-light row g-2">
                            @csrf
                            @method('PUT')
                            <div class="col-md-8">
                                <label class="form-label">{{ __('app.name') }}</label>
                                <input class="form-control" name="name" value="{{ $type->name }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('app.code') }}</label>
                                <input class="form-control" name="code" value="{{ $type->code }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('app.default_interest_rate') }}</label>
                                <input type="number" step="0.01" class="form-control" name="default_interest_rate" value="{{ $type->default_interest_rate }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('app.default_period_months') }}</label>
                                <input type="number" class="form-control" name="default_period_months" value="{{ $type->default_period_months }}" min="1" required>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="loanTypeActive{{ $type->id }}" @checked($type->is_active)>
                                    <label class="form-check-label" for="loanTypeActive{{ $type->id }}">{{ __('app.active') }}</label>
                                </div>
                            </div>
                            <div class="col-md-6 d-flex align-items-end justify-content-end">
                                <button class="btn btn-sm btn-outline-primary">{{ __('app.edit') }}</button>
                            </div>
                        </form>
                    @empty
                        <div class="text-muted">{{ __('app.no_loan_types') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
