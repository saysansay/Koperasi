<h3>{{ __('app.financial_statement') }}</h3>
<table border="1" style="margin-bottom: 20px;">
    <thead>
        <tr>
            <th>{{ __('app.total_income') }}</th>
            <th>{{ __('app.total_expense') }}</th>
            <th>{{ __('app.net_profit') }}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $summary['income'] }}</td>
            <td>{{ $summary['expense'] }}</td>
            <td>{{ $summary['income'] - $summary['expense'] }}</td>
        </tr>
    </tbody>
</table>

<h4>{{ __('app.profit_loss') }}</h4>
<table border="1" style="margin-bottom: 20px;">
    <thead>
        <tr>
            <th>{{ __('app.account') }}</th>
            <th>{{ __('app.debit') }}</th>
            <th>{{ __('app.credit') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($profitLoss as $account)
            <tr>
                <td>{{ $account->code }} - {{ $account->name }}</td>
                <td>{{ $account->debit_total ?? 0 }}</td>
                <td>{{ $account->credit_total ?? 0 }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<h4>{{ __('app.balance_sheet') }}</h4>
<table border="1">
    <thead>
        <tr>
            <th>{{ __('app.account') }}</th>
            <th>{{ __('app.debit') }}</th>
            <th>{{ __('app.credit') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($balanceSheet as $account)
            <tr>
                <td>{{ $account->code }} - {{ $account->name }}</td>
                <td>{{ $account->debit_total ?? 0 }}</td>
                <td>{{ $account->credit_total ?? 0 }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
