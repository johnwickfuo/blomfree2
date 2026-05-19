<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Affiliate extends Model
{
    public const STATUSES = ['active', 'suspended'];

    protected $fillable = [
        'user_id',
        'code',
        'status',
        'whatsapp_number',
        'social_handles',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'pending_balance',
        'available_balance',
        'total_earned',
        'total_withdrawn',
        'suspended_at',
        'suspension_reason',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'social_handles' => 'array',
            'pending_balance' => 'decimal:2',
            'available_balance' => 'decimal:2',
            'total_earned' => 'decimal:2',
            'total_withdrawn' => 'decimal:2',
            'suspended_at' => 'datetime',
            'joined_at' => 'datetime',
            // Bank account number is encrypted at rest.
            'bank_account_number' => 'encrypted',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Affiliate $a): void {
            if (blank($a->code)) {
                $a->code = static::generateUniqueCode();
            }
        });
    }

    public static function generateUniqueCode(int $maxAttempts = 5): string
    {
        for ($i = 0; $i < $maxAttempts; $i++) {
            $code = 'BLM-'.Str::upper(Str::random(6));
            if (! static::query()->where('code', $code)->exists()) {
                return $code;
            }
        }

        throw new \RuntimeException('Could not generate a unique affiliate code after '.$maxAttempts.' attempts.');
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(AffiliateCommission::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(AffiliateWithdrawal::class);
    }

    public function balanceAdjustments(): HasMany
    {
        return $this->hasMany(AffiliateBalanceAdjustment::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    protected function lifetimeReferralsCount(): Attribute
    {
        return Attribute::get(fn (): int => $this->orders()->count());
    }

    protected function totalCommissionsCount(): Attribute
    {
        return Attribute::get(fn (): int => $this->commissions()->count());
    }
}
