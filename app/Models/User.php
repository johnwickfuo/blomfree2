<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_admin',
        'is_affiliate',
        'delivery_address',
        'delivery_state',
        'delivery_lga',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_affiliate' => 'boolean',
            // Bank account number is encrypted at rest.
            'bank_account_number' => 'encrypted',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin === true;
    }

    public function affiliate(): HasOne
    {
        return $this->hasOne(Affiliate::class);
    }

    public function installmentPlans(): HasMany
    {
        return $this->hasMany(InstallmentPlan::class);
    }

    public function activeInstallmentPlans(): HasMany
    {
        return $this->installmentPlans()->whereIn('status', [
            'pending_approval', 'awaiting_down_payment', 'active',
            'completed', 'awaiting_fulfillment', 'awaiting_refund',
        ]);
    }

    public function hasBankDetails(): bool
    {
        return filled($this->bank_name) && filled($this->bank_account_number);
    }
}
