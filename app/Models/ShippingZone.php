<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    protected $fillable = [
        'zone_key',
        'label',
        'price',
        'coordinates',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'integer',
        'coordinates' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public static function getActiveWithPrices(): array
    {
        $zones = self::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['zone_key', 'label', 'price'])
            ->mapWithKeys(fn($zone) => [
                $zone->zone_key => [
                    'label' => $zone->label,
                    'price' => (int) $zone->price,
                ],
            ])
            ->toArray();

        return !empty($zones) ? $zones : config('shipping.zones', []);
    }
}
