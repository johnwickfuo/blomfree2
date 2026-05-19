<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, ?string $default = null): ?string
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $key, string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Read a comma-separated setting as a trimmed list of values.
     *
     * @return list<string>
     */
    public static function list(string $key, ?string $default = null): array
    {
        $raw = static::get($key, $default);

        if (blank($raw)) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }
}
