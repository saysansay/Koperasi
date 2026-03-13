<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\CooperativeSetting;
use App\Models\InstallmentPayment;
use App\Models\JournalEntry;
use App\Models\Loan;
use App\Models\LoanType;
use App\Models\Member;
use App\Models\NotificationItem;
use App\Models\Role;
use App\Models\SalePayment;
use App\Models\SavingTransaction;
use App\Models\SavingType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $roles = collect([
            ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Full system access'],
            ['name' => 'Manager', 'slug' => 'manager', 'description' => 'Operational approvals and reports'],
            ['name' => 'Staff', 'slug' => 'staff', 'description' => 'Daily transaction operations'],
            ['name' => 'Member', 'slug' => 'member', 'description' => 'Member portal access'],
        ])->map(fn ($role) => Role::updateOrCreate(['slug' => $role['slug']], $role));

        $members = collect([
            ['member_id' => 'MBR-001', 'name' => 'Budi Santoso', 'ktp_number' => '3174010101010001', 'address' => 'Jakarta', 'phone_number' => '081200000001', 'email' => 'budi@koperasi.test', 'join_date' => now()->subYears(3)->toDateString(), 'status' => 'active'],
            ['member_id' => 'MBR-002', 'name' => 'Siti Rahma', 'ktp_number' => '3174010101010002', 'address' => 'Bandung', 'phone_number' => '081200000002', 'email' => 'siti@koperasi.test', 'join_date' => now()->subYears(2)->toDateString(), 'status' => 'active'],
            ['member_id' => 'MBR-003', 'name' => 'Andi Wijaya', 'ktp_number' => '3174010101010003', 'address' => 'Surabaya', 'phone_number' => '081200000003', 'email' => 'andi@koperasi.test', 'join_date' => now()->subYear()->toDateString(), 'status' => 'active'],
        ])->map(fn ($member) => Member::updateOrCreate(['member_id' => $member['member_id']], $member));

        $users = [
            ['role' => 'admin', 'name' => 'System Admin', 'email' => 'admin@koperasi.test', 'password' => 'password', 'member_id' => null],
            ['role' => 'manager', 'name' => 'Operations Manager', 'email' => 'manager@koperasi.test', 'password' => 'password', 'member_id' => null],
            ['role' => 'staff', 'name' => 'Front Office Staff', 'email' => 'staff@koperasi.test', 'password' => 'password', 'member_id' => null],
            ['role' => 'member', 'name' => 'Budi Santoso', 'email' => 'member@koperasi.test', 'password' => 'password', 'member_id' => $members[0]->id],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'role_id' => $roles->firstWhere('slug', $data['role'])->id,
                    'member_id' => $data['member_id'],
                    'name' => $data['name'],
                    'password' => Hash::make($data['password']),
                    'is_active' => true,
                ]
            );
        }

        $savingTypes = collect([
            ['name' => 'Mandatory Savings', 'code' => 'WAJIB', 'description' => 'Simpanan wajib bulanan', 'default_amount' => 50000, 'is_active' => true],
            ['name' => 'Principal Savings', 'code' => 'POKOK', 'description' => 'Simpanan pokok saat registrasi', 'default_amount' => 250000, 'is_active' => true],
            ['name' => 'Voluntary Savings', 'code' => 'SUKARELA', 'description' => 'Simpanan sukarela fleksibel', 'default_amount' => 0, 'is_active' => true],
        ])->map(fn ($type) => SavingType::updateOrCreate(['code' => $type['code']], $type));

        $loanTypes = collect([
            ['name' => 'Consumptive Loan', 'code' => 'KONS', 'default_interest_rate' => 1.5, 'default_period_months' => 12, 'is_active' => true],
            ['name' => 'Business Loan', 'code' => 'USAHA', 'default_interest_rate' => 1.2, 'default_period_months' => 18, 'is_active' => true],
        ])->map(fn ($type) => LoanType::updateOrCreate(['code' => $type['code']], $type));

        foreach ([
            ['code' => '101', 'name' => 'Cash', 'category' => 'asset'],
            ['code' => '102', 'name' => 'Bank', 'category' => 'asset'],
            ['code' => '201', 'name' => 'Member Savings Liability', 'category' => 'liability'],
            ['code' => '301', 'name' => 'Capital', 'category' => 'equity'],
            ['code' => '401', 'name' => 'Loan Interest Income', 'category' => 'income'],
            ['code' => '501', 'name' => 'Operational Expense', 'category' => 'expense'],
        ] as $account) {
            Account::updateOrCreate(['code' => $account['code']], $account + ['is_active' => true]);
        }

        CooperativeSetting::updateOrCreate(['id' => 1], [
            'name' => 'Koperasi Sejahtera Bersama',
            'address' => 'Jl. Merdeka No. 10, Jakarta',
            'phone' => '021-5550001',
            'email' => 'info@koperasi.test',
            'default_interest_rate' => 1.5,
            'currency' => 'IDR',
        ]);

        $staffUser = User::where('email', 'staff@koperasi.test')->first();
        $managerUser = User::where('email', 'manager@koperasi.test')->first();

        foreach ($members as $index => $member) {
            SavingTransaction::updateOrCreate([
                'member_id' => $member->id,
                'saving_type_id' => $savingTypes[0]->id,
                'transaction_date' => now()->subMonths(2 - $index)->toDateString(),
            ], [
                'transaction_type' => 'deposit',
                'amount' => 50000 + ($index * 10000),
                'description' => 'Monthly mandatory savings',
                'created_by' => $staffUser->id,
            ]);

            SavingTransaction::updateOrCreate([
                'member_id' => $member->id,
                'saving_type_id' => $savingTypes[2]->id,
                'transaction_date' => now()->subMonth()->toDateString(),
                'description' => 'Top up voluntary savings',
            ], [
                'transaction_type' => 'deposit',
                'amount' => 100000 + ($index * 50000),
                'created_by' => $staffUser->id,
            ]);
        }

        SalePayment::updateOrCreate(['invoice_number' => 'INV-202603-001'], [
            'member_id' => $members[0]->id,
            'sale_date' => now()->subDays(10)->toDateString(),
            'payment_date' => now()->subDays(8)->toDateString(),
            'sale_amount' => 450000,
            'payment_amount' => 450000,
            'remaining_balance' => 0,
            'payment_method' => 'payroll',
            'status' => 'paid',
            'notes' => 'Office groceries purchase',
            'created_by' => $staffUser->id,
        ]);

        SalePayment::updateOrCreate(['invoice_number' => 'INV-202603-002'], [
            'member_id' => $members[1]->id,
            'sale_date' => now()->subDays(6)->toDateString(),
            'payment_date' => now()->subDays(5)->toDateString(),
            'sale_amount' => 650000,
            'payment_amount' => 300000,
            'remaining_balance' => 350000,
            'payment_method' => 'transfer',
            'status' => 'partial',
            'notes' => 'Electronics installment payment',
            'created_by' => $staffUser->id,
        ]);

        $loan = Loan::updateOrCreate(['application_number' => 'LOAN-202603-001'], [
            'member_id' => $members[0]->id,
            'loan_type_id' => $loanTypes[0]->id,
            'application_date' => now()->subMonth()->toDateString(),
            'approved_date' => now()->subWeeks(3)->toDateString(),
            'amount' => 3000000,
            'interest_rate' => 1.5,
            'installment_period' => 12,
            'installment_amount' => 253750,
            'paid_amount' => 507500,
            'remaining_balance' => 2537500,
            'status' => 'active',
            'notes' => 'Consumer goods financing',
            'approved_by' => $managerUser->id,
        ]);

        InstallmentPayment::updateOrCreate(['loan_id' => $loan->id, 'installment_number' => 1], [
            'payment_date' => now()->subWeeks(2)->toDateString(),
            'payment_amount' => 253750,
            'remaining_balance' => 2791250,
            'notes' => 'First installment',
            'created_by' => $staffUser->id,
        ]);

        InstallmentPayment::updateOrCreate(['loan_id' => $loan->id, 'installment_number' => 2], [
            'payment_date' => now()->subWeek()->toDateString(),
            'payment_amount' => 253750,
            'remaining_balance' => 2537500,
            'notes' => 'Second installment',
            'created_by' => $staffUser->id,
        ]);

        $cash = Account::where('code', '101')->first();
        $interestIncome = Account::where('code', '401')->first();
        $expense = Account::where('code', '501')->first();
        $bank = Account::where('code', '102')->first();

        $interestJournal = JournalEntry::updateOrCreate(['reference_no' => 'JRN-INT-001'], [
            'entry_date' => now()->subWeek()->toDateString(),
            'description' => 'Interest income from installments',
            'total_amount' => 150000,
            'created_by' => $staffUser->id,
        ]);
        $interestJournal->lines()->delete();
        $interestJournal->lines()->createMany([
            ['account_id' => $cash->id, 'debit' => 150000, 'credit' => 0],
            ['account_id' => $interestIncome->id, 'debit' => 0, 'credit' => 150000],
        ]);

        $expenseJournal = JournalEntry::updateOrCreate(['reference_no' => 'JRN-EXP-001'], [
            'entry_date' => now()->subDays(5)->toDateString(),
            'description' => 'Office utilities',
            'total_amount' => 75000,
            'created_by' => $staffUser->id,
        ]);
        $expenseJournal->lines()->delete();
        $expenseJournal->lines()->createMany([
            ['account_id' => $expense->id, 'debit' => 75000, 'credit' => 0],
            ['account_id' => $bank->id, 'debit' => 0, 'credit' => 75000],
        ]);

        foreach ([
            ['title' => 'Pending approvals monitored', 'message' => 'Review loan queue and member requests.', 'level' => 'warning', 'action_url' => '/loans'],
            ['title' => 'Savings cycle posted', 'message' => 'Mandatory savings for this month have been posted.', 'level' => 'success', 'action_url' => '/savings'],
        ] as $notification) {
            NotificationItem::firstOrCreate(['title' => $notification['title']], $notification);
        }
    }
}
