<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Animal extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const LISTING_TYPES = ['individual', 'pool'];

    public const CATEGORIES = [
        'dog' => 'Dog',
        'cat' => 'Cat',
        'rabbit' => 'Rabbit',
        'grasscutter' => 'Grasscutter',
        'other' => 'Other',
    ];

    public const AVAILABILITIES = ['available', 'reserved', 'sold', 'on_order'];

    public const VACCINATION_TYPES = [
        'first_shots' => 'First shots',
        'second_shots' => 'Second shots',
        'rabies' => 'Rabies',
        'dewormed' => 'Dewormed',
    ];

    protected $fillable = [
        'name',
        'slug',
        'listing_type',
        'category',
        'breed',
        'description',
        'origin',
        'sex',
        'age_text',
        'date_of_birth',
        'typical_adult_size',
        'temperament',
        'vaccination_status',
        'parents_info',
        'price',
        'stock',
        'availability',
        'supports_inspection',
        'supports_online_purchase',
        'highlights',
        'is_featured',
        'order',
        'affiliate_price',
        'affiliate_commission',
        'affiliate_enabled',
    ];

    protected $appends = ['cover_url', 'effective_availability'];

    protected function casts(): array
    {
        return [
            'vaccination_status' => 'array',
            'highlights' => 'array',
            'date_of_birth' => 'date',
            'price' => 'decimal:2',
            'affiliate_price' => 'decimal:2',
            'affiliate_commission' => 'decimal:2',
            'affiliate_enabled' => 'boolean',
            'stock' => 'integer',
            'supports_inspection' => 'boolean',
            'supports_online_purchase' => 'boolean',
            'is_featured' => 'boolean',
            'order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Animal $animal): void {
            if (blank($animal->slug)) {
                $animal->slug = static::uniqueSlug($animal->name);
            }
        });

        // Keep fields consistent with the listing type's business rules.
        static::saving(function (Animal $animal): void {
            if ($animal->listing_type === 'individual') {
                $animal->stock = null;
            } elseif ($animal->listing_type === 'pool') {
                $animal->age_text = null;
                $animal->date_of_birth = null;
                $animal->stock ??= 0;
            }

            $animal->affiliate_enabled = $animal->affiliate_price !== null
                && $animal->affiliate_commission !== null
                && (float) $animal->affiliate_commission > 0
                && (float) $animal->affiliate_price <= (float) $animal->price;
        });
    }

    public function isAffiliateEnabled(): bool
    {
        return (bool) $this->affiliate_enabled;
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'animal';
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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('gallery');
    }

    public function registerMediaConversions(?\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        $this->addMediaConversion('responsive')->withResponsiveImages();
    }

    public function isPool(): bool
    {
        return $this->listing_type === 'pool';
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

    /**
     * Individuals use their stored availability; pools are available while in stock.
     */
    protected function effectiveAvailability(): Attribute
    {
        return Attribute::get(fn (): string => $this->listing_type === 'pool'
            ? (($this->stock ?? 0) > 0 ? 'available' : 'sold')
            : ($this->availability ?? 'available'));
    }

    /**
     * Polymorphic alias so inspections can read a consistent label across
     * Land (title) and Animal (name).
     */
    protected function title(): Attribute
    {
        return Attribute::get(fn (): string => $this->name);
    }
}
