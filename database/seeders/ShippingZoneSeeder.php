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
                        default => 99,
                    },
                ]
            );
        }

        // Eliminar zonas viejas que ya no existen
        ShippingZone::whereNotIn('zone_key', array_keys($zones))->delete();
    }
}
