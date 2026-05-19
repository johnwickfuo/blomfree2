<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    public const ORDER_STATUSES = [
        'pending_payment', 'paid', 'processing', 'shipped',
        'delivered', 'cancelled', 'refunded',
    ];

    public const PAYMENT_STATUSES = ['pending', 'paid', 'failed', 'cancelled'];

    public const GATEWAYS = ['paystack', 'flutterwave'];

    protected $fillable = [
        'reference',
        'customer_name',
        'customer_email',
        'customer_phone',
        'delivery_address',
        'delivery_state',
        'delivery_lga',
        'delivery_notes',
        'delivery_method',
        'shipping_zone_id',
        'shipping_fee',
        'subtotal',
        'total',
        'payment_gateway',
        'payment_reference',
        'payment_status',
        'order_status',
        'tracking_notes',
        'admin_notes',
        'placed_at',
        'paid_at',
        'affiliate_id',
        'affiliate_code_used',
        'affiliate_discount_total',
        'affiliate_commission_total',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'shipping_fee' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'affiliate_discount_total' => 'decimal:2',
            'affiliate_commission_total' => 'decimal:2',
            'placed_at' => 'datetime',
            'paid_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function affiliateCommissions(): HasMany
    {
        return $this->hasMany(AffiliateCommission::class);
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            if (blank($order->reference)) {
                $order->reference = static::generateReference();
            }
        });
    }

    public static function generateReference(): string
    {
        do {
            $reference = 'BLM-'.Str::upper(Str::random(6));
        } while (static::query()->where('reference', $reference)->exists());

        return $reference;
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(OrderRefund::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(OrderNote::class)->latest();
    }

    public function shippingZone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * True when any line item is a live animal (drives the animal-delivery
     * notes and the urgent admin alert).
     */
    public function hasAnimals(): bool
    {
        if ($this->relationLoaded('items')) {
            return $this->items->contains(
                fn (OrderItem $item): bool => $item->orderable_type === Animal::class,
            );
        }

        return $this->items()->where('orderable_type', Animal::class)->exists();
    }
}
