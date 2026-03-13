<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'name',
        'ktp_number',
        'address',
        'phone_number',
        'email',
        'join_date',
        'status',
    ];

    protected function casts(): array
    {
        return ['join_date' => 'date'];
    }

    public function savings(): HasMany
    {
        return $this->hasMany(SavingTransaction::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function salePayments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function getSavingsBalanceAttribute(): float
    {
        return (float) $this->savings()->selectRaw("COALESCE(SUM(CASE WHEN transaction_type = 'deposit' THEN amount ELSE -amount END), 0) as balance")->value('balance');
    }
}
