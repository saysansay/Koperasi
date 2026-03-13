<?php

namespace App\Http\Controllers;

use App\Models\InstallmentPayment;
use App\Models\SalePayment;
use App\Models\SavingTransaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberTransactionController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->member_id, 403);

        $salePayments = SalePayment::with('member')
            ->where('member_id', $user->member_id)
            ->latest('payment_date')
            ->get();

        $savings = SavingTransaction::with('savingType')
            ->where('member_id', $user->member_id)
            ->latest('transaction_date')
            ->get();

        $installments = InstallmentPayment::with('loan')
            ->whereHas('loan', fn ($loan) => $loan->where('member_id', $user->member_id))
            ->latest('payment_date')
            ->get();

        return view('member-transactions.index', [
            'salePayments' => $salePayments,
            'savings' => $savings,
            'installments' => $installments,
            'summary' => [
                'sales_count' => $salePayments->count(),
                'sales_total' => $salePayments->sum('payment_amount'),
                'savings_total' => $savings->reduce(fn ($carry, $item) => $carry + ($item->transaction_type === 'deposit' ? $item->amount : -$item->amount), 0),
                'installment_total' => $installments->sum('payment_amount'),
            ],
        ]);
    }
}
