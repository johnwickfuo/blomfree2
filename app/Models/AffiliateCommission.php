<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateCommission extends Model
{
    public const STATUSES = ['pending', 'available', 'reversed', 'withdrawn'];

    protected $fillable = [
        'affiliate_id',
        'order_id',
        'order_item_id',
        'amount',
        'status',
        'earned_at',
        'available_at',
        'reversed_at',
        'reversal_reason',
        'withdrawal_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'earned_at' => 'datetime',
            'available_at' => 'datetime',
            'reversed_at' => 'datetime',
        ];
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function withdrawal(): BelongsTo
    {
        return $this->belongsTo(AffiliateWithdrawal::class);
    }
}
