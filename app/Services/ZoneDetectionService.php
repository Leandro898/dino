<?php

namespace App\Services;

class ZoneDetectionService
{
    /**
     * Detecta la zona de envío a partir del nombre de calle y altura.
     * Retorna la zone_key o null si no se encontró.
     */
    public function detect(string $street, ?int $number = null): ?string
    {
        return $this->geocodeFallback($street, $number);
    }

    /**
     * Obtiene las coordenadas (lat, lng) de una dirección de texto.
     */
    public function getCoordinates(string $address): ?array
    {
        $query = $address;
        
        if (empty(trim($query))) {
            return null;
        }

        if (strpos(strtolower($query), 'bariloche') === false) {
            $query .= ', San Carlos de Bariloche, Rio Negro, Argentina';
        }

        // 1. Google Maps Geocoding
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
                            return [
                                'lat' => (float) $result['geometry']['location']['lat'],
                                'lng' => (float) $result['geometry']['location']['lng']
                            ];
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Silencioso
            }
        }

        // 2. OpenStreetMap Nominatim Geocoding
        try {
            $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
                'q' => $query,
                'format' => 'json',
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

            if ($response) {
                $places = json_decode($response, true);
                if (is_array($places) && isset($places[0]['lat']) && isset($places[0]['lon'])) {
                    return [
                        'lat' => (float) $places[0]['lat'],
                        'lng' => (float) $places[0]['lon']
                    ];
                }
            }
        } catch (\Throwable $e) {
            // Silencioso
        }

        return null;
    }

    /**
     * Hace un lookup en OpenStreetMap Nominatim / Google Maps para obtener las coordenadas
     * de la dirección ingresada, e intenta mapearlo a un polígono.
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
                            $coordZone = $this->detectByCoordinates(
                                (float) $result['geometry']['location']['lat'],
                                (float) $result['geometry']['location']['lng']
                            );
                            if ($coordZone) {
                                return $coordZone;
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
                $coordZone = $this->detectByCoordinates(
                    (float) $match['lat'],
                    (float) $match['lon']
                );
                if ($coordZone) {
                    return $coordZone;
                }
            }
        } catch (\Throwable $e) {
            // Silencioso
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
     * Detecta la zona de envío a partir de coordenadas geográficas.
     */
    public function detectByCoordinates(float $lat, float $lon): ?string
    {
        $zones = \App\Models\ShippingZone::query()
            ->where('is_active', true)
            ->whereNotNull('coordinates')
            ->orderBy('sort_order')
            ->get(['zone_key', 'coordinates']);

        foreach ($zones as $zone) {
            $polygon = $zone->coordinates;
            
            // Si viene como string, decodificarlo
            if (is_string($polygon)) {
                $polygon = json_decode($polygon, true);
            }
            
            // Auto-unwrap GeoJSON outer ring array [[[lng, lat], ...]]
            if (is_array($polygon) && isset($polygon[0]) && is_array($polygon[0]) && isset($polygon[0][0]) && is_array($polygon[0][0])) {
                $polygon = $polygon[0];
            }

            if (is_array($polygon) && count($polygon) >= 3) {
                if ($this->isPointInPolygon($lat, $lon, $polygon)) {
                    return $zone->zone_key;
                }
            }
        }

        // Si no coincide con ningún polígono de la DB, usar el fallback por coordenadas
        return $this->checkCoordinateFallback($lat, $lon);
    }

    /**
     * Algoritmo Ray-Casting (Jordan curve theorem) para verificar si un punto está dentro de un polígono.
     */
    public function isPointInPolygon(float $latitude, float $longitude, array $polygon): bool
    {
        $inside = false;
        $numVertices = count($polygon);
        
        $j = $numVertices - 1;
        for ($i = 0; $i < $numVertices; $i++) {
            $vertex1 = $polygon[$i];
            $vertex2 = $polygon[$j];
            
            $val1_0 = isset($vertex1[0]) ? (float) $vertex1[0] : (float) ($vertex1['lat'] ?? $vertex1['latitude'] ?? 0);
            $val1_1 = isset($vertex1[1]) ? (float) $vertex1[1] : (float) ($vertex1['lng'] ?? $vertex1['longitude'] ?? $vertex1['lon'] ?? 0);
            
            $val2_0 = isset($vertex2[0]) ? (float) $vertex2[0] : (float) ($vertex2['lat'] ?? $vertex2['latitude'] ?? 0);
            $val2_1 = isset($vertex2[1]) ? (float) $vertex2[1] : (float) ($vertex2['lng'] ?? $vertex2['longitude'] ?? $vertex2['lon'] ?? 0);

            // Auto-detect [lng, lat] (GeoJSON format, e.g. -71.xxx is longitude) vs [lat, lng]
            if ($val1_0 < -60) {
                $lat1 = $val1_1;
                $lng1 = $val1_0;
            } else {
                $lat1 = $val1_0;
                $lng1 = $val1_1;
            }

            if ($val2_0 < -60) {
                $lat2 = $val2_1;
                $lng2 = $val2_0;
            } else {
                $lat2 = $val2_0;
                $lng2 = $val2_1;
            }
            
            $intersect = (($lng1 > $longitude) !== ($lng2 > $longitude))
                && ($latitude < ($lat2 - $lat1) * ($longitude - $lng1) / ($lng2 - $lng1) + $lat1);
                
            if ($intersect) {
                $inside = !$inside;
            }
            $j = $i;
        }
        
        return $inside;
    }

    /**
     * Verifica si las coordenadas caen al oeste de 20 de Junio y asigna zona_2.
     */
    private function checkCoordinateFallback(float $lat, float $lon): ?string
    {
        if ($lat < -41.25 || $lat > -41.05) {
            return null;
        }

        if ($lon < -71.316) {
            return 'zona_2';
        }

        return null;
    }
}
