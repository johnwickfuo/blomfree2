<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Land extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const DOCUMENT_TYPES = [
        'c_of_o' => 'Certificate of Occupancy',
        'governors_consent' => "Governor's Consent",
        'deed_of_assignment' => 'Deed of Assignment',
        'survey_plan' => 'Survey Plan',
        'receipt_of_purchase' => 'Receipt of Purchase',
    ];

    public const STATUSES = ['available', 'sold', 'reserved', 'reserved_installment'];

    protected $fillable = [
        'title',
        'slug',
        'location_address',
        'state',
        'city_or_lga',
        'number_of_plots',
        'plot_size_sqm',
        'price_per_plot',
        'price_label',
        'description',
        'features',
        'close_to_landmarks',
        'has_good_access_road',
        'is_flood_free',
        'installment_available',
        'installment_enabled',
        'installment_minimum_down_payment_percentage',
        'installment_maximum_length_months',
        'document_status',
        'status',
        'is_featured',
        'order',
    ];

    protected $appends = ['cover_url', 'total_price'];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'close_to_landmarks' => 'array',
            'document_status' => 'array',
            'has_good_access_road' => 'boolean',
            'is_flood_free' => 'boolean',
            'installment_available' => 'boolean',
            'installment_enabled' => 'boolean',
            'installment_minimum_down_payment_percentage' => 'integer',
            'installment_maximum_length_months' => 'integer',
            'is_featured' => 'boolean',
            'price_per_plot' => 'decimal:2',
            'number_of_plots' => 'integer',
            'plot_size_sqm' => 'integer',
            'order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Land $land): void {
            if (blank($land->slug)) {
                $land->slug = static::uniqueSlug($land->title);
            }
        });
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'land';
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

    public function inspections(): MorphMany
    {
        return $this->morphMany(Inspection::class, 'inspectable');
    }

    public function installmentPlans(): MorphMany
    {
        return $this->morphMany(InstallmentPlan::class, 'installable');
    }

    public function installmentAvailableForPurchase(): bool
    {
        return $this->installment_enabled
            && $this->installment_minimum_down_payment_percentage !== null
            && $this->installment_maximum_length_months !== null
            && $this->status === 'available';
    }

    public function effectiveMinimumDownPaymentPercentage(): ?int
    {
        return $this->installment_minimum_down_payment_percentage;
    }

    public function effectiveMaximumLengthMonths(): ?int
    {
        return $this->installment_maximum_length_months;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('gallery');
    }

    public function registerMediaConversions(?\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        $this->addMediaConversion('responsive')->withResponsiveImages();
    }

    protected function coverUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            $cover = $this->getFirstMediaUrl('cover');

            return $cover !== '' ? $cover : ($this->getFirstMediaUrl('gallery') ?: null);
        });
    }

    protected function galleryUrls(): Attribute
    {
        return Attribute::get(function (): array {
            $urls = [];

            $cover = $this->getFirstMediaUrl('cover');
            if ($cover !== '') {
                $urls[] = $cover;
            }

            foreach ($this->getMedia('gallery') as $media) {
                $urls[] = $media->getUrl();
            }

            return $urls;
        });
    }

    protected function totalPrice(): Attribute
    {
        return Attribute::get(
            fn (): float => (float) $this->price_per_plot * (int) $this->number_of_plots,
        );
    }
}
