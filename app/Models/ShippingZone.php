<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    protected $fillable = [
        'name',
        'price',
        'states',
        'delivery_estimate_days',
        'is_active',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'states' => 'array',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'order' => 'integer',
        ];
    }

    /**
     * The active shipping zone covering the given Nigerian state, or null if
     * no active zone covers it. On overlap, the zone with the lowest `order`
     * wins (admin can adjust ordering to control this).
     */
    public static function findForState(string $state): ?self
    {
        return static::query()
            ->where('is_active', true)
            ->whereJsonContains('states', $state)
            ->orderBy('order')
            ->first();
    }
}
