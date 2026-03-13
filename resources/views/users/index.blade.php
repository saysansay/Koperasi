@extends('layouts.app')
@section('title', __('app.user_management'))
@section('content')
<div class="d-flex justify-content-end mb-3"><a class="btn btn-primary" href="{{ route('users.create') }}">{{ __('app.add_user') }}</a></div>
<div class="card panel"><div class="card-body table-responsive"><table class="table table-hover"><thead><tr><th>{{ __('app.name') }}</th><th>{{ __('app.email') }}</th><th>{{ __('app.role') }}</th><th>{{ __('app.member') }}</th><th>{{ __('app.status') }}</th><th></th></tr></thead><tbody>@forelse($users as $user)<tr><td>{{ $user->name }}</td><td>{{ $user->email }}</td><td>{{ $user->role?->name }}</td><td>{{ $user->member?->name ?? '-' }}</td><td>{{ $user->is_active ? __('app.active') : __('app.inactive') }}</td><td class="text-end d-flex gap-2 justify-content-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('users.edit', $user) }}">{{ __('app.edit') }}</a><form method="POST" action="{{ route('users.destroy', $user) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" onclick="return confirm('{{ __('app.delete') }}?')">{{ __('app.delete') }}</button></form></td></tr>@empty<tr><td colspan="6" class="text-center text-muted">{{ __('app.no_users_found') }}</td></tr>@endforelse</tbody></table></div></div>
<div class="mt-3">{{ $users->links() }}</div>
@endsection
