<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstallmentController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MemberTransactionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalePaymentController;
use App\Http\Controllers\SavingsController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', fn () => redirect()->route('login'));
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/my-transactions', [MemberTransactionController::class, 'index'])->name('member-transactions.index')->middleware('role:member');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/change-password', [ProfileController::class, 'editPassword'])->name('profile.password.edit');
    Route::put('/change-password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::get('members/import-template', [MemberController::class, 'importTemplate'])->name('members.import-template')->middleware('role:admin,manager,staff');
    Route::post('members/import', [MemberController::class, 'import'])->name('members.import')->middleware('role:admin,manager,staff');
    Route::resource('members', MemberController::class)->middleware('role:admin,manager,staff');
    Route::resource('savings', SavingsController::class)->middleware('role:admin,manager,staff');
    Route::resource('sale-payments', SalePaymentController::class)->parameters(['sale-payments' => 'salePayment'])->middleware('role:admin,manager,staff');
    Route::resource('loans', LoanController::class)->middleware('role:admin,manager,staff');
    Route::post('loans/{loan}/approve', [LoanController::class, 'approve'])->name('loans.approve')->middleware('role:admin,manager');
    Route::post('loans/{loan}/reject', [LoanController::class, 'reject'])->name('loans.reject')->middleware('role:admin,manager');
    Route::resource('installments', InstallmentController::class)->middleware('role:admin,manager,staff');
    Route::resource('accounts', AccountController::class)->middleware('role:admin,manager,staff');
    Route::resource('journals', JournalEntryController::class)->middleware('role:admin,manager,staff');
    Route::resource('users', UserController::class)->middleware('role:admin');
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index')->middleware('role:admin,manager');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update')->middleware('role:admin,manager');

    Route::prefix('reports')->name('reports.')->middleware('role:admin,manager')->group(function () {
        Route::get('members', [ReportController::class, 'members'])->name('members');
        Route::get('savings', [ReportController::class, 'savings'])->name('savings');
        Route::get('loans', [ReportController::class, 'loans'])->name('loans');
        Route::get('installments', [ReportController::class, 'installments'])->name('installments');
        Route::get('financial', [ReportController::class, 'financial'])->name('financial');
        Route::get('export/{report}', [ReportController::class, 'export'])->name('export');
    });
});
