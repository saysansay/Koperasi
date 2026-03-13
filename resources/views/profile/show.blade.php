@extends('layouts.app')
@section('title', __('app.profile'))
@section('content')
<form method="POST" action="{{ route('profile.update') }}" class="row g-4">
    @csrf
    @method('PUT')
    <div class="col-lg-7">
        <div class="card panel">
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.name') }}</label>
                    <input class="form-control" name="name" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.email') }}</label>
                    <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.role') }}</label>
                    <input class="form-control" value="{{ $user->role?->name ?? '-' }}" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.linked_member') }}</label>
                    <input class="form-control" value="{{ $user->member?->name ?? '-' }}" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.last_login') }}</label>
                    <input class="form-control" value="{{ $user->last_login_at?->format('Y-m-d H:i') ?? '-' }}" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.status') }}</label>
                    <input class="form-control" value="{{ $user->is_active ? __('app.active') : __('app.inactive') }}" disabled>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-primary">{{ __('app.save_profile') }}</button>
                    <a class="btn btn-light" href="{{ route('profile.password.edit') }}">{{ __('app.change_password') }}</a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
