<?php

namespace App\Jobs\LocalRank;

use App\Models\LocalRankGridPoint;
use App\Models\LocalRankResult;
use App\Services\LocalRank\LocalRankMetricsService;
use App\Services\LocalRank\Providers\DataForSeoMapsClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Throwable;

class FetchLocalRankPointResult implements ShouldQueue
{
    use Queueable;
    use InteractsWithQueue;

    public int $tries = 15;

    public int $timeout = 90;

    public function __construct(
        public int $pointId
    ) {
    }

    public function handle(
        DataForSeoMapsClient $client,
        LocalRankMetricsService $metrics
    ): void {
        $point = LocalRankGridPoint::with([
            'scan.location',
            'scan.keyword',
        ])->findOrFail($this->pointId);

        if ($point->status === 'completed') {
            return;
        }

        if (!$point->provider_task_id) {
            throw new \RuntimeException(
                'Grid point does not have provider_task_id.'
            );
        }

        $point->increment('attempts');

        $response = $client->getTask(
            $point->provider_task_id
        );

        /*
|--------------------------------------------------------------------------
| No SERP results
|--------------------------------------------------------------------------
|
| DataForSEO 40102 means Google returned no Maps results for this
| coordinate/keyword. This is a valid GeoGrid outcome, not a failed job.
|
*/

if ($response['_local_rank_no_results'] ?? false) {

    LocalRankResult::updateOrCreate(
        [
            'local_rank_grid_point_id' => $point->id,
        ],
        [
            'local_rank_scan_id' =>
                $point->local_rank_scan_id,

            'found' => false,

            'rank' => null,

            'business_name' => null,

            'place_id' => null,

            'cid' => null,

            'category' => null,

            'rating' => null,

            'reviews_count' => null,

            'address' => null,

            'items' => [],

            'raw_response' => $response,
        ]
    );

    $point->update([
        'status' => 'completed',

        'error_message' =>
            'No Google Maps results returned for this point.',
    ]);

    $metrics->refreshScan(
        $point->local_rank_scan_id
    );

    return;
}

        $task = $response['tasks'][0] ?? null;

        if (!$task) {
            $this->release(20);

            return;
        }

        /*
         * DataForSEO may return task info before SERP
         * processing has completely finished.
         */
        $results = $task['result'] ?? null;

        if (empty($results)) {
            $this->release(20);

            return;
        }

        $resultData = $results[0] ?? [];

        $items = collect(
            $resultData['items'] ?? []
        )
            ->filter(
                fn ($item) =>
                    ($item['type'] ?? null) === 'maps_search'
            )
            ->values();

        $location = $point->scan->location;

        /*
         * place_id is our strongest matching identifier.
         */
        $ourBusiness = null;

        if ($location->google_place_id) {
            $ourBusiness = $items->first(
                fn ($item) =>
                    ($item['place_id'] ?? null)
                    === $location->google_place_id
            );
        }

        /*
         * Optional CID fallback.
         */
        if (!$ourBusiness && $location->google_cid) {
            $ourBusiness = $items->first(
                fn ($item) =>
                    (string) ($item['cid'] ?? '')
                    === (string) $location->google_cid
            );
        }

        /*
         * Last-resort name comparison.
         * place_id is still strongly recommended.
         */
        if (!$ourBusiness) {
            $ourBusiness = $items->first(
                fn ($item) =>
                    mb_strtolower(trim($item['title'] ?? ''))
                    === mb_strtolower(trim($location->name))
            );
        }

        $rank = $ourBusiness
            ? (int) ($ourBusiness['rank_group'] ?? 0)
            : null;

        LocalRankResult::updateOrCreate(
            [
                'local_rank_grid_point_id' => $point->id,
            ],
            [
                'local_rank_scan_id' => $point->local_rank_scan_id,

                'found' => $ourBusiness !== null,

                'rank' => $rank ?: null,

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

                /*
                 * Save Maps listings for competitive analysis.
                 */
                'items' => $items->all(),

                'raw_response' => $response,
            ]
        );

        $point->update([
            'status' => 'completed',
            'error_message' => null,
        ]);

        $metrics->refreshScan(
            $point->local_rank_scan_id
        );
    }

    public function failed(Throwable $exception): void
    {
        $point = LocalRankGridPoint::find(
            $this->pointId
        );

        if (!$point) {
            return;
        }

        $point->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
        ]);

        app(LocalRankMetricsService::class)
            ->refreshScan(
                $point->local_rank_scan_id
            );
    }
}