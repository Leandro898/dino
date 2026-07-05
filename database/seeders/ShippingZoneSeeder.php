<?php

namespace Database\Seeders;

use App\Models\ShippingZone;
use Illuminate\Database\Seeder;

class ShippingZoneSeeder extends Seeder
{
    public function run(): void
    {
        $zones = config('shipping.zones', []);

        foreach ($zones as $zoneKey => $zone) {
            ShippingZone::updateOrCreate(
                ['zone_key' => $zoneKey],
                [
                    'label' => (string) ($zone['label'] ?? $zoneKey),
                    'price' => (int) ($zone['price'] ?? 0),
                    'is_active' => true,
                    'sort_order' => match ($zoneKey) {
                        'centro' => 1,
                        'belgrano_melipal' => 2,
                        'exterior' => 3,
                        default => 99,
                    },
                ]
            );
        }
    }
}
