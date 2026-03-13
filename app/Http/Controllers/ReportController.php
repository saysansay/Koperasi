<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\InstallmentPayment;
use App\Models\JournalLine;
use App\Models\Loan;
use App\Models\Member;
use App\Models\SavingTransaction;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function members(): View
    {
        return view('reports.members', ['members' => $this->membersData()]);
    }

    public function savings(): View
    {
        return view('reports.savings', ['transactions' => $this->savingsData()]);
    }

    public function loans(): View
    {
        return view('reports.loans', ['loans' => $this->loansData()]);
    }

    public function installments(): View
    {
        return view('reports.installments', ['payments' => $this->installmentsData()]);
    }

    public function financial(): View
    {
        return view('reports.financial', $this->financialData());
    }

    public function export(string $report): Response
    {
        return match ($report) {
            'members' => $this->exportMembers(),
            'savings' => $this->exportSavings(),
            'loans' => $this->exportLoans(),
            'installments' => $this->exportInstallments(),
            'financial' => $this->exportFinancial(),
            default => abort(404),
        };
    }

    private function exportMembers(): Response
    {
        return $this->excelResponse(
            'member-report',
            view('reports.exports.table', [
                'title' => __('app.member_report'),
                'headers' => [__('app.member_id'), __('app.name'), __('app.ktp'), __('app.phone'), __('app.status')],
                'rows' => $this->membersData()->map(fn ($member) => [
                    $member->member_id,
                    $member->name,
                    $member->ktp_number,
                    $member->phone_number,
                    $member->status === 'active' ? __('app.active') : ($member->status === 'inactive' ? __('app.inactive') : __('app.suspended')),
                ]),
            ])->render()
        );
    }

    private function exportSavings(): Response
    {
        return $this->excelResponse(
            'savings-report',
            view('reports.exports.table', [
                'title' => __('app.savings_report'),
                'headers' => [__('app.date'), __('app.member'), __('app.savings_type'), __('app.transaction_type'), __('app.amount')],
                'rows' => $this->savingsData()->map(fn ($transaction) => [
                    $transaction->transaction_date->format('Y-m-d'),
                    $transaction->member->name,
                    $transaction->savingType->name,
                    $transaction->transaction_type === 'deposit' ? __('app.deposit') : __('app.withdrawal'),
                    $transaction->amount,
                ]),
            ])->render()
        );
    }

    private function exportLoans(): Response
    {
        return $this->excelResponse(
            'loan-report',
            view('reports.exports.table', [
                'title' => __('app.loan_report'),
                'headers' => [__('app.application_no'), __('app.member'), __('app.type'), __('app.amount'), __('app.status'), __('app.remaining')],
                'rows' => $this->loansData()->map(fn ($loan) => [
                    $loan->application_number,
                    $loan->member->name,
                    $loan->loanType->name,
                    $loan->amount,
                    __('app.'.$loan->status),
                    $loan->remaining_balance,
                ]),
            ])->render()
        );
    }

    private function exportInstallments(): Response
    {
        return $this->excelResponse(
            'installment-report',
            view('reports.exports.table', [
                'title' => __('app.installment_report'),
                'headers' => [__('app.date'), __('app.loans'), __('app.member'), __('app.amount'), __('app.remaining')],
                'rows' => $this->installmentsData()->map(fn ($payment) => [
                    $payment->payment_date->format('Y-m-d'),
                    $payment->loan->application_number,
                    $payment->loan->member->name,
                    $payment->payment_amount,
                    $payment->remaining_balance,
                ]),
            ])->render()
        );
    }

    private function exportFinancial(): Response
    {
        return $this->excelResponse(
            'financial-statement',
            view('reports.exports.financial', $this->financialData())->render()
        );
    }

    private function excelResponse(string $filename, string $content): Response
    {
        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'-'.now()->format('YmdHis').'.xls"',
        ]);
    }

    private function membersData()
    {
        return Member::latest()->get();
    }

    private function savingsData()
    {
        return SavingTransaction::with(['member', 'savingType'])->latest('transaction_date')->get();
    }

    private function loansData()
    {
        return Loan::with(['member', 'loanType'])->latest('application_date')->get();
    }

    private function installmentsData()
    {
        return InstallmentPayment::with('loan.member')->latest('payment_date')->get();
    }

    private function financialData(): array
    {
        $profitLoss = Account::query()
            ->select('accounts.*')
            ->withSum(['journalLines as debit_total' => fn ($q) => $q], 'debit')
            ->withSum(['journalLines as credit_total' => fn ($q) => $q], 'credit')
            ->whereIn('category', ['income', 'expense'])
            ->get();

        $balanceSheet = Account::query()
            ->select('accounts.*')
            ->withSum(['journalLines as debit_total' => fn ($q) => $q], 'debit')
            ->withSum(['journalLines as credit_total' => fn ($q) => $q], 'credit')
            ->whereIn('category', ['asset', 'liability', 'equity'])
            ->get();

        $summary = [
            'income' => (float) JournalLine::whereHas('account', fn ($q) => $q->where('category', 'income'))->sum('credit'),
            'expense' => (float) JournalLine::whereHas('account', fn ($q) => $q->where('category', 'expense'))->sum('debit'),
            'assets' => (float) DB::table('journal_lines')->join('accounts', 'accounts.id', '=', 'journal_lines.account_id')->where('accounts.category', 'asset')->sum(DB::raw('debit - credit')),
            'liabilities' => (float) DB::table('journal_lines')->join('accounts', 'accounts.id', '=', 'journal_lines.account_id')->where('accounts.category', 'liability')->sum(DB::raw('credit - debit')),
            'equity' => (float) DB::table('journal_lines')->join('accounts', 'accounts.id', '=', 'journal_lines.account_id')->where('accounts.category', 'equity')->sum(DB::raw('credit - debit')),
        ];

        return compact('profitLoss', 'balanceSheet', 'summary');
    }
}
