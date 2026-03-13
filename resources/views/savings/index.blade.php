@extends('layouts.app')
@section('title', __('app.savings_management'))
@section('content')
<div class="row g-4 mb-4">
    @foreach($balances as $member)
        <div class="col-md-6 col-xl-3"><div class="card stat-card"><div class="card-body"><div class="small text-muted">{{ $member->name }}</div><div class="fw-semibold">{{ __('app.balance') }}</div><div>Rp {{ number_format(($member->deposits_sum ?? 0) - ($member->withdrawals_sum ?? 0), 0) }}</div></div></div></div>
    @endforeach
</div>
<div class="d-flex justify-content-end mb-3"><a class="btn btn-primary" href="{{ route('savings.create') }}">{{ __('app.add_transaction') }}</a></div>
<div class="card panel"><div class="card-body table-responsive"><table class="table table-hover"><thead><tr><th>{{ __('app.date') }}</th><th>{{ __('app.member') }}</th><th>{{ __('app.savings_type') }}</th><th>{{ __('app.transaction_type') }}</th><th class="text-end">{{ __('app.amount') }}</th><th></th></tr></thead><tbody>@forelse($transactions as $saving)<tr><td>{{ $saving->transaction_date->format('Y-m-d') }}</td><td>{{ $saving->member->name }}</td><td>{{ $saving->savingType->name }}</td><td>{{ $saving->transaction_type === 'deposit' ? __('app.deposit') : __('app.withdrawal') }}</td><td class="text-end">Rp {{ number_format($saving->amount, 0) }}</td><td class="text-end d-flex gap-2 justify-content-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('savings.edit', $saving) }}">{{ __('app.edit') }}</a><form method="POST" action="{{ route('savings.destroy', $saving) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" onclick="return confirm('{{ __('app.delete') }}?')">{{ __('app.delete') }}</button></form></td></tr>@empty<tr><td colspan="6" class="text-center text-muted">{{ __('app.no_transactions_found') }}</td></tr>@endforelse</tbody></table></div></div>
<div class="mt-3">{{ $transactions->links() }}</div>
@endsection
