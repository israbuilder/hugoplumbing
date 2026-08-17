<?php

namespace App\Integrations\Google\SearchConsole;

use App\Models\SearchConsoleMetric;
use App\Models\SearchConsoleSite;
use App\Models\SyncRun;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class SearchConsoleSyncService
{
    private const ROW_LIMIT = 25000;

    public function __construct(
        protected SearchConsoleClient $client
    ) {
    }

    public function sync(
        SearchConsoleSite $site,
        Carbon|string $from,
        Carbon|string $to
    ): SyncRun {
        $from = Carbon::parse($from)->startOfDay();
        $to = Carbon::parse($to)->startOfDay();

        $run = SyncRun::create([
            'integration_account_id' =>
                $site->integration_account_id,

            'type' =>
                'search_console_performance',

            'status' =>
                SyncRun::STATUS_PENDING,

            'date_from' => $from,

            'date_to' => $to,
        ]);

        $run->markRunning();

        try {

            $rowsProcessed = 0;

            /*
             * Site totals
             */
            $rowsProcessed +=
                $this->syncGrain(
                    $site,
                    $from,
                    $to,
                    SearchConsoleMetric::GRAIN_SITE,
                    ['date']
                );

            /*
             * Keywords
             */
            $rowsProcessed +=
                $this->syncGrain(
                    $site,
                    $from,
                    $to,
                    SearchConsoleMetric::GRAIN_QUERY,
                    [
                        'date',
                        'query',
                    ]
                );

            /*
             * Landing pages
             */
            $rowsProcessed +=
                $this->syncGrain(
                    $site,
                    $from,
                    $to,
                    SearchConsoleMetric::GRAIN_PAGE,
                    [
                        'date',
                        'page',
                    ]
                );

            /*
             * Keyword -> landing page
             */
            $rowsProcessed +=
                $this->syncGrain(
                    $site,
                    $from,
                    $to,
                    SearchConsoleMetric::GRAIN_QUERY_PAGE,
                    [
                        'date',
                        'query',
                        'page',
                    ]
                );

            /*
             * Countries
             */
            $rowsProcessed +=
                $this->syncGrain(
                    $site,
                    $from,
                    $to,
                    SearchConsoleMetric::GRAIN_COUNTRY,
                    [
                        'date',
                        'country',
                    ]
                );

            /*
             * Devices
             */
            $rowsProcessed +=
                $this->syncGrain(
                    $site,
                    $from,
                    $to,
                    SearchConsoleMetric::GRAIN_DEVICE,
                    [
                        'date',
                        'device',
                    ]
                );

            $site->update([
                'last_synced_at' => now(),
            ]);

            $site->account->update([
                'last_synced_at' => now(),
            ]);

            $run->update([
                'rows_processed' =>
                    $rowsProcessed,

                'status' =>
                    SyncRun::STATUS_SUCCESS,

                'finished_at' => now(),
            ]);

        } catch (Throwable $e) {

            $run->markFailed($e);

            throw $e;
        }

        return $run->fresh();
    }

    protected function syncGrain(
        SearchConsoleSite $site,
        Carbon $from,
        Carbon $to,
        string $grain,
        array $dimensions
    ): int {
        $startRow = 0;
        $total = 0;

        do {
            $response = $this->client->query(
                $site->account,
                $site->site_url,
                [
                    'startDate' =>
                        $from->toDateString(),

                    'endDate' =>
                        $to->toDateString(),

                    'dimensions' =>
                        $dimensions,

                    'type' => 'web',

                    /*
                     * final means avoid incomplete
                     * fresh data during our normal sync.
                     */
                    'dataState' => 'final',

                    'rowLimit' =>
                        self::ROW_LIMIT,

                    'startRow' =>
                        $startRow,
                ]
            );

            $rows = $response['rows'] ?? [];

            if (empty($rows)) {
                break;
            }

            $records = [];

            foreach ($rows as $row) {

                $record =
                    $this->mapRow(
                        $site,
                        $grain,
                        $dimensions,
                        $row
                    );

                $records[] = $record;
            }

            /*
             * Insert/update in chunks.
             */
            foreach (
                array_chunk($records, 1000)
                as $chunk
            ) {
                DB::table(
                    'search_console_metrics'
                )->upsert(
                    $chunk,
                    [
                        'search_console_site_id',
                        'date',
                        'grain',
                        'search_type',
                        'dimension_hash',
                    ],
                    [
                        'data_state',
                        'query',
                        'page',
                        'country',
                        'device',
                        'search_appearance',
                        'clicks',
                        'impressions',
                        'ctr',
                        'position',
                        'synced_at',
                        'updated_at',
                    ]
                );
            }

            $count = count($rows);

            $total += $count;
            $startRow += $count;

        } while (
            count($rows) === self::ROW_LIMIT
        );

        return $total;
    }

    protected function mapRow(
        SearchConsoleSite $site,
        string $grain,
        array $dimensions,
        array $row
    ): array {
        $keys = $row['keys'] ?? [];

        $values = [];

        foreach (
            $dimensions as $index => $dimension
        ) {
            $values[$dimension] =
                $keys[$index] ?? null;
        }

        $date =
            $values['date']
            ?? now()->toDateString();

        $query =
            $values['query']
            ?? null;

        $page =
            $values['page']
            ?? null;

        $country =
            $values['country']
            ?? null;

        $device =
            $values['device']
            ?? null;

        $searchAppearance =
            $values['searchAppearance']
            ?? null;

        $hash =
            SearchConsoleMetric::makeDimensionHash(
                siteId: $site->id,
                date: $date,
                grain: $grain,
                searchType: 'web',
                query: $query,
                page: $page,
                country: $country,
                device: $device,
                searchAppearance:
                    $searchAppearance,
            );

        $now = now();

        return [
            'search_console_site_id' =>
                $site->id,

            'date' =>
                $date,

            'grain' =>
                $grain,

            'search_type' =>
                'web',

            'data_state' =>
                'final',

            'query' =>
                $query,

            'page' =>
                $page,

            'country' =>
                $country,

            'device' =>
                $device,

            'search_appearance' =>
                $searchAppearance,

            'clicks' =>
                (int) ($row['clicks'] ?? 0),

            'impressions' =>
                (int) ($row['impressions'] ?? 0),

            'ctr' =>
                (float) ($row['ctr'] ?? 0),

            'position' =>
                (float) ($row['position'] ?? 0),

            'dimension_hash' =>
                $hash,

            'synced_at' =>
                $now,

            'created_at' =>
                $now,

            'updated_at' =>
                $now,
        ];
    }
}