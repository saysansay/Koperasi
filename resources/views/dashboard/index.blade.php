@extends('layouts.app')
@section('title', __('app.dashboard'))
@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3"><div class="card stat-card h-100"><div class="card-body"><div class="text-muted">{{ __('app.total_members') }}</div><h2>{{ $stats['total_members'] }}</h2></div></div></div>
    <div class="col-md-6 col-xl-3"><div class="card stat-card h-100"><div class="card-body"><div class="text-muted">{{ __('app.total_savings') }}</div><h2>Rp {{ number_format($stats['total_savings'], 0) }}</h2></div></div></div>
    <div class="col-md-6 col-xl-3"><div class="card stat-card h-100"><div class="card-body"><div class="text-muted">{{ __('app.active_loans') }}</div><h2>{{ $stats['active_loans'] }}</h2></div></div></div>
    <div class="col-md-6 col-xl-3"><div class="card stat-card h-100"><div class="card-body"><div class="text-muted">{{ __('app.monthly_income') }}</div><h2>Rp {{ number_format($stats['monthly_income'], 0) }}</h2></div></div></div>
</div>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card panel mb-4"><div class="card-body"><h5>{{ __('app.savings_trend') }}</h5><canvas id="savingsChart" height="110"></canvas></div></div>
        <div class="card panel"><div class="card-body"><h5>{{ __('app.latest_transactions') }}</h5>
            <div class="table-responsive"><table class="table align-middle"><thead><tr><th>{{ __('app.date') }}</th><th>{{ __('app.transaction') }}</th><th>{{ __('app.type') }}</th><th class="text-end">{{ __('app.amount') }}</th></tr></thead><tbody>
                @forelse($latestTransactions as $item)
                    <tr><td>{{ $item['date'] }}</td><td>{{ $item['label'] }}</td><td>{{ $item['type'] }}</td><td class="text-end">Rp {{ number_format($item['amount'], 0) }}</td></tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted">{{ __('app.no_transactions_yet') }}</td></tr>
                @endforelse
            </tbody></table></div>
        </div></div>
    </div>
    <div class="col-lg-4">
        <div class="card panel mb-4"><div class="card-body"><h5>{{ __('app.loan_trend') }}</h5><canvas id="loanChart" height="160"></canvas></div></div>
        <div class="card panel"><div class="card-body"><h5>{{ __('app.notifications') }}</h5>
            @forelse($notifications as $notification)
                <div class="border rounded-3 p-3 mb-3"><div class="fw-semibold">{{ $notification->title }}</div><div class="small text-muted">{{ $notification->message }}</div></div>
            @empty
                <div class="text-muted">{{ __('app.no_notifications') }}</div>
            @endforelse
        </div></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
new Chart(document.getElementById('savingsChart'), {type:'line',data:{labels:@json($savingsTrend->pluck('month')),datasets:[{label:@json(__('app.savings')),data:@json($savingsTrend->pluck('total')),borderColor:'#0f4c5c',backgroundColor:'rgba(15,76,92,.15)',tension:.35,fill:true}]}});
new Chart(document.getElementById('loanChart'), {type:'bar',data:{labels:@json($loanTrend->pluck('month')),datasets:[{label:@json(__('app.loans')),data:@json($loanTrend->pluck('total')),backgroundColor:'#e36414'}]}});
</script>
@endpush
