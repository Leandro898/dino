<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class PedidosYaPharmacyImportService
{
    private const EXTERNAL_SOURCE = 'pedidosya';
    private const EXTERNAL_CATEGORY = 'farmacia';
    private const RESTAURANT_UUID = '43e06b77-eb9f-4f3c-a8a2-67e920b07602';
    private const RESTAURANT_URL = 'https://www.pedidosya.com.ar/restaurantes/bariloche/pharmacy-pasaje-43e06b77-eb9f-4f3c-a8a2-67e920b07602-menu';

    /**
     * Import products from PedidosYa Pharmacy Pasaje into the local DB.
     *
     * @param  string  $cookie  Raw Cookie header value copied from browser DevTools.
     * @param  int|null  $userId
     * @param  bool  $deactivateMissing  Mark products no longer in the catalog as inactive.
     */
    public function import(string $cookie, ?int $userId = null, bool $deactivateMissing = false): array
    {
        $ownerId = $this->resolveOwnerId($userId);

        $sections = $this->fetchSections($cookie);

        return $this->importFromSections($sections, $ownerId, $deactivateMissing);
    }

    /**
     * Import products from an exported JSON payload (menu/sections response).
     */
    public function importFromJsonPayload(array $payload, ?int $userId = null, bool $deactivateMissing = false): array
    {
        $ownerId = $this->resolveOwnerId($userId);
        $sections = $this->extractSections($payload);

        return $this->importFromSections($sections, $ownerId, $deactivateMissing);
    }

    /**
     * Import products from a browser HAR export (Network -> Save all as HAR with content).
     */
    public function importFromHarPayload(array $harPayload, ?int $userId = null, bool $deactivateMissing = false): array
    {
        $ownerId = $this->resolveOwnerId($userId);
        $entries = data_get($harPayload, 'log.entries', []);

        if (!is_array($entries) || $entries === []) {
            throw new RuntimeException('HAR has no entries. Export using "Save all as HAR with content".');
        }

        $responses = [];

        foreach ($entries as $entry) {
            $url = (string) data_get($entry, 'request.url', '');
            $status = (int) data_get($entry, 'response.status', 0);
            $text = (string) data_get($entry, 'response.content.text', '');

            if ($status !== 200 || $text === '') {
                continue;
            }

            if (!str_contains($url, '/groceries/web/v1/vendors/') || !str_contains($url, '/products')) {
                continue;
            }

            $decoded = json_decode($text, true);
            if (is_array($decoded)) {
                $responses[] = $decoded;
            }
        }

        if ($responses === []) {
            throw new RuntimeException('No 200 product responses found in HAR for /groceries/web/v1/vendors/.../products.');
        }

        return $this->importFromGroceriesResponses($responses, $ownerId, $deactivateMissing);
    }

    /**
     * @param array<int, mixed> $sections
     */
    private function importFromSections(array $sections, int $ownerId, bool $deactivateMissing): array
    {

        if (empty($sections)) {
            throw new RuntimeException('No sections found in PedidosYa response. Check cookie or API response shape.');
        }

        $seenExternalIds = [];
        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($sections as $section) {
            $remoteProducts = data_get($section, 'products', []);

            foreach ($remoteProducts as $remoteProduct) {
                $mapped = $this->mapProduct($remoteProduct, $ownerId);

                if ($mapped === null) {
                    $skipped++;
                    continue;
                }

                $seenExternalIds[] = $mapped['external_id'];

                $product = Product::query()->firstOrNew([
                    'external_source' => self::EXTERNAL_SOURCE,
                    'external_id'     => $mapped['external_id'],
                ]);

                $wasExisting = $product->exists;
                $product->fill($mapped);
                $product->save();

                $wasExisting ? $updated++ : $created++;
            }
        }

        $deactivated = 0;

        if ($deactivateMissing && !empty($seenExternalIds)) {
            $deactivated = Product::query()
                ->where('external_source', self::EXTERNAL_SOURCE)
                ->where('external_category', self::EXTERNAL_CATEGORY)
                ->whereNotIn('external_id', $seenExternalIds)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        return compact('created', 'updated', 'skipped', 'deactivated');
    }

    /**
     * @param array<int, array<string, mixed>> $responses
     */
    private function importFromGroceriesResponses(array $responses, int $ownerId, bool $deactivateMissing): array
    {
        $seenExternalIds = [];
        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($responses as $response) {
            $remoteProducts = $this->extractGroceriesProducts($response);

            foreach ($remoteProducts as $remoteProduct) {
                $mapped = $this->mapProduct($remoteProduct, $ownerId);

                if ($mapped === null) {
                    $skipped++;
                    continue;
                }

                $seenExternalIds[] = $mapped['external_id'];

                $product = Product::query()->firstOrNew([
                    'external_source' => self::EXTERNAL_SOURCE,
                    'external_id'     => $mapped['external_id'],
                ]);

                $wasExisting = $product->exists;
                $product->fill($mapped);
                $product->save();

                $wasExisting ? $updated++ : $created++;
            }
        }

        $deactivated = 0;

        if ($deactivateMissing && !empty($seenExternalIds)) {
            $deactivated = Product::query()
                ->where('external_source', self::EXTERNAL_SOURCE)
                ->where('external_category', self::EXTERNAL_CATEGORY)
                ->whereNotIn('external_id', $seenExternalIds)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        return compact('created', 'updated', 'skipped', 'deactivated');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function extractGroceriesProducts(array $payload): array
    {
        $candidates = [
            data_get($payload, 'products'),
            data_get($payload, 'data.products'),
            data_get($payload, 'items'),
            data_get($payload, 'data.items'),
            data_get($payload, 'results'),
            data_get($payload, 'data.results'),
        ];

        foreach ($candidates as $candidate) {
            if (is_array($candidate) && $candidate !== []) {
                return array_values(array_filter($candidate, 'is_array'));
            }
        }

        return [];
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function fetchSections(string $cookie): array
    {
        // Try the sections endpoint first, fall back to full menu
        $endpoints = [
            'https://www.pedidosya.com.ar/api/v3/restaurants/' . self::RESTAURANT_UUID . '/menu/sections',
            'https://www.pedidosya.com.ar/api/v3/restaurants/' . self::RESTAURANT_UUID . '/menu',
            'https://www.pedidosya.com.ar/api/v3/restaurants/' . self::RESTAURANT_UUID . '/sections',
        ];

        $headers = [
            'User-Agent'       => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'Accept'           => 'application/json, text/plain, */*',
            'Accept-Language'  => 'es-AR,es;q=0.9,en;q=0.8',
            'Referer'          => self::RESTAURANT_URL,
            'X-Requested-With' => 'XMLHttpRequest',
            'Cookie'           => $cookie,
        ];

        foreach ($endpoints as $url) {
            $response = Http::withHeaders($headers)->timeout(30)->get($url);

            if (!$response->successful()) {
                continue;
            }

            $data = $response->json();
            $sections = $this->extractSections($data);

            if (!empty($sections)) {
                return $sections;
            }
        }

        throw new RuntimeException(
            "All PedidosYa endpoints failed or returned no sections.\n" .
                "Make sure the --cookie value is a fresh session cookie from your browser."
        );
    }

    /**
     * Try every known response shape PedidosYa has used.
     */
    private function extractSections(array $data): array
    {
        $candidates = [
            data_get($data, 'sections'),
            data_get($data, 'data.menu.sections'),
            data_get($data, 'data.sections'),
            data_get($data, 'menu.sections'),
            // Sometimes the root IS the sections array
            is_array($data) && isset($data[0]['products']) ? $data : null,
        ];

        foreach ($candidates as $candidate) {
            if (is_array($candidate) && !empty($candidate)) {
                return $candidate;
            }
        }

        return [];
    }

    private function mapProduct(array $remote, int $ownerId): ?array
    {
        $id    = (string) (data_get($remote, 'id') ?? data_get($remote, 'integrationCode') ?? '');
        $name  = trim((string) (data_get($remote, 'name') ?? ''));
        $name = mb_substr($name, 0, 240);
        $priceRaw = data_get($remote, 'price');
        if (is_array($priceRaw)) {
            $priceRaw = data_get($remote, 'price.amount') ?? data_get($remote, 'price.value');
        }
        if ($priceRaw === null) {
            $priceRaw = data_get($remote, 'pricing.price')
                ?? data_get($remote, 'pricing.beforePrice')
                ?? data_get($remote, 'pricing.pricePerMeasurementUnit');
        }
        $price = (float) ($priceRaw ?? data_get($remote, 'unitPrice') ?? 0);

        if ($id === '' || $name === '' || $price <= 0) {
            return null;
        }

        // Image can be object {url} or plain string URL
        $imageRaw = data_get($remote, 'absoluteImages.0')
            ?? data_get($remote, 'image')
            ?? data_get($remote, 'imageUrl')
            ?? data_get($remote, 'images.0')
            ?? '';
        $image = is_array($imageRaw)
            ? (string) (data_get($imageRaw, 'url') ?? data_get($imageRaw, 'smallUrl') ?? '')
            : (string) $imageRaw;

        $baseSlug = Str::slug($name);
        $baseSlug = mb_substr($baseSlug, 0, 220);
        $slug = $baseSlug . '-py-' . substr($id, 0, 8);

        // Ensure slug uniqueness across existing records
        $existingBySlug = Product::query()
            ->where('slug', $slug)
            ->where('external_id', '!=', $id)
            ->exists();

        if ($existingBySlug) {
            $slug .= '-' . substr(md5($id), 0, 4);
        }

        return [
            'name'              => $name,
            'slug'              => $slug,
            'price'             => $price,
            'image'             => $image,
            'is_active'         => true,
            'user_id'           => $ownerId,
            'external_source'   => self::EXTERNAL_SOURCE,
            'external_id'       => $id,
            'external_category' => self::EXTERNAL_CATEGORY,
            'external_url'      => self::RESTAURANT_URL,
        ];
    }

    private function resolveOwnerId(?int $userId): int
    {
        if ($userId !== null) {
            return $userId;
        }

        $user = User::query()->orderBy('id')->first();

        if (!$user) {
            throw new RuntimeException('No users found in database. Create at least one user first.');
        }

        return $user->id;
    }
}
