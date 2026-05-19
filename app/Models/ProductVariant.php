<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ProductVariant extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'product_id',
        'attributes',
        'sku',
        'price_override',
        'stock',
        'affiliate_price',
        'affiliate_commission',
        'affiliate_enabled',
        'installment_enabled',
        'installment_minimum_down_payment_percentage',
        'installment_maximum_length_months',
    ];

    protected $appends = ['attributes_label'];

    protected function casts(): array
    {
        return [
            'attributes' => 'array',
            'price_override' => 'decimal:2',
            'affiliate_price' => 'decimal:2',
            'affiliate_commission' => 'decimal:2',
            'affiliate_enabled' => 'boolean',
            'installment_enabled' => 'boolean',
            'installment_minimum_down_payment_percentage' => 'integer',
            'installment_maximum_length_months' => 'integer',
            'stock' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ProductVariant $variant): void {
            // Variant-level affiliate_enabled mirrors its own pricing fields.
            $variant->affiliate_enabled = $variant->affiliate_price !== null
                && $variant->affiliate_commission !== null
                && (float) $variant->affiliate_commission > 0;
        });
    }

    /**
     * Affiliate price falls back to the parent product's price.
     */
    public function effectiveAffiliatePrice(): ?float
    {
        if ($this->affiliate_price !== null) {
            return (float) $this->affiliate_price;
        }
        return $this->product?->affiliate_price !== null
            ? (float) $this->product->affiliate_price
            : null;
    }

    /**
     * Affiliate commission falls back to the parent product's commission.
     */
    public function effectiveAffiliateCommission(): ?float
    {
        if ($this->affiliate_commission !== null) {
            return (float) $this->affiliate_commission;
        }
        return $this->product?->affiliate_commission !== null
            ? (float) $this->product->affiliate_commission
            : null;
    }

    public function isAffiliateEnabled(): bool
    {
        return $this->effectiveAffiliatePrice() !== null
            && $this->effectiveAffiliateCommission() !== null
            && $this->effectiveAffiliateCommission() > 0;
    }

    public function installmentPlans(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(InstallmentPlan::class, 'installable');
    }

    /**
     * Variant inherits installment settings from product if its own are null.
     */
    public function effectiveMinimumDownPaymentPercentage(): ?int
    {
        return $this->installment_minimum_down_payment_percentage
            ?? $this->product?->installment_minimum_down_payment_percentage;
    }

    public function effectiveMaximumLengthMonths(): ?int
    {
        return $this->installment_maximum_length_months
            ?? $this->product?->installment_maximum_length_months;
    }

    public function installmentAvailableForPurchase(): bool
    {
        $product = $this->product;
        if (! $product || $product->subsidiary !== 'gadgets') {
            return false;
        }
        $enabled = $this->installment_enabled || ($product->installment_enabled ?? false);

        return $enabled
            && $this->effectiveMinimumDownPaymentPercentage() !== null
            && $this->effectiveMaximumLengthMonths() !== null;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function registerMediaCollections(): void
    {
        // Optional variant-specific images (e.g. the "Red" colour shot).
        $this->addMediaCollection('variant_images');
    }

    public function registerMediaConversions(?\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        $this->addMediaConversion('responsive')->withResponsiveImages();
    }

    /**
     * Human-readable variant label, e.g. "M / Red" or "128GB / Black".
     *
     * Note: the `attributes` column collides with Eloquent's internal
     * `$attributes` property, so it must be read via getAttribute() here.
     */
    protected function attributesLabel(): Attribute
    {
        return Attribute::get(
            fn (): string => collect($this->getAttribute('attributes') ?? [])->values()->implode(' / '),
        );
    }

    /**
     * The variant's own image, falling back to the parent product's cover.
     */
    public function imageUrl(): ?string
    {
        $variantImage = $this->getFirstMediaUrl('variant_images');

        if ($variantImage !== '') {
            return $variantImage;
        }

        return $this->product?->cover_url;
    }

    /**
     * The price a customer pays for this variant.
     */
    public function effectivePrice(): float
    {
        return (float) ($this->price_override ?? $this->product?->base_price ?? 0);
    }
}
