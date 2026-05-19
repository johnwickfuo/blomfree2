<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'orderable_type',
        'orderable_id',
        'item_name',
        'item_label',
        'unit_price',
        'quantity',
        'subtotal',
        'meta',
        'normal_unit_price',
        'affiliate_unit_commission',
        'affiliate_eligible',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'normal_unit_price' => 'decimal:2',
            'affiliate_unit_commission' => 'decimal:2',
            'affiliate_eligible' => 'boolean',
            'quantity' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderable(): MorphTo
    {
        return $this->morphTo();
    }
}
