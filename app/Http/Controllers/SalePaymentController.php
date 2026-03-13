<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\SalePayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalePaymentController extends Controller
{
    public function index(Request $request): View
    {
        $query = SalePayment::with('member')->latest('payment_date');

        if ($request->user()->role?->slug === 'member' && $request->user()->member_id) {
            $query->where('member_id', $request->user()->member_id);
        }

        return view('sales-payments.index', [
            'payments' => $query->paginate(10),
            'summary' => [
                'total_sales' => (clone $query)->sum('sale_amount'),
                'total_paid' => (clone $query)->sum('payment_amount'),
                'total_balance' => (clone $query)->sum('remaining_balance'),
            ],
        ]);
    }

    public function create(): View
    {
        $user = auth()->user();

        return view('sales-payments.create', [
            'members' => $user->role?->slug === 'member'
                ? Member::whereKey($user->member_id)->get()
                : Member::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
            'invoice_number' => ['required', 'string', 'max:50', 'unique:sale_payments,invoice_number'],
            'sale_date' => ['required', 'date'],
            'payment_date' => ['required', 'date'],
            'sale_amount' => ['required', 'numeric', 'min:0.01'],
            'payment_amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($request->user()->role?->slug === 'member' && $request->user()->member_id) {
            $data['member_id'] = $request->user()->member_id;
        }

        $data['remaining_balance'] = max((float) $data['sale_amount'] - (float) $data['payment_amount'], 0);
        $data['status'] = $data['remaining_balance'] <= 0 ? 'paid' : ((float) $data['payment_amount'] > 0 ? 'partial' : 'pending');
        $data['created_by'] = $request->user()->id;

        SalePayment::create($data);

        return redirect()->route('sale-payments.create')->with('success', __('app.sales_payment_recorded'));
    }

    public function show(SalePayment $salePayment): View
    {
        abort_if(auth()->user()->role?->slug === 'member' && auth()->user()->member_id !== $salePayment->member_id, 403);

        return view('sales-payments.show', ['payment' => $salePayment->load('member')]);
    }

    public function edit(SalePayment $salePayment): View
    {
        $user = auth()->user();
        abort_if($user->role?->slug === 'member' && $user->member_id !== $salePayment->member_id, 403);

        return view('sales-payments.edit', [
            'payment' => $salePayment,
            'members' => $user->role?->slug === 'member'
                ? Member::whereKey($user->member_id)->get()
                : Member::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, SalePayment $salePayment): RedirectResponse
    {
        $data = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
            'invoice_number' => ['required', 'string', 'max:50', 'unique:sale_payments,invoice_number,'.$salePayment->id],
            'sale_date' => ['required', 'date'],
            'payment_date' => ['required', 'date'],
            'sale_amount' => ['required', 'numeric', 'min:0.01'],
            'payment_amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($request->user()->role?->slug === 'member' && $request->user()->member_id !== $salePayment->member_id) {
            abort(403);
        }

        $data['remaining_balance'] = max((float) $data['sale_amount'] - (float) $data['payment_amount'], 0);
        $data['status'] = $data['remaining_balance'] <= 0 ? 'paid' : ((float) $data['payment_amount'] > 0 ? 'partial' : 'pending');

        $salePayment->update($data);

        return redirect()->route('sale-payments.index')->with('success', __('app.sales_payment_updated'));
    }

    public function destroy(SalePayment $salePayment): RedirectResponse
    {
        abort_if(auth()->user()->role?->slug === 'member', 403);

        $salePayment->delete();

        return redirect()->route('sale-payments.index')->with('success', __('app.sales_payment_deleted'));
    }
}
