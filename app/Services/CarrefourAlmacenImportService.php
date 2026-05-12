<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CarrefourAlmacenImportService
{
    private const CATEGORY_SLUG = 'almacen';
    private const EXTERNAL_SOURCE = 'carrefour';

    private const FACETS_QUERY = <<<'GRAPHQL'
query($query:String,$selectedFacets:[SelectedFacetInput]){
    facets(query:$query, selectedFacets:$selectedFacets)
        @context(provider: "vtex.search-graphql") {
        facets {
            name
            values {
                name
                quantity
                key
                value
            }
        }
    }
}
GRAPHQL;

    private const GRAPHQL_QUERY = <<<'GRAPHQL'
query($query:String,$selectedFacets:[SelectedFacetInput],$from:Int,$to:Int){
  productSearch(query:$query, selectedFacets:$selectedFacets, from:$from, to:$to)
    @context(provider: "vtex.search-graphql") {
    products {
      productId
      productName
      link
      metaTagDescription
      items {
        images {
          imageUrl
        }
        sellers {
          sellerDefault
          commertialOffer {
            Price
            ListPrice
            AvailableQuantity
          }
        }
      }
    }
    recordsFiltered
  }
}
GRAPHQL;

    public function import(
        string $departmentSlug = self::CATEGORY_SLUG,
        ?int $userId = null,
        int $chunkSize = 50,
        ?int $maxPages = null,
        bool $deactivateMissing = false
    ): array {
        $departmentSlug = trim($departmentSlug) !== '' ? trim($departmentSlug) : self::CATEGORY_SLUG;
        $ownerId = $this->resolveOwnerId($userId);
        $page = 0;
        $total = 0;
        $seenExternalIds = [];
        $created = 0;
        $updated = 0;
        $facets = $this->fetchCategoryFacets($departmentSlug);
        $currentDepartmentSlug = $departmentSlug;

        foreach ($facets as $facet) {
            $offset = 0;
            $facetTotal = (int) Arr::get($facet, 'quantity', 0);
            $selectedFacets = [
                [
                    'key' => 'c',
                    'value' => $departmentSlug,
                ],
                [
                    'key' => (string) Arr::get($facet, 'key'),
                    'value' => (string) Arr::get($facet, 'value'),
                ],
            ];

            do {
                $page++;

                $payload = $this->fetchBatch($selectedFacets, $offset, $chunkSize);
                $products = Arr::get($payload, 'data.productSearch.products', []);

                if ($products === []) {
                    break;
                }

                DB::transaction(function () use ($products, $ownerId, $currentDepartmentSlug, &$seenExternalIds, &$created, &$updated) {
                    foreach ($products as $remoteProduct) {
                        $mapped = $this->mapProduct($remoteProduct, $ownerId, $currentDepartmentSlug);
                        if ($mapped === null) {
                            continue;
                        }

                        $seenExternalIds[] = $mapped['external_id'];

                        $product = Product::query()->firstOrNew([
                            'external_source' => self::EXTERNAL_SOURCE,
                            'external_id' => $mapped['external_id'],
                        ]);

                        $wasExisting = $product->exists;
                        $product->fill($mapped);
                        $product->save();

                        if ($wasExisting) {
                            $updated++;
                        } else {
                            $created++;
                        }
                    }
                });

                $offset += $chunkSize;
            } while (($maxPages === null || $page < $maxPages) && $offset < $facetTotal);

            $total += $facetTotal;

            if ($maxPages !== null && $page >= $maxPages) {
                break;
            }
        }

        $deactivated = 0;

        if ($deactivateMissing && $seenExternalIds !== []) {
            $deactivated = Product::query()
                ->where('external_source', self::EXTERNAL_SOURCE)
                ->where('external_category', $departmentSlug)
                ->whereNotIn('external_id', array_values(array_unique($seenExternalIds)))
                ->update([
                    'is_active' => false,
                    'stock' => 0,
                ]);
        }

        return [
            'owner_id' => $ownerId,
            'records_filtered' => $total ?? 0,
            'pages_processed' => $page,
            'created' => $created,
            'updated' => $updated,
            'deactivated' => $deactivated,
        ];
    }

    private function fetchBatch(array $selectedFacets, int $offset, int $chunkSize): array
    {
        $response = Http::baseUrl('https://www.carrefour.com.ar')
            ->acceptJson()
            ->retry(3, 500)
            ->timeout(60)
            ->post('/_v/private/graphql/v1', [
                'query' => self::GRAPHQL_QUERY,
                'variables' => [
                    'query' => '',
                    'selectedFacets' => $selectedFacets,
                    'from' => $offset,
                    'to' => $offset + $chunkSize - 1,
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('No se pudo consultar Carrefour: HTTP ' . $response->status());
        }

        $json = $response->json();

        if (!empty($json['errors'])) {
            $message = Arr::get($json, 'errors.0.message', 'Carrefour devolvio un error GraphQL durante la importacion.');
            throw new RuntimeException($message);
        }

        return $json;
    }

    private function fetchCategoryFacets(string $departmentSlug): array
    {
        $response = Http::baseUrl('https://www.carrefour.com.ar')
            ->acceptJson()
            ->retry(3, 500)
            ->timeout(60)
            ->post('/_v/private/graphql/v1', [
                'query' => self::FACETS_QUERY,
                'variables' => [
                    'query' => $departmentSlug,
                    'selectedFacets' => [
                        [
                            'key' => 'c',
                            'value' => $departmentSlug,
                        ],
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('No se pudieron obtener las categorias internas de Carrefour.');
        }

        $json = $response->json();
        $facets = Arr::get($json, 'data.facets.facets', []);
        $categoryFacet = collect($facets)->first(fn(array $facet) => Arr::get($facet, 'name') === 'Categoría');
        $values = Arr::get($categoryFacet, 'values', []);

        if ($values === []) {
            throw new RuntimeException('Carrefour no devolvio subcategorias utilizables para la importacion.');
        }

        return array_values($values);
    }

    private function mapProduct(array $remoteProduct, int $ownerId, string $departmentSlug): ?array
    {
        $externalId = (string) Arr::get($remoteProduct, 'productId', '');
        $name = trim((string) Arr::get($remoteProduct, 'productName', ''));
        $item = Arr::first(Arr::get($remoteProduct, 'items', []));
        $seller = $this->resolveSeller((array) $item);
        $price = Arr::get($seller, 'commertialOffer.Price');
        $availableQuantity = (int) Arr::get($seller, 'commertialOffer.AvailableQuantity', 0);

        if ($externalId === '' || $name === '' || !is_numeric($price)) {
            return null;
        }

        $description = trim(strip_tags((string) Arr::get($remoteProduct, 'metaTagDescription', '')));
        $relativeUrl = (string) Arr::get($remoteProduct, 'link', '');

        return [
            'user_id' => $ownerId,
            'name' => $name,
            'description' => $description !== '' ? $description : 'Producto importado automaticamente desde Carrefour.',
            'price' => (float) $price,
            'image' => Arr::get($item, 'images.0.imageUrl'),
            'stock' => $availableQuantity > 0 ? min($availableQuantity, 9999) : 0,
            'is_active' => $availableQuantity > 0,
            'is_raffle' => false,
            'external_source' => self::EXTERNAL_SOURCE,
            'external_id' => $externalId,
            'external_category' => $departmentSlug,
            'external_url' => $relativeUrl !== '' ? 'https://www.carrefour.com.ar' . $relativeUrl : null,
        ];
    }

    private function resolveSeller(array $item): array
    {
        $sellers = Arr::get($item, 'sellers', []);

        foreach ($sellers as $seller) {
            if ((bool) Arr::get($seller, 'sellerDefault', false)) {
                return $seller;
            }
        }

        return Arr::first($sellers) ?? [];
    }

    private function resolveOwnerId(?int $userId): int
    {
        if ($userId !== null) {
            $user = User::query()->find($userId);

            if (!$user) {
                throw new RuntimeException('No existe el usuario indicado para asignar los productos importados.');
            }

            return $user->id;
        }

        $user = User::query()->orderBy('id')->first();

        if (!$user) {
            throw new RuntimeException('No hay usuarios en la base de datos. Crea uno o ejecuta el comando con --user=ID.');
        }

        return $user->id;
    }
}
