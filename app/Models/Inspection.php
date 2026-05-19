<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Inspection extends Model
{
    public const STATUSES = ['pending', 'approved', 'rejected', 'completed', 'no_show'];

    /**
     * Models that may be inspected, keyed by the public `inspectable_type` value.
     *
     * @var array<string, class-string<\Illuminate\Database\Eloquent\Model>>
     */
    public const INSPECTABLE_TYPES = [
        'land' => Land::class,
        'animal' => Animal::class,
    ];

    protected $fillable = [
        'inspectable_type',
        'inspectable_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'preferred_date',
        'preferred_time_slot',
        'alternate_date',
        'party_size',
        'notes',
        'status',
        'meeting_address',
        'meeting_instructions',
        'admin_notes',
        'reference',
    ];

    protected function casts(): array
    {
        return [
            'preferred_date' => 'date',
            'alternate_date' => 'date',
            'party_size' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Inspection $inspection): void {
            if (blank($inspection->reference)) {
                $inspection->reference = static::generateReference();
            }
        });
    }

    public static function generateReference(): string
    {
        do {
            $reference = 'INSP-'.Str::upper(Str::random(6));
        } while (static::query()->where('reference', $reference)->exists());

        return $reference;
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function inspectable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
