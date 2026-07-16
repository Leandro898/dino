<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StreetZone extends Model
{
    protected $fillable = [
        'street_name',
        'number_from',
        'number_to',
        'zone_key',
        'price',
    ];

    protected $casts = [
        'number_from' => 'integer',
        'number_to'   => 'integer',
        'price'       => 'integer',
    ];

    protected static function booted()
    {
        static::saving(function (StreetZone $streetZone) {
            $price = (int) ($streetZone->price ?? 5000);
            $zoneKey = 'zone_' . $price;
            $streetZone->zone_key = $zoneKey;

            \App\Models\ShippingZone::firstOrCreate(
                ['zone_key' => $zoneKey],
                [
                    'label' => 'Envio $' . number_format($price, 0, ',', '.'),
                    'price' => $price,
                    'is_active' => true,
                    'sort_order' => 100,
                ]
            );
        });
    }
}
