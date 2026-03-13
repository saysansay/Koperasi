<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'default_interest_rate', 'default_period_months', 'is_active'];

    protected function casts(): array
    {
        return ['default_interest_rate' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }
}
