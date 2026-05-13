<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class StoreSetting extends Model
{
    protected $fillable = [
        'global_price_adjustment',
    ];

    protected $casts = [
        'global_price_adjustment' => 'float',
    ];

    public static function current(): self
    {
        return Cache::rememberForever('store_settings.current', function () {
            return static::query()->firstOrCreate([], [
                'global_price_adjustment' => 0,
            ]);
        });
    }

    public static function globalPriceAdjustment(): float
    {
        return (float) static::current()->global_price_adjustment;
    }

    public static function updateGlobalPriceAdjustment(float $amount): self
    {
        $setting = static::query()->firstOrCreate([]);
        $setting->global_price_adjustment = round(max(0, $amount), 2);
        $setting->save();

        return $setting;
    }

    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('store_settings.current');
        });

        static::deleted(function () {
            Cache::forget('store_settings.current');
        });
    }
}
