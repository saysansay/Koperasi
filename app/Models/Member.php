<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Member extends Model
{
    use HasFactory;

    public const ID_PREFIX = 'MBR-';

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

    public static function generateNextMemberId(): string
    {
        $maxNumber = static::query()
            ->pluck('member_id')
            ->map(function ($memberId) {
                if (! preg_match('/^'.preg_quote(self::ID_PREFIX, '/').'(\d+)$/', (string) $memberId, $matches)) {
                    return 0;
                }

                return (int) $matches[1];
            })
            ->max() ?? 0;

        return self::ID_PREFIX.str_pad((string) ($maxNumber + 1), 3, '0', STR_PAD_LEFT);
    }
}
