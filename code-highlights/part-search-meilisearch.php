<?php

declare(strict_types=1);

/**
 * KOD ÖRNEĞİ — Meilisearch ile parça araması (highlight'lı autocomplete + filtreler).
 *
 * Parça araması MySQL LIKE yerine Meilisearch'tedir: OEM kodu, ad, marka üzerinde
 * typo-toleranslı, <50ms hedefli, highlight'lı. Eloquent yalnızca dönen id'leri hidrate
 * eder (brand/category eager-load ile N+1 önlenir). Arama hatası kullanıcıyı düşürmez —
 * boş sonuç döner ve loglanır.
 */

namespace App\Modules\Search\Services;

use App\Models\Part;
use Illuminate\Support\Facades\Log;
use Meilisearch\Endpoints\Indexes;
use Throwable;

final class PartSearchService
{
    /**
     * Autocomplete — code / oem_code / name / brand_name üzerinde, highlight tag'leriyle.
     *
     * @return list<array<string, mixed>>
     */
    public function autocomplete(string $term, int $limit = 8): array
    {
        $term = trim($term);
        if (mb_strlen($term) < 2) {
            return [];
        }
        $limit = max(1, min($limit, 15));

        try {
            $builder = Part::search($term, function (Indexes $index, string $searchTerm, array $options) use ($limit) {
                $options['filter'] = ['is_active = true'];
                $options['limit'] = $limit;
                $options['attributesToRetrieve']  = ['id', 'code', 'oem_code', 'name', 'brand_name'];
                $options['attributesToHighlight'] = ['code', 'oem_code', 'name', 'brand_name'];
                $options['attributesToSearchOn']  = ['code', 'oem_code', 'name', 'brand_name'];
                // Frontend CSS `.search-highlight em` ile eşleşir
                $options['highlightPreTag'] = '<em>';
                $options['highlightPostTag'] = '</em>';

                return $index->rawSearch($searchTerm, $options);
            });

            $raw = $builder->raw();
        } catch (Throwable $e) {
            Log::error('Meilisearch autocomplete hatasi', ['term' => $term, 'error' => $e->getMessage()]);

            return []; // arama servisi çökse bile UI çalışmaya devam eder
        }

        return array_map(static function (array $hit): array {
            // Highlight önceliği: name → code → oem_code → düz name
            $highlight = $hit['_formatted']['name']
                ?? $hit['_formatted']['code']
                ?? $hit['_formatted']['oem_code']
                ?? ($hit['name'] ?? '');

            return [
                'id'         => $hit['id'] ?? null,
                'code'       => $hit['code'] ?? null,
                'oem_code'   => $hit['oem_code'] ?? null,
                'name'       => $hit['name'] ?? null,
                'brand_name' => $hit['brand_name'] ?? null,
                'highlight'  => $highlight,
            ];
        }, $raw['hits'] ?? []);
    }

    /**
     * Tam arama için Meilisearch filter ifadeleri. is_active her zaman uygulanır.
     *
     * @return list<string>
     */
    private function buildFilters(int $brandId = 0, int $categoryId = 0, int $engineId = 0, bool $originalOnly = false): array
    {
        $filters = ['is_active = true'];
        if ($brandId)    $filters[] = 'brand_id = '.$brandId;
        if ($categoryId) $filters[] = 'category_id = '.$categoryId;
        if ($engineId)   $filters[] = 'vehicle_engine_ids = '.$engineId; // araç bazlı uyumluluk
        if ($originalOnly) $filters[] = 'is_original = true';

        return $filters;
    }
}
