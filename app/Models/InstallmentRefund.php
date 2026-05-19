<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class InstallmentRefund extends Model
{
    public const STATUSES = ['pending', 'approved', 'paid', 'rejected'];

    public const TRIGGERS = [
        'land_resold', 'gadget_default', 'customer_cancelled',
        'item_unavailable_at_completion', 'admin_override',
    ];

    protected $fillable = [
        'installment_plan_id',
        'reference',
        'amount_paid_total',
        'forfeiture_amount',
        'refund_amount',
        'triggered_by',
        'status',
        'bank_snapshot',
        'admin_notes',
        'payment_reference',
        'requested_at',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_paid_total' => 'decimal:2',
            'forfeiture_amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'bank_snapshot' => 'array',
            'requested_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (InstallmentRefund $r): void {
            if (blank($r->reference)) {
                do {
                    $ref = 'REF-'.Str::upper(Str::random(6));
                } while (static::query()->where('reference', $ref)->exists());
                $r->reference = $ref;
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
