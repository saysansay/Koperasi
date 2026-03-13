<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'loan_type_id',
        'application_number',
        'application_date',
        'approved_date',
        'amount',
        'interest_rate',
        'installment_period',
        'installment_amount',
        'paid_amount',
        'remaining_balance',
        'status',
        'notes',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'application_date' => 'date',
            'approved_date' => 'date',
            'amount' => 'decimal:2',
            'interest_rate' => 'decimal:2',
            'installment_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'remaining_balance' => 'decimal:2',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function loanType(): BelongsTo
    {
        return $this->belongsTo(LoanType::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InstallmentPayment::class);
    }

    public function getTotalPayableAttribute(): float
    {
        return (float) ($this->amount + ($this->amount * $this->interest_rate / 100));
    }
}
