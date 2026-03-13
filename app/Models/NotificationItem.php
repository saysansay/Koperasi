<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationItem extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'message', 'level', 'action_url', 'read_at'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }
}
