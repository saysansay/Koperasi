<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CooperativeSetting extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address', 'phone', 'email', 'default_interest_rate', 'currency'];

    protected function casts(): array
    {
        return ['default_interest_rate' => 'decimal:2'];
    }
}
