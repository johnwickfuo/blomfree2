<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const SUBSIDIARIES = [
        'collections' => 'Collections',
        'gadgets' => 'Gadgets',
    ];

    protected $fillable = [
        'name',
        'slug',
        'subsidiary',
        'category',
        'short_description',
        'description',
        'base_price',
        'compare_price',
        'has_variants',
        'stock',
        'attribute_keys',
        'is_featured',
        'is_published',
        'order',
        'affiliate_price',
        'affiliate_commission',
        'affiliate_enabled',
        'installment_enabled',
        'installment_minimum_down_payment_percentage',
        'installment_maximum_length_months',
    ];

    protected $appends = ['cover_url', 'is_in_stock', 'display_price'];

    protected function casts(): array
    {
        return [
            'attribute_keys' => 'array',
            'base_price' => 'decimal:2',
            'compare_price' => 'decimal:2',
            'affiliate_price' => 'decimal:2',
            'affiliate_commission' => 'decimal:2',
            'affiliate_enabled' => 'boolean',
            'installment_enabled' => 'boolean',
            'installment_minimum_down_payment_percentage' => 'integer',
            'installment_maximum_length_months' => 'integer',
            'has_variants' => 'boolean',
            'stock' => 'integer',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            if (blank($product->slug)) {
                $product->slug = static::uniqueSlug($product->name);
            }
        });

        static::saving(function (Product $product): void {
            if ($product->has_variants) {
                $product->stock = null;
            } else {
                $product->stock ??= 0;
                $product->attribute_keys = [];
            }
            $product->attribute_keys ??= [];

            // Derive affiliate_enabled from whether both pricing fields are set
            // and commission is > 0.
            $product->affiliate_enabled = $product->affiliate_price !== null
                && $product->affiliate_commission !== null
                && (float) $product->affiliate_commission > 0
                && (float) $product->affiliate_price <= (float) $product->base_price;
        });
    }

    public function isAffiliateEnabled(): bool
    {
        return (bool) $this->affiliate_enabled;
    }

    public function installmentPlans(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(InstallmentPlan::class, 'installable');
    }

    public function installmentAvailableForPurchase(): bool
    {
        return $this->subsidiary === 'gadgets'
            && $this->installment_enabled
            && $this->installment_minimum_down_payment_percentage !== null
            && $this->installment_maximum_length_months !== null;
    }

    public function effectiveMinimumDownPaymentPercentage(): ?int
    {
        return $this->installment_minimum_down_payment_percentage;
    }

    public function effectiveMaximumLengthMonths(): ?int
    {
        return $this->installment_maximum_length_months;
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'product';
        $slug = $base;
        $suffix = 1;

        while (
            static::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images');
    }

    public function registerMediaConversions(?\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        $this->addMediaConversion('responsive')->withResponsiveImages();
    }

    protected function coverUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->getFirstMediaUrl('images') ?: null);
    }

    /**
     * @return list<string>
     */
    public function galleryUrls(): array
    {
        return $this->getMedia('images')->map(fn ($media): string => $media->getUrl())->all();
    }

    /**
     * Variant products are in stock when any variant has stock; otherwise the
     * product's own stock field is used.
     */
    protected function isInStock(): Attribute
    {
        return Attribute::get(function (): bool {
            if ($this->has_variants) {
                return $this->variants->contains(fn (ProductVariant $variant): bool => $variant->stock > 0);
            }

            return ($this->stock ?? 0) > 0;
        });
    }

    /**
     * The base price, unless every variant shares the exact same price override.
     */
    protected function displayPrice(): Attribute
    {
        return Attribute::get(function (): float {
            if (! $this->has_variants) {
                return (float) $this->base_price;
            }

            $overrides = $this->variants->pluck('price_override');

            if (
                $overrides->isNotEmpty()
                && $overrides->every(fn ($override): bool => $override !== null)
                && $overrides->map(fn ($override): string => (string) $override)->unique()->count() === 1
            ) {
                return (float) $overrides->first();
            }

            return (float) $this->base_price;
        });
    }
}
