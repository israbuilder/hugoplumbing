<?php

namespace App\Integrations\Google\BusinessProfile;

use App\Models\BusinessProfileLocation;
use App\Models\SyncRun;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class BusinessProfileSyncService
{
    public function __construct(
        protected BusinessProfilePerformanceClient $client
    ) {
    }

    public function sync(
        BusinessProfileLocation $location,
        Carbon|string $from,
        Carbon|string $to
    ): SyncRun {
        $from =
            Carbon::parse($from);

        $to =
            Carbon::parse($to);

        $integrationAccount =
            $location
                ->businessProfileAccount
                ->integrationAccount;

        $run =
            SyncRun::create([
                'integration_account_id' =>
                    $integrationAccount->id,

                'type' =>
                    'google_business_profile',

                'status' =>
                    SyncRun::STATUS_PENDING,

                'date_from' =>
                    $from,

                'date_to' =>
                    $to,
            ]);

        $run->markRunning();

        try {
            $count = 0;

            $count +=
                $this->syncDailyMetrics(
                    $location,
                    $from,
                    $to
                );

            $count +=
                $this->syncKeywords(
                    $location,
                    $from,
                    $to
                );

            $location->update([
                'last_synced_at' =>
                    now(),
            ]);

            $run->update([
                'status' =>
                    SyncRun::STATUS_SUCCESS,

                'rows_processed' =>
                    $count,

                'finished_at' =>
                    now(),
            ]);

        } catch (Throwable $e) {
            $run->markFailed(
                $e
            );

            throw $e;
        }

        return $run->fresh();
    }

    protected function syncDailyMetrics(
        BusinessProfileLocation $location,
        Carbon $from,
        Carbon $to
    ): int {
        $response =
            $this->client
                ->dailyMetrics(
                    $location,
                    $from,
                    $to
                );

        $records = [];

        foreach (
            $response[
                'multiDailyMetricTimeSeries'
            ]
            ?? []
            as $group
        ) {
            foreach (
                $group[
                    'dailyMetricTimeSeries'
                ]
                ?? []
                as $series
            ) {
                $metric =
                    $series[
                        'dailyMetric'
                    ];

                $subEntity =
                    $series[
                        'dailySubEntityType'
                    ]
                    ?? null;

                foreach (
                    $series[
                        'timeSeries'
                    ][
                        'datedValues'
                    ]
                    ?? []
                    as $value
                ) {
                    $dateData =
                        $value[
                            'date'
                        ];

                    $date =
                        sprintf(
                            '%04d-%02d-%02d',
                            $dateData['year'],
                            $dateData['month'],
                            $dateData['day']
                        );

                    $hash =
                        hash(
                            'sha256',
                            json_encode(
                                $subEntity
                                ?? []
                            )
                        );

                    $records[] = [
                        'business_profile_location_id' =>
                            $location->id,

                        'date' =>
                            $date,

                        'metric' =>
                            $metric,

                        'value' =>
                            (int) (
                                $value[
                                    'value'
                                ]
                                ?? 0
                            ),

                        'sub_entity' =>
                            $subEntity
                                ? json_encode(
                                    $subEntity
                                )
                                : null,

                        'dimension_hash' =>
                            $hash,

                        'synced_at' =>
                            now(),

                        'created_at' =>
                            now(),

                        'updated_at' =>
                            now(),
                    ];
                }
            }
        }

        foreach (
            array_chunk(
                $records,
                1000
            )
            as $chunk
        ) {
            DB::table(
                'business_profile_daily_metrics'
            )->upsert(
                $chunk,
                [
                    'business_profile_location_id',
                    'date',
                    'metric',
                    'dimension_hash',
                ],
                [
                    'value',
                    'sub_entity',
                    'synced_at',
                    'updated_at',
                ]
            );
        }

        return count(
            $records
        );
    }

    protected function syncKeywords(
        BusinessProfileLocation $location,
        Carbon $from,
        Carbon $to
    ): int {
        $rows =
            $this->client
                ->searchKeywords(
                    $location,
                    $from,
                    $to
                );

        /*
         * Search keywords endpoint returns the
         * aggregate over the requested month range.
         *
         * To maintain true monthly records,
         * our normal sync will call this with
         * one month per request.
         */
        $month =
            $from
                ->copy()
                ->startOfMonth()
                ->toDateString();

        $records = [];

        foreach (
            $rows as $row
        ) {
            $keyword =
                $row[
                    'searchKeyword'
                ];

            $insights =
                $row[
                    'insightsValue'
                ]
                ?? [];

            $records[] = [
                'business_profile_location_id' =>
                    $location->id,

                'month' =>
                    $month,

                'keyword' =>
                    $keyword,

                'impressions' =>
                    isset(
                        $insights[
                            'value'
                        ]
                    )
                        ? (int)
                            $insights[
                                'value'
                            ]
                        : null,

                'threshold' =>
                    isset(
                        $insights[
                            'threshold'
                        ]
                    )
                        ? (int)
                            $insights[
                                'threshold'
                            ]
                        : null,

                'keyword_hash' =>
                    hash(
                        'sha256',
                        mb_strtolower(
                            $keyword
                        )
                    ),

                'synced_at' =>
                    now(),

                'created_at' =>
                    now(),

                'updated_at' =>
                    now(),
            ];
        }

        if (!empty($records)) {
            DB::table(
                'business_profile_search_keywords'
            )->upsert(
                $records,
                [
                    'business_profile_location_id',
                    'month',
                    'keyword_hash',
                ],
                [
                    'keyword',
                    'impressions',
                    'threshold',
                    'synced_at',
                    'updated_at',
                ]
            );
        }

        return count(
            $records
        );
    }
}