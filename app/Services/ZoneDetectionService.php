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

        // 1. Intentar buscar en la base de datos local
        $zoneKey = $this->detectLocal($normalized, $number);
        if ($zoneKey) {
            return $zoneKey;
        }

        // 2. Fallback: geocodificar usando Nominatim de OpenStreetMap
        return $this->geocodeFallback($street, $number);
    }

    /**
     * Busca la calle y altura directamente en la base de datos local.
     */
    private function detectLocal(string $normalized, ?int $number = null): ?string
    {
        $zoneKey = $this->queryLocalExact($normalized, $number);
        if ($zoneKey) {
            return $zoneKey;
        }

        $allZones = StreetZone::all();
        foreach ($allZones as $zone) {
            $dbStreet = $zone->street_name;
            if (str_ends_with($normalized, ' ' . $dbStreet) || str_starts_with($normalized, $dbStreet . ' ')) {
                if ($number !== null) {
                    if (($zone->number_from === null || $number >= $zone->number_from) &&
                        ($zone->number_to === null || $number <= $zone->number_to)) {
                        return $zone->zone_key;
                    }
                } else {
                    if ($zone->number_from === null && $zone->number_to === null) {
                        return $zone->zone_key;
                    }
                }
            }
        }

        return null;
    }

    private function queryLocalExact(string $normalized, ?int $number = null): ?string
    {
        $query = StreetZone::where('street_name', $normalized);

        if ($number !== null) {
            $withRange = (clone $query)
                ->whereNotNull('number_from')
                ->whereNotNull('number_to')
                ->where('number_from', '<=', $number)
                ->where('number_to', '>=', $number)
                ->first();

            if ($withRange) {
                return $withRange->zone_key;
            }

            $noRange = (clone $query)
                ->whereNull('number_from')
                ->first();

            if ($noRange) {
                $boundaryZone = $this->resolveBoundaryZone($normalized, $number, $noRange->zone_key);
                if ($boundaryZone !== null) {
                    return $boundaryZone;
                }
            }

            return $noRange?->zone_key;
        }

        return $query->whereNull('number_from')->first()?->zone_key;
    }

    /**
     * Hace un lookup en OpenStreetMap Nominatim para obtener la calle oficial
     * y el suburbio (barrio) de la dirección ingresada, e intenta mapearlo.
     */
    private function geocodeFallback(string $street, ?int $number = null): ?string
    {
        $query = $street;
        if ($number !== null) {
            $query .= ' ' . $number;
        }
        $query .= ', San Carlos de Bariloche, Rio Negro, Argentina';

        $googleKey = config('services.google_maps.key');

        if ($googleKey) {
            try {
                $url = 'https://maps.googleapis.com/maps/api/geocode/json?' . http_build_query([
                    'address' => $query,
                    'key' => $googleKey,
                ]);

                $response = file_get_contents($url);
                if ($response) {
                    $data = json_decode($response, true);
                    if (($data['status'] ?? '') === 'OK' && !empty($data['results'])) {
                        $result = $data['results'][0];

                        if (isset($result['geometry']['location']['lat']) && isset($result['geometry']['location']['lng'])) {
                            $coordZone = $this->checkCoordinateFallback(
                                (float) $result['geometry']['location']['lat'],
                                (float) $result['geometry']['location']['lng']
                            );
                            if ($coordZone) {
                                return $coordZone;
                            }
                        }

                        $components = $result['address_components'] ?? [];

                        $road = null;
                        $suburbs = [];

                        foreach ($components as $comp) {
                            $types = $comp['types'] ?? [];
                            if (in_array('route', $types, true)) {
                                $road = $comp['long_name'];
                            }
                            if (array_intersect(['neighborhood', 'sublocality', 'sublocality_level_1', 'sublocality_level_2'], $types)) {
                                $suburbs[] = $comp['long_name'];
                            }
                        }

                        if ($road) {
                            $roadNormalized = $this->normalize($road);
                            $zoneFromRoad = $this->detectLocal($roadNormalized, $number);
                            if ($zoneFromRoad) {
                                return $zoneFromRoad;
                            }
                        }

                        foreach ($suburbs as $suburb) {
                            $suburbNormalized = $this->normalize($suburb);
                            $zoneFromSuburb = $this->detectLocal($suburbNormalized, null);
                            if ($zoneFromSuburb) {
                                return $zoneFromSuburb;
                            }

                            $words = preg_split('/[\s,\-_]+/', $suburbNormalized);
                            foreach ($words as $word) {
                                if (strlen($word) > 3) {
                                    $zoneFromWord = $this->detectLocal($word, null);
                                    if ($zoneFromWord) {
                                        return $zoneFromWord;
                                    }
                                }
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Silencioso
            }
        }

        try {
            $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
                'q' => $query,
                'format' => 'json',
                'addressdetails' => 1,
                'limit' => 1,
                'countrycodes' => 'ar',
            ]);

            $options = [
                'http' => [
                    'header' => "User-Agent: DinoApp/1.0 (contact@baritienda.online)\r\n",
                    'timeout' => 3,
                ]
            ];
            $context = stream_context_create($options);
            $response = file_get_contents($url, false, $context);

            if (!$response) {
                return null;
            }

            $places = json_decode($response, true);
            $match = is_array($places) && isset($places[0]) ? $places[0] : null;

            if (!$match) {
                return null;
            }

            if (isset($match['lat']) && isset($match['lon'])) {
                $coordZone = $this->checkCoordinateFallback(
                    (float) $match['lat'],
                    (float) $match['lon']
                );
                if ($coordZone) {
                    return $coordZone;
                }
            }

            $address = $match['address'] ?? [];

            // A. Intentar con el nombre oficial de la calle ("road")
            if (isset($address['road'])) {
                $roadNormalized = $this->normalize($address['road']);
                $zoneFromRoad = $this->detectLocal($roadNormalized, $number);
                if ($zoneFromRoad) {
                    return $zoneFromRoad;
                }
            }

            // B. Intentar buscar por el suburbio o barrio ("suburb", "neighbourhood", "hamlet", "village")
            $suburbKeys = ['suburb', 'neighbourhood', 'hamlet', 'village'];
            foreach ($suburbKeys as $key) {
                if (isset($address[$key])) {
                    $suburbNormalized = $this->normalize($address[$key]);

                    // 1. Buscar coincidencia exacta del suburbio
                    $zoneFromSuburb = $this->detectLocal($suburbNormalized, null);
                    if ($zoneFromSuburb) {
                        return $zoneFromSuburb;
                    }

                    // 2. Buscar coincidencia por palabra (ej: "Belgrano Sudeste" -> probar con "Belgrano")
                    $words = preg_split('/[\s,\-_]+/', $suburbNormalized);
                    foreach ($words as $word) {
                        if (strlen($word) > 3) {
                            $zoneFromWord = $this->detectLocal($word, null);
                            if ($zoneFromWord) {
                                  return $zoneFromWord;
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Silencioso
        }

        return null;
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

    /**
     * Permite aplicar reglas globales de corte por altura para calles
     * que estaban cargadas como "toda la calle".
     */
    private function resolveBoundaryZone(string $street, int $number, string $currentZone): ?string
    {
        $rule = config('shipping.height_boundary_rule', []);

        if (!($rule['enabled'] ?? false)) {
            return null;
        }

        $fromNumber = (int) ($rule['from_number'] ?? 0);
        $fromZone   = (string) ($rule['from_zone'] ?? '');
        $toZone     = (string) ($rule['to_zone'] ?? '');
        $streets    = (array) ($rule['streets'] ?? []);

        if ($fromNumber <= 0 || $fromZone === '' || $toZone === '' || empty($streets)) {
            return null;
        }

        if ($currentZone !== $fromZone) {
            return null;
        }

        if ($number < $fromNumber) {
            return null;
        }

        $normalizedStreets = array_map(fn ($name) => $this->normalize((string) $name), $streets);

        if (in_array($street, $normalizedStreets, true)) {
            return $toZone;
        }

        return null;
    }

    /**
     * Realiza geocodificación inversa para obtener calle y altura.
     */
    public function reverseGeocode(float $lat, float $lon): array
    {
        $googleKey = config('services.google_maps.key');

        if ($googleKey) {
            try {
                $url = 'https://maps.googleapis.com/maps/api/geocode/json?' . http_build_query([
                    'latlng' => "{$lat},{$lon}",
                    'key' => $googleKey,
                ]);

                $response = file_get_contents($url);
                if ($response) {
                    $data = json_decode($response, true);
                    if (($data['status'] ?? '') === 'OK' && !empty($data['results'])) {
                        $result = $data['results'][0];
                        $components = $result['address_components'] ?? [];

                        $street = '';
                        $number = '';

                        foreach ($components as $comp) {
                            $types = $comp['types'] ?? [];
                            if (in_array('route', $types, true)) {
                                $street = $comp['long_name'];
                            }
                            if (in_array('street_number', $types, true)) {
                                $number = $comp['long_name'];
                            }
                        }

                        if ($street !== '') {
                            return [
                                'street' => $street,
                                'number' => $number,
                                'label' => $result['formatted_address'] ?? "{$street} {$number}",
                            ];
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Silencioso
            }
        }

        try {
            $url = 'https://nominatim.openstreetmap.org/reverse?' . http_build_query([
                'lat' => $lat,
                'lon' => $lon,
                'format' => 'json',
                'addressdetails' => 1,
            ]);

            $options = [
                'http' => [
                    'header' => "User-Agent: DinoApp/1.0 (contact@baritienda.online)\r\n",
                    'timeout' => 3,
                ]
            ];
            $context = stream_context_create($options);
            $response = file_get_contents($url, false, $context);

            if ($response) {
                $data = json_decode($response, true);
                $address = $data['address'] ?? [];
                $street = $address['road'] ?? $address['pedestrian'] ?? '';
                $number = $address['house_number'] ?? '';

                if ($street !== '') {
                    return [
                        'street' => $street,
                        'number' => $number,
                        'label' => $data['display_name'] ?? "{$street} {$number}",
                    ];
                }
            }
        } catch (\Throwable $e) {
            // Silencioso
        }

        return [
            'street' => null,
            'number' => null,
            'label' => null,
        ];
    }

    /**
     * Verifica si las coordenadas caen al oeste de 20 de Junio y asigna zona_6000.
     */
    private function checkCoordinateFallback(float $lat, float $lon): ?string
    {
        if ($lat < -41.25 || $lat > -41.05) {
            return null;
        }

        if ($lon < -71.316) {
            return 'zone_6000';
        }

        return null;
    }
}
