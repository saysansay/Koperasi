@extends('layouts.app')
@section('title', __('app.journal_entries'))
@section('content')
<div class="d-flex justify-content-end mb-3"><a class="btn btn-primary" href="{{ route('journals.create') }}">{{ __('app.add_journal') }}</a></div>
<div class="card panel"><div class="card-body table-responsive"><table class="table table-hover"><thead><tr><th>{{ __('app.date') }}</th><th>{{ __('app.reference') }}</th><th>{{ __('app.description') }}</th><th>{{ __('app.amount') }}</th><th></th></tr></thead><tbody>@forelse($entries as $entry)<tr><td>{{ $entry->entry_date->format('Y-m-d') }}</td><td>{{ $entry->reference_no }}</td><td>{{ $entry->description }}</td><td>Rp {{ number_format($entry->total_amount, 0) }}</td><td class="text-end d-flex gap-2 justify-content-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('journals.edit', $entry) }}">{{ __('app.edit') }}</a><form method="POST" action="{{ route('journals.destroy', $entry) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" onclick="return confirm('{{ __('app.delete') }}?')">{{ __('app.delete') }}</button></form></td></tr>@empty<tr><td colspan="5" class="text-center text-muted">{{ __('app.no_journal_entries_found') }}</td></tr>@endforelse</tbody></table></div></div>
<div class="mt-3">{{ $entries->links() }}</div>
@endsection
