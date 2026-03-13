<div class="card panel"><div class="card-body row g-3">
    <div class="col-md-4"><label class="form-label">{{ __('app.name') }}</label><input class="form-control" name="name" value="{{ old('name', $user->name ?? '') }}" required></div>
    <div class="col-md-4"><label class="form-label">{{ __('app.email') }}</label><input type="email" class="form-control" name="email" value="{{ old('email', $user->email ?? '') }}" required></div>
    <div class="col-md-4"><label class="form-label">{{ __('app.password') }}</label><input type="password" class="form-control" name="password" {{ isset($user) ? '' : 'required' }}></div>
    <div class="col-md-4"><label class="form-label">{{ __('app.role') }}</label><select class="form-select" name="role_id">@foreach($roles as $role)<option value="{{ $role->id }}" @selected(old('role_id', $user->role_id ?? '') == $role->id)>{{ $role->name }}</option>@endforeach</select></div>
    <div class="col-md-4"><label class="form-label">{{ __('app.linked_member') }}</label><select class="form-select" name="member_id"><option value="">- {{ __('app.none') }} -</option>@foreach($members as $member)<option value="{{ $member->id }}" @selected(old('member_id', $user->member_id ?? '') == $member->id)>{{ $member->member_id }} - {{ $member->name }}</option>@endforeach</select></div>
    <div class="col-md-4 form-check ms-1 mt-5"><input type="checkbox" class="form-check-input" id="user_active" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true))><label class="form-check-label" for="user_active">{{ __('app.active') }}</label></div>
    <div class="col-12 d-flex gap-2"><button class="btn btn-primary">{{ __('app.save') }}</button><a class="btn btn-light" href="{{ route('users.index') }}">{{ __('app.cancel') }}</a></div>
</div></div>
