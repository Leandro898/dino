<?php

namespace App\Services;

use App\Models\StreetZone;

class ZoneDetectionService
{
    /**
     * Detecta la zona de envío a partir del nombre de calle y altura.
     * Retorna la zone_key o null si no se encontró.
     */
    public function detect(string $street, ?int $number = null): ?string
    {
        $normalized = $this->normalize($street);

        if (empty($normalized)) {
            return null;
        }

        $query = StreetZone::where('street_name', $normalized);

        if ($number !== null) {
            // Buscar primero coincidencia con rango
            $withRange = (clone $query)
                ->whereNotNull('number_from')
                ->whereNotNull('number_to')
                ->where('number_from', '<=', $number)
                ->where('number_to', '>=', $number)
                ->first();

            if ($withRange) {
                return $withRange->zone_key;
            }

            // Fallback: registro sin rango (toda la calle)
            $noRange = (clone $query)
                ->whereNull('number_from')
                ->first();

            return $noRange?->zone_key;
        }

        return $query->whereNull('number_from')->first()?->zone_key;
    }

    /**
     * Normaliza un nombre de calle: minúsculas, sin tildes, sin prefijos comunes.
     */
    public function normalize(string $street): string
    {
        $street = mb_strtolower(trim($street));

        // Reemplazar tildes y ñ
        $from = ['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ', 'à', 'è', 'ì', 'ò', 'ù'];
        $to   = ['a', 'e', 'i', 'o', 'u', 'u', 'n', 'a', 'e', 'i', 'o', 'u'];
        $street = str_replace($from, $to, $street);

        // Quitar prefijos comunes
        $prefixes = [
            'avenida ', 'av. ', 'av ', 'avda. ', 'avda ',
            'gral. ', 'gral ', 'general ', 'gral.',
            'dr. ', 'dr ', 'doctor ',
            'ing. ', 'ing ', 'ingeniero ',
            'comodoro ', 'cmdte. ', 'cmdte ',
            'comandante ', 'alm. ', 'alm ',
            'almirante ', 'pje. ', 'pje ', 'pasaje ',
            'calle ', 'ruta ',
        ];

        foreach ($prefixes as $prefix) {
            if (str_starts_with($street, $prefix)) {
                $street = substr($street, strlen($prefix));
                break;
            }
        }

        // Quitar números y caracteres especiales al final (ej: "mitre 450" → "mitre")
        $street = preg_replace('/\s+\d+.*$/', '', $street);

        return trim($street);
    }
}
