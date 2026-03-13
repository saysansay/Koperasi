@extends('layouts.app')
@section('title', __('app.change_password'))
@section('content')
<form method="POST" action="{{ route('profile.password.update') }}" class="row g-4">
    @csrf
    @method('PUT')
    <div class="col-lg-6">
        <div class="card panel">
            <div class="card-body row g-3">
                <div class="col-12">
                    <label class="form-label">{{ __('app.current_password') }}</label>
                    <input type="password" class="form-control" name="current_password" required>
                </div>
                <div class="col-12">
                    <label class="form-label">{{ __('app.new_password') }}</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <div class="col-12">
                    <label class="form-label">{{ __('app.confirm_new_password') }}</label>
                    <input type="password" class="form-control" name="password_confirmation" required>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-primary">{{ __('app.update_password') }}</button>
                    <a class="btn btn-light" href="{{ route('profile.show') }}">{{ __('app.back_to_profile') }}</a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
