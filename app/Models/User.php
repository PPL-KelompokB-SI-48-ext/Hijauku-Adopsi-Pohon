<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->is_active || ! ($this->role instanceof UserRole)) {
            return false;
        }

        return match ($panel->getId()) {
            'app' => $this->role === UserRole::Manager,
            'petugas' => $this->role === UserRole::FieldStaff,
            'warga' => $this->role === UserRole::Citizen,
            default => false,
        };
    }

    public function isManager(): bool
    {
        return $this->is_active && $this->role === UserRole::Manager;
    }

    public function isFieldStaff(): bool
    {
        return $this->is_active && $this->role === UserRole::FieldStaff;
    }

    public function isCitizen(): bool
    {
        return $this->is_active && $this->role === UserRole::Citizen;
    }
}
