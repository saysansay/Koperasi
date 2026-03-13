<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'role_id',
        'member_id',
        'name',
        'email',
        'password',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role?->slug, $roles, true);
    }

    public function canAccessMenu(string $menuKey): bool
    {
        $menu = collect(config('menu'))->firstWhere('key', $menuKey);

        if (! $menu) {
            return false;
        }

        return $this->hasAnyRole($menu['roles'] ?? []);
    }

    public function visibleMenus(): array
    {
        return collect(config('menu'))
            ->filter(fn (array $menu) => $this->hasAnyRole($menu['roles'] ?? []))
            ->values()
            ->all();
    }
}
