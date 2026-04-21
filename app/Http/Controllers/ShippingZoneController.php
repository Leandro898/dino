<?php

namespace App\Http\Controllers;

use App\Models\ShippingZone;
use App\Models\StreetZone;
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
        $query = trim($request->query('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json(['suggestions' => []]);
        }

        $normalized = $this->detector->normalize($query);

        if ($normalized === '') {
            return response()->json(['suggestions' => []]);
        }

        $streets = StreetZone::query()
            ->select('street_name')
            ->where('street_name', 'like', $normalized . '%')
            ->distinct()
            ->orderBy('street_name')
            ->limit(12)
            ->pluck('street_name')
            ->map(fn ($name) => mb_convert_case($name, MB_CASE_TITLE, 'UTF-8'))
            ->values();

        return response()->json([
            'suggestions' => $streets,
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

        $zones = ShippingZone::query()
            ->where('is_active', true)
            ->get(['zone_key', 'label', 'price'])
            ->mapWithKeys(fn ($zone) => [
                $zone->zone_key => [
                    'label' => $zone->label,
                    'price' => (int) $zone->price,
                ],
            ])
            ->toArray();

        if (empty($zones)) {
            $zones = config('shipping.zones', []);
        }

        $zone  = $zones[$zoneKey] ?? null;

        return response()->json([
            'zone_key'   => $zoneKey,
            'zone_label' => $zone['label'] ?? $zoneKey,
            'zone_price' => $zone['price'] ?? 0,
        ]);
    }
}
