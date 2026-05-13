<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role', 'farm_id', 'avatar_url', 'last_login_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_FARM_OWNER = 'farm_owner';
    public const ROLE_FARM_MANAGER = 'farm_manager';
    public const ROLE_TECHNICIAN = 'technician';
    public const ROLE_WORKER = 'worker';
    public const ROLE_WAREHOUSE = 'warehouse';
    public const ROLE_DELIVERY = 'delivery';

    public const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_FARM_OWNER,
        self::ROLE_FARM_MANAGER,
        self::ROLE_TECHNICIAN,
        self::ROLE_WORKER,
        self::ROLE_WAREHOUSE,
        self::ROLE_DELIVERY,
    ];

    public const APPROVER_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_FARM_OWNER,
        self::ROLE_FARM_MANAGER,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => 'string',
        ];
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function canApprove(): bool
    {
        return in_array($this->role, self::APPROVER_ROLES);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, self::ROLES, true);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isFarmOwner(): bool
    {
        return $this->role === self::ROLE_FARM_OWNER;
    }

    public function isFarmManager(): bool
    {
        return $this->role === self::ROLE_FARM_MANAGER;
    }
}
