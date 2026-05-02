<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key', 'value'
    ];
    public $timestamps = false;

    private static ?array $allKeyValueCache = null;
    private static array $valueCache = [];

    protected static function booted(): void
    {
        static::saved(function (): void {
            static::flushRuntimeCache();
        });

        static::deleted(function (): void {
            static::flushRuntimeCache();
        });
    }

    public static function allAsKeyValue(): array
    {
        if (self::$allKeyValueCache !== null) {
            return self::$allKeyValueCache;
        }

        try {
            return self::$allKeyValueCache = self::query()->pluck('value', 'key')->all();
        } catch (\Throwable $e) {
            return self::$allKeyValueCache = [];
        }
    }

    public static function getMany(array $keys, array $defaults = []): array
    {
        $settings = self::allAsKeyValue();
        $resolved = [];

        foreach ($keys as $key) {
            if (array_key_exists($key, $settings)) {
                $resolved[$key] = $settings[$key];
                continue;
            }

            $resolved[$key] = $defaults[$key] ?? null;
        }

        return $resolved;
    }

    /**
     * Get a setting value by key
     */
    public static function getValue($key, $default = null)
    {
        if (array_key_exists($key, self::$valueCache)) {
            return self::$valueCache[$key];
        }

        $settings = self::allAsKeyValue();
        if (array_key_exists($key, $settings)) {
            return self::$valueCache[$key] = $settings[$key];
        }

        return $default;
    }

    /**
     * Set a setting value by key
     */
    public static function setValue($key, $value)
    {
        $setting = self::updateOrCreate(['key' => $key], ['value' => $value]);
        static::flushRuntimeCache();

        return $setting;
    }

    public static function flushRuntimeCache(): void
    {
        self::$allKeyValueCache = null;
        self::$valueCache = [];
    }
}
