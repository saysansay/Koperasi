@extends('layouts.app')
@section('title', __('app.members'))
@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
    <form class="d-flex gap-2" method="GET">
        <input class="form-control" name="search" value="{{ request('search') }}" placeholder="{{ __('app.search_member') }}">
        <button class="btn btn-outline-secondary">{{ __('app.search') }}</button>
    </form>
    <div class="d-flex flex-column flex-md-row gap-2">
        <a class="btn btn-light" href="{{ route('members.import-template') }}">{{ __('app.download_template') }}</a>
        <button class="btn btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#memberImportBox" aria-expanded="{{ $errors->has('import_file') ? 'true' : 'false' }}" aria-controls="memberImportBox">{{ __('app.import_members') }}</button>
        <a class="btn btn-primary" href="{{ route('members.create') }}">{{ __('app.add_member') }}</a>
    </div>
</div>
<div class="collapse mb-3 {{ $errors->has('import_file') ? 'show' : '' }}" id="memberImportBox">
    <div class="card panel">
        <div class="card-body">
            <div class="fw-bold mb-1">{{ __('app.import_members') }}</div>
            <div class="text-muted small mb-3">{{ __('app.member_import_help') }}</div>
            <form method="POST" action="{{ route('members.import') }}" enctype="multipart/form-data" class="row g-3 align-items-end">
                @csrf
                <div class="col-lg-6">
                    <label class="form-label">{{ __('app.import_file') }}</label>
                    <input type="file" name="import_file" class="form-control" accept=".xlsx,.csv,.txt" required>
                </div>
                <div class="col-lg-6 d-flex gap-2">
                    <button class="btn btn-primary">{{ __('app.import_now') }}</button>
                    <a class="btn btn-light" href="{{ route('members.import-template') }}">{{ __('app.download_template') }}</a>
                </div>
            </form>
            @if(session('import_errors'))
                <div class="alert alert-warning mt-3 mb-0">
                    <div class="fw-bold mb-2">{{ __('app.import_with_errors') }}</div>
                    <ul class="mb-0 ps-3">
                        @foreach(array_slice(session('import_errors'), 0, 5) as $error)
                            <li>{{ __('app.row_number', ['row' => $error['row']]) }}: {{ $error['message'] }}</li>
                        @endforeach
                    </ul>
                    @if(count(session('import_errors')) > 5)
                        <div class="small mt-2">{{ __('app.more_import_errors', ['count' => count(session('import_errors')) - 5]) }}</div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
<div class="card panel"><div class="card-body table-responsive"><table class="table table-hover"><thead><tr><th>{{ __('app.member_id') }}</th><th>{{ __('app.name') }}</th><th>{{ __('app.ktp') }}</th><th>{{ __('app.phone') }}</th><th>{{ __('app.join_date') }}</th><th>{{ __('app.status') }}</th><th></th></tr></thead><tbody>@forelse($members as $member)<tr><td>{{ $member->member_id }}</td><td>{{ $member->name }}</td><td>{{ $member->ktp_number }}</td><td>{{ $member->phone_number }}</td><td>{{ $member->join_date->format('Y-m-d') }}</td><td><span class="badge text-bg-secondary">{{ $member->status === 'active' ? __('app.active') : ($member->status === 'inactive' ? __('app.inactive') : __('app.suspended')) }}</span></td><td class="text-end d-flex gap-2 justify-content-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('members.edit', $member) }}">{{ __('app.edit') }}</a><form method="POST" action="{{ route('members.destroy', $member) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" onclick="return confirm('{{ __('app.delete') }}?')">{{ __('app.delete') }}</button></form></td></tr>@empty<tr><td colspan="7" class="text-center text-muted">{{ __('app.no_members_found') }}</td></tr>@endforelse</tbody></table></div></div>
<div class="mt-3">{{ $members->links() }}</div>
@endsection
