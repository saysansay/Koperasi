<?php

use App\Models\Account;
use App\Models\InstallmentPayment;
use App\Models\JournalEntry;
use App\Models\Loan;
use App\Models\Member;
use App\Models\SalePayment;
use App\Models\SavingTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('v1')->group(function () {
    Route::get('dashboard', fn () => response()->json([
        'members' => Member::count(),
        'savings' => SavingTransaction::sum('amount'),
        'active_loans' => Loan::whereIn('status', ['approved', 'active'])->count(),
        'installments' => InstallmentPayment::count(),
        'sales_payments' => SalePayment::count(),
    ]));

    Route::get('members', fn () => Member::latest()->paginate(15));
    Route::get('savings', fn () => SavingTransaction::with(['member', 'savingType'])->latest('transaction_date')->paginate(15));
    Route::get('sale-payments', fn () => SalePayment::with('member')->latest('payment_date')->paginate(15));
    Route::get('loans', fn () => Loan::with(['member', 'loanType'])->latest('application_date')->paginate(15));
    Route::get('installments', fn () => InstallmentPayment::with('loan.member')->latest('payment_date')->paginate(15));
    Route::get('accounts', fn () => Account::orderBy('code')->get());
    Route::get('journals', fn () => JournalEntry::with('lines.account')->latest('entry_date')->paginate(15));
    Route::get('users', fn () => User::with(['role', 'member'])->latest()->paginate(15));

    Route::get('summary', fn () => response()->json([
        'members' => Member::latest()->take(5)->get(),
        'loans' => Loan::with('member')->latest()->take(5)->get(),
        'sales_payments' => SalePayment::with('member')->latest()->take(5)->get(),
        'accounts' => Account::orderBy('code')->get(),
        'users' => User::with('role')->latest()->take(5)->get(),
    ]));
});
