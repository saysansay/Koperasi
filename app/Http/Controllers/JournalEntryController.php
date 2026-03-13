<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\JournalEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class JournalEntryController extends Controller
{
    public function index(): View
    {
        return view('journals.index', [
            'entries' => JournalEntry::with('lines.account')->latest('entry_date')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('journals.create', ['accounts' => Account::where('is_active', true)->orderBy('code')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'reference_no' => ['required', 'string', 'max:50', 'unique:journal_entries,reference_no'],
            'entry_date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'debit_account_id' => ['required', 'exists:accounts,id', 'different:credit_account_id'],
            'credit_account_id' => ['required', 'exists:accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        DB::transaction(function () use ($request, $data) {
            $entry = JournalEntry::create([
                'reference_no' => $data['reference_no'],
                'entry_date' => $data['entry_date'],
                'description' => $data['description'],
                'total_amount' => $data['amount'],
                'created_by' => $request->user()->id,
            ]);

            $entry->lines()->createMany([
                ['account_id' => $data['debit_account_id'], 'debit' => $data['amount'], 'credit' => 0],
                ['account_id' => $data['credit_account_id'], 'debit' => 0, 'credit' => $data['amount']],
            ]);
        });

        return redirect()->route('journals.index')->with('success', __('app.journal_created'));
    }

    public function show(JournalEntry $journal): View
    {
        return $this->edit($journal);
    }

    public function edit(JournalEntry $journal): View
    {
        $journal->load('lines');
        return view('journals.edit', ['journal' => $journal, 'accounts' => Account::where('is_active', true)->orderBy('code')->get()]);
    }

    public function update(Request $request, JournalEntry $journal): RedirectResponse
    {
        $data = $request->validate([
            'reference_no' => ['required', 'string', 'max:50', 'unique:journal_entries,reference_no,'.$journal->id],
            'entry_date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'debit_account_id' => ['required', 'exists:accounts,id', 'different:credit_account_id'],
            'credit_account_id' => ['required', 'exists:accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        DB::transaction(function () use ($journal, $data) {
            $journal->update([
                'reference_no' => $data['reference_no'],
                'entry_date' => $data['entry_date'],
                'description' => $data['description'],
                'total_amount' => $data['amount'],
            ]);

            $journal->lines()->delete();
            $journal->lines()->createMany([
                ['account_id' => $data['debit_account_id'], 'debit' => $data['amount'], 'credit' => 0],
                ['account_id' => $data['credit_account_id'], 'debit' => 0, 'credit' => $data['amount']],
            ]);
        });

        return redirect()->route('journals.index')->with('success', __('app.journal_updated'));
    }

    public function destroy(JournalEntry $journal): RedirectResponse
    {
        $journal->delete();

        return redirect()->route('journals.index')->with('success', __('app.journal_deleted'));
    }
}
