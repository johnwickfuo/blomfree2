<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class InstallmentPayment extends Model
{
    public const STATUSES = ['pending', 'paid', 'failed', 'reversed'];

    public const GATEWAYS = ['paystack', 'flutterwave', 'manual'];

    protected $fillable = [
        'installment_plan_id',
        'reference',
        'amount',
        'payment_gateway',
        'payment_reference',
        'payment_status',
        'paid_at',
        'is_down_payment',
        'gateway_response',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'is_down_payment' => 'boolean',
            'gateway_response' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (InstallmentPayment $p): void {
            if (blank($p->reference)) {
                do {
                    $ref = 'PAY-'.Str::upper(Str::random(6));
                } while (static::query()->where('reference', $ref)->exists());
                $p->reference = $ref;
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(InstallmentPlan::class, 'installment_plan_id');
    }
}
