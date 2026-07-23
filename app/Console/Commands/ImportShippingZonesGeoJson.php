<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ShippingZone;
use Illuminate\Support\Facades\Storage;

class ImportShippingZonesGeoJson extends Command
{
    protected $signature = 'shipping:import-geojson {file=mapa_bariloche.geojson}';
    protected $description = 'Importa las zonas de envío desde un archivo GeoJSON en storage/app/';

    public function handle()
    {
        $file = $this->argument('file');
        $filePath = storage_path('app/' . $file);
        
        if (!file_exists($filePath)) {
            $this->error("El archivo {$file} no existe en storage/app/ (ruta esperada: {$filePath}).");
            return 1;
        }

        $geojson = json_decode(file_get_contents($filePath), true);

        if (!isset($geojson['features'])) {
            $this->error('Formato GeoJSON inválido: no contiene features.');
            return 1;
        }

        $importedKeys = [];
        $count = 0;

        foreach ($geojson['features'] as $feature) {
            $properties = $feature['properties'] ?? [];
            $geometry = $feature['geometry'] ?? [];

            $zoneKey = $properties['zone_key'] ?? null;
            $label = $properties['label'] ?? null;
            $price = $properties['price'] ?? null;

            if (!$zoneKey || !$label) {
                $this->warn("Omitiendo feature sin zone_key o label.");
                continue;
            }

            if ($geometry['type'] !== 'Polygon') {
                $this->warn("Omitiendo {$zoneKey}: solo se soportan geometrías de tipo Polygon.");
                continue;
            }

            // Almacenamos el array de coordenadas del polígono
            $coordinates = $geometry['coordinates'];

            ShippingZone::updateOrCreate(
                ['zone_key' => $zoneKey],
                [
                    'label' => $label,
                    'price' => (int) ($price ?? 0),
                    'coordinates' => $coordinates,
                    'is_active' => true,
                    'sort_order' => 100,
                ]
            );

            $this->info("✓ Zona importada: {$label} ({$zoneKey}) - Envio: {$price}");
            $importedKeys[] = $zoneKey;
            $count++;
        }

        // Desactivar zonas que ya no están en el GeoJSON para mantener consistencia
        $deactivatedCount = ShippingZone::whereNotIn('zone_key', $importedKeys)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        if ($deactivatedCount > 0) {
            $this->info("ℹ Se desactivaron {$deactivatedCount} zonas viejas que no estaban en el archivo GeoJSON.");
        }

        $this->info("🎉 Importación completada con éxito. Se procesaron {$count} zonas.");
        return 0;
    }
}
