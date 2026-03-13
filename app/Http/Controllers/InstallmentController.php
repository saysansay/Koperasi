<?php

namespace App\Http\Controllers;

use App\Models\InstallmentPayment;
use App\Models\Loan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstallmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = InstallmentPayment::with('loan.member')->latest('payment_date');

        if ($request->user()->role?->slug === 'member' && $request->user()->member_id) {
            $query->whereHas('loan', fn ($loan) => $loan->where('member_id', $request->user()->member_id));
        }

        return view('installments.index', [
            'payments' => $query->paginate(10),
        ]);
    }

    public function create(): View
    {
        $user = auth()->user();
        $loans = Loan::with('member')->whereIn('status', ['approved', 'active'])->orderBy('application_date', 'desc');

        if ($user->role?->slug === 'member' && $user->member_id) {
            $loans->where('member_id', $user->member_id);
        }

        return view('installments.create', [
            'loans' => $loans->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'loan_id' => ['required', 'exists:loans,id'],
            'payment_date' => ['required', 'date'],
            'payment_amount' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string'],
        ]);

        $loan = Loan::findOrFail($data['loan_id']);
        abort_if($request->user()->role?->slug === 'member' && $request->user()->member_id !== $loan->member_id, 403);
        $nextNumber = $loan->payments()->max('installment_number') + 1;
        $remaining = max((float) $loan->remaining_balance - (float) $data['payment_amount'], 0);

        InstallmentPayment::create([
            'loan_id' => $loan->id,
            'payment_date' => $data['payment_date'],
            'installment_number' => $nextNumber,
            'payment_amount' => $data['payment_amount'],
            'remaining_balance' => $remaining,
            'notes' => $data['notes'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        $loan->update([
            'paid_amount' => $loan->paid_amount + $data['payment_amount'],
            'remaining_balance' => $remaining,
            'status' => $remaining <= 0 ? 'completed' : 'active',
        ]);

        return redirect()->route('installments.index')->with('success', __('app.installment_recorded'));
    }

    public function show(InstallmentPayment $installment): View
    {
        abort_if(auth()->user()->role?->slug === 'member' && auth()->user()->member_id !== $installment->loan->member_id, 403);

        return $this->edit($installment);
    }

    public function edit(InstallmentPayment $installment): View
    {
        $user = auth()->user();
        abort_if($user->role?->slug === 'member' && $user->member_id !== $installment->loan->member_id, 403);

        $loans = Loan::with('member')->orderBy('application_date', 'desc');
        if ($user->role?->slug === 'member' && $user->member_id) {
            $loans->where('member_id', $user->member_id);
        }

        return view('installments.edit', [
            'installment' => $installment,
            'loans' => $loans->get(),
        ]);
    }

    public function update(Request $request, InstallmentPayment $installment): RedirectResponse
    {
        $data = $request->validate([
            'loan_id' => ['required', 'exists:loans,id'],
            'payment_date' => ['required', 'date'],
            'payment_amount' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string'],
        ]);

        abort_if($request->user()->role?->slug === 'member' && $request->user()->member_id !== $installment->loan->member_id, 403);

        $installment->update([
            'loan_id' => $data['loan_id'],
            'payment_date' => $data['payment_date'],
            'payment_amount' => $data['payment_amount'],
            'notes' => $data['notes'] ?? null,
        ]);

        $loan = $installment->loan()->withSum('payments', 'payment_amount')->first();
        $paid = (float) ($loan->payments_sum_payment_amount ?? 0);
        $totalPayable = $loan->amount + ($loan->amount * $loan->interest_rate / 100);
        $remaining = max(round($totalPayable - $paid, 2), 0);
        $loan->update([
            'paid_amount' => $paid,
            'remaining_balance' => $remaining,
            'status' => $remaining <= 0 ? 'completed' : 'active',
        ]);
        $installment->update(['remaining_balance' => $remaining]);

        return redirect()->route('installments.index')->with('success', __('app.installment_updated'));
    }

    public function destroy(InstallmentPayment $installment): RedirectResponse
    {
        abort_if(auth()->user()->role?->slug === 'member', 403);

        $loan = $installment->loan;
        $installment->delete();

        $paid = (float) $loan->payments()->sum('payment_amount');
        $totalPayable = $loan->amount + ($loan->amount * $loan->interest_rate / 100);
        $remaining = max(round($totalPayable - $paid, 2), 0);
        $loan->update([
            'paid_amount' => $paid,
            'remaining_balance' => $remaining,
            'status' => $remaining <= 0 ? 'completed' : 'active',
        ]);

        return redirect()->route('installments.index')->with('success', __('app.installment_deleted'));
    }
}
