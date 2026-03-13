<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\SavingTransaction;
use App\Models\SavingType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SavingsController extends Controller
{
    public function index(Request $request): View
    {
        $transactionsQuery = SavingTransaction::with(['member', 'savingType'])->latest('transaction_date');
        $balancesQuery = Member::withSum(['savings as deposits_sum' => fn ($q) => $q->where('transaction_type', 'deposit')], 'amount')
            ->withSum(['savings as withdrawals_sum' => fn ($q) => $q->where('transaction_type', 'withdrawal')], 'amount')
            ->latest();

        if ($request->user()->role?->slug === 'member' && $request->user()->member_id) {
            $transactionsQuery->where('member_id', $request->user()->member_id);
            $balancesQuery->whereKey($request->user()->member_id);
        }

        $transactions = $transactionsQuery->paginate(10);
        $balances = $balancesQuery->take(8)->get();

        return view('savings.index', compact('transactions', 'balances'));
    }

    public function create(): View
    {
        $user = auth()->user();

        return view('savings.create', [
            'members' => $user->role?->slug === 'member'
                ? Member::whereKey($user->member_id)->get()
                : Member::orderBy('name')->get(),
            'savingTypes' => SavingType::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
            'saving_type_id' => ['required', 'exists:saving_types,id'],
            'transaction_date' => ['required', 'date'],
            'transaction_type' => ['required', 'in:deposit,withdrawal'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string'],
        ]);

        if ($request->user()->role?->slug === 'member' && $request->user()->member_id) {
            $data['member_id'] = $request->user()->member_id;
        }

        $data['created_by'] = $request->user()->id;
        SavingTransaction::create($data);

        return redirect()->route('savings.index')->with('success', __('app.savings_recorded'));
    }

    public function show(SavingTransaction $saving): View
    {
        abort_if(auth()->user()->role?->slug === 'member' && auth()->user()->member_id !== $saving->member_id, 403);

        return $this->edit($saving);
    }

    public function edit(SavingTransaction $saving): View
    {
        $user = auth()->user();
        abort_if($user->role?->slug === 'member' && $user->member_id !== $saving->member_id, 403);

        return view('savings.edit', [
            'saving' => $saving,
            'members' => $user->role?->slug === 'member'
                ? Member::whereKey($user->member_id)->get()
                : Member::orderBy('name')->get(),
            'savingTypes' => SavingType::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, SavingTransaction $saving): RedirectResponse
    {
        $data = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
            'saving_type_id' => ['required', 'exists:saving_types,id'],
            'transaction_date' => ['required', 'date'],
            'transaction_type' => ['required', 'in:deposit,withdrawal'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string'],
        ]);

        if ($request->user()->role?->slug === 'member' && $request->user()->member_id !== $saving->member_id) {
            abort(403);
        }

        $saving->update($data);

        return redirect()->route('savings.index')->with('success', __('app.savings_updated'));
    }

    public function destroy(SavingTransaction $saving): RedirectResponse
    {
        abort_if(auth()->user()->role?->slug === 'member', 403);

        $saving->delete();

        return redirect()->route('savings.index')->with('success', __('app.savings_deleted'));
    }
}
