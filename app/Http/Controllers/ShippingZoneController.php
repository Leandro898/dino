<?php

namespace App\Http\Controllers;

use App\Models\ShippingZone;
use App\Services\ZoneDetectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingZoneController extends Controller
{
    public function __construct(private ZoneDetectionService $detector) {}

    /**
     * GET /shipping/street-suggestions?q=alba
     */
    public function suggestions(Request $request): JsonResponse
    {
        return response()->json([
            'suggestions' => [],
        ]);
    }

    /**
     * GET /shipping/detect-zone?street=albarracin&number=1430
     */
    public function detect(Request $request): JsonResponse
    {
        $street = trim($request->query('street', ''));
        $number = $request->query('number');

        if (empty($street)) {
            return response()->json(['zone_key' => null]);
        }

        $zoneKey = $this->detector->detect($street, $number ? (int) $number : null);

        if (!$zoneKey) {
            return response()->json(['zone_key' => null]);
        }

        $zones = ShippingZone::getActiveWithPrices();

        $zone  = $zones[$zoneKey] ?? null;

        return response()->json([
            'zone_key'   => $zoneKey,
            'zone_label' => $zone['label'] ?? $zoneKey,
            'zone_price' => $zone['price'] ?? 0,
        ]);
    }

    /**
     * GET /shipping/reverse-geocode?lat=-41.133&lon=-71.31
     */
    public function reverseGeocode(Request $request): JsonResponse
    {
        $lat = (float) $request->query('lat');
        $lon = (float) $request->query('lon');

        if (!$lat || !$lon) {
            return response()->json([
                'street' => null,
                'number' => null,
                'label' => null,
            ]);
        }

        $result = $this->detector->reverseGeocode($lat, $lon);

        if (!empty($result['street'])) {
            $result['success'] = true;
            $result['zone_key'] = $this->detector->detectByCoordinates($lat, $lon);
        } else {
            $result['success'] = false;
        }

        return response()->json($result);
    }
}
