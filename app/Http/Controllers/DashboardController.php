<?php

namespace App\Http\Controllers;

use App\Models\InstallmentPayment;
use App\Models\Loan;
use App\Models\Member;
use App\Models\NotificationItem;
use App\Models\SavingTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $monthExpression = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', transaction_date)"
            : "DATE_FORMAT(transaction_date, '%Y-%m')";

        $loanMonthExpression = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', application_date)"
            : "DATE_FORMAT(application_date, '%Y-%m')";

        $totalSavings = SavingTransaction::selectRaw("COALESCE(SUM(CASE WHEN transaction_type = 'deposit' THEN amount ELSE -amount END), 0) as total")->value('total');
        $monthlyIncome = InstallmentPayment::whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('payment_amount');

        $savingsTrend = SavingTransaction::selectRaw($monthExpression.' as month, SUM(CASE WHEN transaction_type = "deposit" THEN amount ELSE -amount END) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->limit(6)
            ->get();

        $loanTrend = Loan::selectRaw($loanMonthExpression.' as month, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->limit(6)
            ->get();

        $latestTransactions = collect()
            ->merge(SavingTransaction::with('member')->latest('transaction_date')->take(5)->get()->map(fn ($item) => [
                'date' => $item->transaction_date?->format('Y-m-d'),
                'label' => 'Savings: '.$item->member->name,
                'amount' => $item->amount,
                'type' => ucfirst($item->transaction_type),
            ]))
            ->merge(InstallmentPayment::with('loan.member')->latest('payment_date')->take(5)->get()->map(fn ($item) => [
                'date' => $item->payment_date?->format('Y-m-d'),
                'label' => 'Installment: '.$item->loan->member->name,
                'amount' => $item->payment_amount,
                'type' => 'Payment',
            ]))
            ->sortByDesc('date')
            ->take(8);

        return view('dashboard.index', [
            'stats' => [
                'total_members' => Member::count(),
                'total_savings' => $totalSavings,
                'active_loans' => Loan::whereIn('status', ['approved', 'active'])->count(),
                'monthly_income' => $monthlyIncome,
            ],
            'savingsTrend' => $savingsTrend,
            'loanTrend' => $loanTrend,
            'notifications' => NotificationItem::latest()->take(5)->get(),
            'latestTransactions' => $latestTransactions,
            'statusBreakdown' => Loan::select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status'),
        ]);
    }
}
