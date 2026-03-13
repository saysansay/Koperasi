@extends('layouts.app')
@section('title', __('app.ledger'))
@section('content')
<div class="card panel"><div class="card-body"><h5>{{ $account->code }} - {{ $account->name }}</h5><div class="table-responsive mt-3"><table class="table"><thead><tr><th>{{ __('app.date') }}</th><th>{{ __('app.reference') }}</th><th>{{ __('app.description') }}</th><th>{{ __('app.debit') }}</th><th>{{ __('app.credit') }}</th></tr></thead><tbody>@forelse($account->journalLines as $line)<tr><td>{{ $line->journalEntry->entry_date->format('Y-m-d') }}</td><td>{{ $line->journalEntry->reference_no }}</td><td>{{ $line->journalEntry->description }}</td><td>{{ number_format($line->debit, 0) }}</td><td>{{ number_format($line->credit, 0) }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted">{{ __('app.no_ledger_entries') }}</td></tr>@endforelse</tbody></table></div></div></div>
@endsection
