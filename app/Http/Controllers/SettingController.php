<?php

namespace App\Http\Controllers;

use App\Models\CooperativeSetting;
use App\Models\LoanType;
use App\Models\SavingType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        return view('settings.index', [
            'setting' => CooperativeSetting::first(),
            'savingTypes' => SavingType::latest()->get(),
            'loanTypes' => LoanType::latest()->get(),
        ]);
    }

    public function create(): View
    {
        return $this->index();
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->update($request, CooperativeSetting::firstOrCreate(['name' => 'Koperasi Demo']));
    }

    public function show(CooperativeSetting $setting): View
    {
        return $this->index();
    }

    public function edit(CooperativeSetting $setting): View
    {
        return $this->index();
    }

    public function update(Request $request, CooperativeSetting $setting = null): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email'],
            'default_interest_rate' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
        ]);

        ($setting ?? CooperativeSetting::firstOrNew())->fill($data)->save();

        return redirect()->route('settings.index')->with('success', __('app.settings_updated'));
    }

    public function destroy(CooperativeSetting $setting): RedirectResponse
    {
        return redirect()->route('settings.index');
    }

    public function storeSavingType(Request $request): RedirectResponse
    {
        $data = $this->validateSavingType($request);

        SavingType::create($data);

        return redirect()->route('settings.index')->with('success', __('app.saving_type_created'));
    }

    public function updateSavingType(Request $request, SavingType $savingType): RedirectResponse
    {
        $data = $this->validateSavingType($request, $savingType);

        $savingType->update($data);

        return redirect()->route('settings.index')->with('success', __('app.saving_type_updated'));
    }

    public function storeLoanType(Request $request): RedirectResponse
    {
        $data = $this->validateLoanType($request);

        LoanType::create($data);

        return redirect()->route('settings.index')->with('success', __('app.loan_type_created'));
    }

    public function updateLoanType(Request $request, LoanType $loanType): RedirectResponse
    {
        $data = $this->validateLoanType($request, $loanType);

        $loanType->update($data);

        return redirect()->route('settings.index')->with('success', __('app.loan_type_updated'));
    }

    private function validateSavingType(Request $request, ?SavingType $savingType = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('saving_types', 'code')->ignore($savingType?->id)],
            'description' => ['nullable', 'string'],
            'default_amount' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function validateLoanType(Request $request, ?LoanType $loanType = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('loan_types', 'code')->ignore($loanType?->id)],
            'default_interest_rate' => ['required', 'numeric', 'min:0'],
            'default_period_months' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
