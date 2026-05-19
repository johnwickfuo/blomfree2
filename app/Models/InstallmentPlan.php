<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class InstallmentPlan extends Model
{
    public const STATUSES = [
        'pending_approval', 'awaiting_down_payment', 'active', 'completed',
        'awaiting_fulfillment', 'fulfilled', 'defaulted',
        'cancelled_by_customer', 'awaiting_refund', 'refunded',
    ];

    protected $fillable = [
        'reference',
        'user_id',
        'installable_type',
        'installable_id',
        'installable_label',
        'subsidiary',
        'status',
        'total_amount',
        'amount_paid',
        'minimum_down_payment_amount',
        'minimum_down_payment_percentage',
        'down_payment_paid',
        'maximum_length_months',
        'deadline',
        'suggested_schedule',
        'forfeiture_percentage',
        'affiliate_id',
        'affiliate_code_used',
        'affiliate_commission_locked',
        'terms_accepted_at',
        'terms_acceptance_ip',
        'terms_version',
        'requested_at',
        'approved_at',
        'activated_at',
        'completed_at',
        'fulfilled_at',
        'defaulted_at',
        'cancelled_at',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'minimum_down_payment_amount' => 'decimal:2',
            'minimum_down_payment_percentage' => 'integer',
            'down_payment_paid' => 'boolean',
            'maximum_length_months' => 'integer',
            'deadline' => 'date',
            'suggested_schedule' => 'array',
            'forfeiture_percentage' => 'integer',
            'affiliate_commission_locked' => 'decimal:2',
            'terms_accepted_at' => 'datetime',
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'activated_at' => 'datetime',
            'completed_at' => 'datetime',
            'fulfilled_at' => 'datetime',
            'defaulted_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (InstallmentPlan $plan): void {
            if (blank($plan->reference)) {
                do {
                    $ref = 'INS-'.Str::upper(Str::random(6));
                } while (static::query()->where('reference', $ref)->exists());
                $plan->reference = $ref;
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function installable(): MorphTo
    {
        return $this->morphTo();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InstallmentPayment::class)->latest('paid_at');
    }

    public function refund(): HasOne
    {
        return $this->hasOne(InstallmentRefund::class);
    }

    public function termsAgreement(): HasOne
    {
        return $this->hasOne(InstallmentTermsAgreement::class);
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function remainingAmount(): float
    {
        return max(0, (float) $this->total_amount - (float) $this->amount_paid);
    }

    public function progressPercentage(): float
    {
        if ((float) $this->total_amount <= 0) {
            return 0;
        }
        return min(100, ((float) $this->amount_paid / (float) $this->total_amount) * 100);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isComplete(): bool
    {
        return in_array($this->status, ['completed', 'awaiting_fulfillment', 'fulfilled'], true);
    }

    public function isFinished(): bool
    {
        return in_array($this->status, ['fulfilled', 'refunded'], true);
    }

    public function canAcceptPayment(): bool
    {
        return in_array($this->status, ['awaiting_down_payment', 'active'], true);
    }

    protected function isOverdue(): Attribute
    {
        return Attribute::get(fn (): bool => $this->isActive()
            && $this->deadline
            && $this->deadline->isPast());
    }
}
