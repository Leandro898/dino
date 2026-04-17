<?php

namespace App\Http\Controllers;

use App\Services\ZoneDetectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingZoneController extends Controller
{
    public function __construct(private ZoneDetectionService $detector) {}

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

        $zones = config('shipping.zones', []);
        $zone  = $zones[$zoneKey] ?? null;

        return response()->json([
            'zone_key'   => $zoneKey,
            'zone_label' => $zone['label'] ?? $zoneKey,
            'zone_price' => $zone['price'] ?? 0,
        ]);
    }
}
