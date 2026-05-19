<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'cartable_type',
        'cartable_id',
        'quantity',
        'price_snapshot',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'price_snapshot' => 'decimal:2',
            'quantity' => 'integer',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function cartable(): MorphTo
    {
        return $this->morphTo();
    }

    public function lineTotal(): float
    {
        return (float) $this->price_snapshot * (int) $this->quantity;
    }
}
