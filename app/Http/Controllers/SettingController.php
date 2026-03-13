<?php

namespace App\Http\Controllers;

use App\Models\CooperativeSetting;
use App\Models\LoanType;
use App\Models\SavingType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
}
