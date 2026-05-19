<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AffiliateWithdrawal extends Model
{
    public const STATUSES = ['pending', 'approved', 'paid', 'rejected'];

    protected $fillable = [
        'affiliate_id',
        'reference',
        'amount',
        'fee',
        'net_amount',
        'bank_snapshot',
        'status',
        'admin_notes',
        'payment_reference',
        'requested_at',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'bank_snapshot' => 'array',
            'amount' => 'decimal:2',
            'fee' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'requested_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (AffiliateWithdrawal $w): void {
            if (blank($w->reference)) {
                do {
                    $ref = 'WTH-'.Str::upper(Str::random(6));
                } while (static::query()->where('reference', $ref)->exists());
                $w->reference = $ref;
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(AffiliateCommission::class, 'withdrawal_id');
    }
}
