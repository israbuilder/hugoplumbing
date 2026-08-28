<?php

namespace App\Console\Commands;

use App\Models\LocalRankScan;
use Illuminate\Console\Command;

class LocalRankRematchCommand extends Command
{
    protected $signature = 'local-rank:rematch
        {scan : Local rank scan ID}';

    protected $description =
        'Re-match stored local rank results against the current business identifiers';

    public function handle(): int
    {
        $scan = LocalRankScan::with([
            'location',
            'results.point',
        ])->findOrFail(
            (int) $this->argument('scan')
        );

        $location = $scan->location;

        $normalize = function (?string $value): string {
            return mb_strtolower(
                trim(
                    preg_replace(
                        '/\s+/',
                        ' ',
                        $value ?? ''
                    )
                )
            );
        };

        $updated = 0;
        $found = 0;

        foreach ($scan->results as $result) {

            $items = collect(
                $result->items ?? []
            );

            $ourBusiness = $items->first(
                function ($item) use (
                    $location,
                    $normalize
                ) {
                    $itemPlaceId = trim(
                        (string) ($item['place_id'] ?? '')
                    );

                    $locationPlaceId = trim(
                        (string) ($location->google_place_id ?? '')
                    );

                    if (
                        $locationPlaceId !== '' &&
                        $itemPlaceId !== '' &&
                        $itemPlaceId === $locationPlaceId
                    ) {
                        return true;
                    }

                    $itemCid = trim(
                        (string) ($item['cid'] ?? '')
                    );

                    $locationCid = trim(
                        (string) ($location->google_cid ?? '')
                    );

                    if (
                        $locationCid !== '' &&
                        $itemCid !== '' &&
                        $itemCid === $locationCid
                    ) {
                        return true;
                    }

                    return $normalize(
                        $item['title'] ?? ''
                    ) === $normalize(
                        $location->name
                    );
                }
            );

            $rank = $ourBusiness
                ? (int) ($ourBusiness['rank_group'] ?? 0)
                : null;

            $result->update([
                'found' =>
                    $ourBusiness !== null,

                'rank' =>
                    $rank ?: null,

                'business_name' =>
                    $ourBusiness['title'] ?? null,

                'place_id' =>
                    $ourBusiness['place_id'] ?? null,

                'cid' =>
                    isset($ourBusiness['cid'])
                        ? (string) $ourBusiness['cid']
                        : null,

                'category' =>
                    $ourBusiness['category'] ?? null,

                'rating' =>
                    data_get(
                        $ourBusiness,
                        'rating.value'
                    ),

                'reviews_count' =>
                    data_get(
                        $ourBusiness,
                        'rating.votes_count'
                    ),

                'address' =>
                    $ourBusiness['address'] ?? null,
            ]);

            $updated++;

            if ($ourBusiness) {
                $found++;

                $this->line(
                    "Point {$result->local_rank_grid_point_id}: " .
                    "#{$rank} {$ourBusiness['title']}"
                );
            }
        }

        app(
            \App\Services\LocalRank\LocalRankMetricsService::class
        )->refreshScan($scan->id);

        $this->newLine();

        $this->info(
            "Rematch complete. Updated: {$updated}. Found: {$found}."
        );

        return self::SUCCESS;
    }
}