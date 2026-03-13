<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanType;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function index(Request $request): View
    {
        $query = Loan::with(['member', 'loanType', 'approver'])->latest('application_date');

        if ($request->user()->role?->slug === 'member' && $request->user()->member_id) {
            $query->where('member_id', $request->user()->member_id);
        }

        return view('loans.index', [
            'loans' => $query->paginate(10),
        ]);
    }

    public function create(): View
    {
        $user = auth()->user();

        return view('loans.create', [
            'members' => $user->role?->slug === 'member'
                ? Member::whereKey($user->member_id)->get()
                : Member::orderBy('name')->get(),
            'loanTypes' => LoanType::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
            'loan_type_id' => ['required', 'exists:loan_types,id'],
            'application_number' => ['required', 'string', 'max:50', 'unique:loans,application_number'],
            'application_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'interest_rate' => ['required', 'numeric', 'min:0'],
            'installment_period' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($request->user()->role?->slug === 'member' && $request->user()->member_id) {
            $data['member_id'] = $request->user()->member_id;
        }

        $totalPayable = $data['amount'] + ($data['amount'] * $data['interest_rate'] / 100);
        $data['installment_amount'] = round($totalPayable / $data['installment_period'], 2);
        $data['remaining_balance'] = round($totalPayable, 2);
        $data['status'] = 'pending';

        Loan::create($data);

        return redirect()->route('loans.index')->with('success', __('app.loan_submitted'));
    }

    public function show(Loan $loan): View
    {
        abort_if(auth()->user()->role?->slug === 'member' && auth()->user()->member_id !== $loan->member_id, 403);

        $loan->load(['member', 'loanType', 'payments']);

        return view('loans.show', compact('loan'));
    }

    public function edit(Loan $loan): View
    {
        $user = auth()->user();
        abort_if($user->role?->slug === 'member' && $user->member_id !== $loan->member_id, 403);

        return view('loans.edit', [
            'loan' => $loan,
            'members' => $user->role?->slug === 'member'
                ? Member::whereKey($user->member_id)->get()
                : Member::orderBy('name')->get(),
            'loanTypes' => LoanType::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Loan $loan): RedirectResponse
    {
        $data = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
            'loan_type_id' => ['required', 'exists:loan_types,id'],
            'application_number' => ['required', 'string', 'max:50', 'unique:loans,application_number,'.$loan->id],
            'application_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'interest_rate' => ['required', 'numeric', 'min:0'],
            'installment_period' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:pending,approved,rejected,active,completed'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($request->user()->role?->slug === 'member' && $request->user()->member_id !== $loan->member_id) {
            abort(403);
        }

        $totalPayable = $data['amount'] + ($data['amount'] * $data['interest_rate'] / 100);
        $paidAmount = $loan->paid_amount;
        $data['installment_amount'] = round($totalPayable / $data['installment_period'], 2);
        $data['remaining_balance'] = max(round($totalPayable - $paidAmount, 2), 0);

        if (in_array($data['status'], ['approved', 'active'], true) && ! $loan->approved_date) {
            $data['approved_date'] = now()->toDateString();
            $data['approved_by'] = $request->user()->id;
        }

        $loan->update($data);

        return redirect()->route('loans.index')->with('success', __('app.loan_updated'));
    }

    public function destroy(Loan $loan): RedirectResponse
    {
        abort_if(auth()->user()->role?->slug === 'member', 403);

        $loan->delete();

        return redirect()->route('loans.index')->with('success', __('app.loan_deleted'));
    }

    public function approve(Request $request, Loan $loan): RedirectResponse
    {
        $loan->update([
            'status' => 'active',
            'approved_date' => now()->toDateString(),
            'approved_by' => $request->user()->id,
        ]);

        return redirect()->route('loans.index')->with('success', __('app.loan_approved'));
    }

    public function reject(Loan $loan): RedirectResponse
    {
        $loan->update(['status' => 'rejected']);

        return redirect()->route('loans.index')->with('success', __('app.loan_rejected'));
    }
}
