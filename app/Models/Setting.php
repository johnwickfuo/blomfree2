<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, ?string $default = null): ?string
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    /**
     * Resolve a setting that stores a path on the public disk into a
     * browser-accessible URL. The URL is emitted path-only (no scheme or
     * host) so the browser resolves it against the current page origin —
     * which sidesteps an APP_URL that's set to http while the site is
     * served over https (mixed-content blocking), or APP_URL pointing at
     * a slightly different hostname.
     * Returns null when the setting is empty. Appends a cache-busting
     * ?v=<mtime> when the file exists.
     */
    public static function publicUrl(string $key): ?string
    {
        $path = static::get($key);

        if (blank($path)) {
            return null;
        }

        $url = '/storage/'.ltrim($path, '/');

        $disk = Storage::disk('public');
        if ($disk->exists($path)) {
            $url .= '?v='.$disk->lastModified($path);
        }

        return $url;
    }

    public static function set(string $key, string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Read an encrypted setting value (e.g. payment gateway secret keys).
     * Falls back to $default when missing or undecryptable.
     */
    public static function getSecret(string $key, ?string $default = null): ?string
    {
        $raw = static::get($key);

        if (blank($raw)) {
            return $default;
        }

        try {
            return Crypt::decryptString($raw);
        } catch (\Throwable) {
            return $default;
        }
    }

    /**
     * Persist a setting whose value should be encrypted at rest.
     */
    public static function setSecret(string $key, ?string $value): void
    {
        if (blank($value)) {
            static::set($key, '');

            return;
        }

        static::set($key, Crypt::encryptString($value));
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
