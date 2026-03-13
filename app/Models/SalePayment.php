<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'invoice_number',
        'sale_date',
        'payment_date',
        'sale_amount',
        'payment_amount',
        'remaining_balance',
        'payment_method',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'payment_date' => 'date',
            'sale_amount' => 'decimal:2',
            'payment_amount' => 'decimal:2',
            'remaining_balance' => 'decimal:2',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
