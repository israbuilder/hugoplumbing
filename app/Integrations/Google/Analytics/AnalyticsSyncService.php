<?php

namespace App\Integrations\Google\Analytics;

use App\Models\AnalyticsDailyMetric;
use App\Models\AnalyticsEventMetric;
use App\Models\AnalyticsPageMetric;
use App\Models\AnalyticsProperty;
use App\Models\AnalyticsTrafficMetric;
use App\Models\SyncRun;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class AnalyticsSyncService
{
    public function __construct(
        protected AnalyticsDataClient $client
    ) {
    }

    public function sync(
        AnalyticsProperty $property,
        Carbon|string $from,
        Carbon|string $to
    ): SyncRun {

        $from =
            Carbon::parse(
                $from
            )->startOfDay();

        $to =
            Carbon::parse(
                $to
            )->startOfDay();

        $run =
            SyncRun::create([
                'integration_account_id' =>
                    $property
                        ->integration_account_id,

                'type' =>
                    'google_analytics',

                'status' =>
                    SyncRun::STATUS_PENDING,

                'date_from' =>
                    $from,

                'date_to' =>
                    $to,
            ]);

        $run->markRunning();

        try {

            $rows = 0;

            $rows +=
                $this->syncDaily(
                    $property,
                    $from,
                    $to
                );

            $rows +=
                $this->syncPages(
                    $property,
                    $from,
                    $to
                );

            $rows +=
                $this->syncLandingPages(
                    $property,
                    $from,
                    $to
                );

            $rows +=
                $this->syncTraffic(
                    $property,
                    $from,
                    $to
                );

            $rows +=
                $this->syncEvents(
                    $property,
                    $from,
                    $to
                );

            $property->update([
                'last_synced_at' =>
                    now(),
            ]);

            $property
                ->account
                ->update([
                    'last_synced_at'
                        => now(),
                ]);

            $run->update([
                'rows_processed' =>
                    $rows,

                'status' =>
                    SyncRun::STATUS_SUCCESS,

                'finished_at' =>
                    now(),
            ]);

        } catch (
            Throwable $e
        ) {

            $run->markFailed(
                $e
            );

            throw $e;
        }

        return $run
            ->fresh();
    }

    protected function syncDaily(
        AnalyticsProperty $property,
        Carbon $from,
        Carbon $to
    ): int {

        $rows =
            $this->client
                ->runPaginatedReport(
                    $property,
                    [
                        'dateRanges' => [
                            [
                                'startDate' =>
                                    $from
                                        ->toDateString(),

                                'endDate' =>
                                    $to
                                        ->toDateString(),
                            ],
                        ],

                        'dimensions' => [
                            [
                                'name' =>
                                    'date',
                            ],
                        ],

                        'metrics' => [
                            [
                                'name' =>
                                    'activeUsers',
                            ],

                            [
                                'name' =>
                                    'totalUsers',
                            ],

                            [
                                'name' =>
                                    'newUsers',
                            ],

                            [
                                'name' =>
                                    'sessions',
                            ],

                            [
                                'name' =>
                                    'engagedSessions',
                            ],

                            [
                                'name' =>
                                    'engagementRate',
                            ],

                            [
                                'name' =>
                                    'averageSessionDuration',
                            ],

                            [
                                'name' =>
                                    'screenPageViews',
                            ],

                            [
                                'name' =>
                                    'eventCount',
                            ],

                            [
                                'name' =>
                                    'keyEvents',
                            ],

                        ],
                    ]
                );

        $records = [];

        foreach ($rows as $row) {

            $dimensions =
                $this->dimensions(
                    $row
                );

            $metrics =
                $this->metrics(
                    $row
                );

            $date =
                $this->gaDate(
                    $dimensions[0]
                    ?? null
                );

            if (!$date) {
                continue;
            }

            $records[] = [
                'analytics_property_id' =>
                    $property->id,

                'date' =>
                    $date,

                'active_users' =>
                    $this->integer(
                        $metrics[0]
                        ?? null
                    ),

                'total_users' =>
                    $this->integer(
                        $metrics[1]
                        ?? null
                    ),

                'new_users' =>
                    $this->integer(
                        $metrics[2]
                        ?? null
                    ),

                'sessions' =>
                    $this->integer(
                        $metrics[3]
                        ?? null
                    ),

                'engaged_sessions' =>
                    $this->integer(
                        $metrics[4]
                        ?? null
                    ),

                'engagement_rate' =>
                    $this->decimal(
                        $metrics[5]
                        ?? null
                    ),

                'average_session_duration' =>
                    $this->decimal(
                        $metrics[6]
                        ?? null
                    ),

                'screen_page_views' =>
                    $this->integer(
                        $metrics[7]
                        ?? null
                    ),

                'event_count' =>
                    $this->integer(
                        $metrics[8]
                        ?? null
                    ),

                'key_events' =>
                    $this->decimal(
                        $metrics[9]
                        ?? null
                    ),

                'total_revenue' => 0,

                'synced_at' =>
                    now(),

                'created_at' =>
                    now(),

                'updated_at' =>
                    now(),
            ];
        }

        $this->upsertChunks(
            'analytics_daily_metrics',
            $records,
            [
                'analytics_property_id',
                'date',
            ],
            [
                'active_users',
                'total_users',
                'new_users',
                'sessions',
                'engaged_sessions',
                'engagement_rate',
                'average_session_duration',
                'screen_page_views',
                'event_count',
                'key_events',
                'total_revenue',
                'synced_at',
                'updated_at',
            ]
        );

        return count(
            $records
        );
    }

    protected function syncPages(
        AnalyticsProperty $property,
        Carbon $from,
        Carbon $to
    ): int {

        $rows =
            $this->client
                ->runPaginatedReport(
                    $property,
                    [
                        'dateRanges' => [
                            [
                                'startDate' =>
                                    $from
                                        ->toDateString(),

                                'endDate' =>
                                    $to
                                        ->toDateString(),
                            ],
                        ],

                        'dimensions' => [
                            [
                                'name' =>
                                    'date',
                            ],

                            [
                                'name' =>
                                    'pagePath',
                            ],

                            [
                                'name' =>
                                    'pageTitle',
                            ],
                        ],

                        'metrics' =>
                            $this
                                ->pageMetricDefinitions(),
                    ]
                );

        $records = [];

        foreach (
            $rows as $row
        ) {

            $dimensions =
                $this->dimensions(
                    $row
                );

            $metrics =
                $this->metrics(
                    $row
                );

            $date =
                $this->gaDate(
                    $dimensions[0]
                    ?? null
                );

            $pagePath =
                $dimensions[1]
                ?? null;

            $pageTitle =
                $dimensions[2]
                ?? null;

            if (
                !$date
                || !$pagePath
            ) {
                continue;
            }

            $hash =
                hash(
                    'sha256',
                    implode(
                        '|',
                        [
                            $property->id,
                            $date,
                            'page',
                            $pagePath,
                            $pageTitle
                                ?? '',
                        ]
                    )
                );

            $records[] =
                $this->pageRecord(
                    property:
                        $property,

                    date:
                        $date,

                    grain:
                        'page',

                    metrics:
                        $metrics,

                    hash:
                        $hash,

                    pagePath:
                        $pagePath,

                    pageTitle:
                        $pageTitle,
                );
        }

        $this->upsertPageRecords(
            $records
        );

        return count(
            $records
        );
    }

    protected function syncLandingPages(
        AnalyticsProperty $property,
        Carbon $from,
        Carbon $to
    ): int {

        $rows =
            $this->client
                ->runPaginatedReport(
                    $property,
                    [
                        'dateRanges' => [
                            [
                                'startDate' =>
                                    $from
                                        ->toDateString(),

                                'endDate' =>
                                    $to
                                        ->toDateString(),
                            ],
                        ],

                        'dimensions' => [
                            [
                                'name' =>
                                    'date',
                            ],

                            [
                                'name' =>
                                    'landingPage',
                            ],
                        ],

                        'metrics' =>
                            $this
                                ->pageMetricDefinitions(),
                    ]
                );

        $records = [];

        foreach (
            $rows as $row
        ) {

            $dimensions =
                $this->dimensions(
                    $row
                );

            $metrics =
                $this->metrics(
                    $row
                );

            $date =
                $this->gaDate(
                    $dimensions[0]
                    ?? null
                );

            $landing =
                $dimensions[1]
                ?? null;

            if (
                !$date
                || !$landing
            ) {
                continue;
            }

            $hash =
                hash(
                    'sha256',
                    implode(
                        '|',
                        [
                            $property->id,
                            $date,
                            'landing_page',
                            $landing,
                        ]
                    )
                );

            $records[] =
                $this->pageRecord(
                    property:
                        $property,

                    date:
                        $date,

                    grain:
                        'landing_page',

                    metrics:
                        $metrics,

                    hash:
                        $hash,

                    landingPage:
                        $landing,
                );
        }

        $this->upsertPageRecords(
            $records
        );

        return count(
            $records
        );
    }

    protected function syncTraffic(
        AnalyticsProperty $property,
        Carbon $from,
        Carbon $to
    ): int {

        $rows =
            $this->client
                ->runPaginatedReport(
                    $property,
                    [
                        'dateRanges' => [
                            [
                                'startDate' =>
                                    $from
                                        ->toDateString(),

                                'endDate' =>
                                    $to
                                        ->toDateString(),
                            ],
                        ],

                        'dimensions' => [
                            [
                                'name' =>
                                    'date',
                            ],

                            [
                                'name' =>
                                    'sessionSource',
                            ],

                            [
                                'name' =>
                                    'sessionMedium',
                            ],

                            [
                                'name' =>
                                    'sessionCampaignName',
                            ],

                            [
                                'name' =>
                                    'sessionDefaultChannelGroup',
                            ],

                            [
                                'name' =>
                                    'landingPage',
                            ],
                        ],

                        'metrics' => [
                            [
                                'name' =>
                                    'activeUsers',
                            ],

                            [
                                'name' =>
                                    'newUsers',
                            ],

                            [
                                'name' =>
                                    'sessions',
                            ],

                            [
                                'name' =>
                                    'engagedSessions',
                            ],

                            [
                                'name' =>
                                    'engagementRate',
                            ],

                            [
                                'name' =>
                                    'keyEvents',
                            ],

                            [
                                'name' =>
                                    'totalRevenue',
                            ],
                        ],
                    ]
                );

        $records = [];

        foreach (
            $rows as $row
        ) {

            $dimensions =
                $this->dimensions(
                    $row
                );

            $metrics =
                $this->metrics(
                    $row
                );

            $date =
                $this->gaDate(
                    $dimensions[0]
                    ?? null
                );

            if (!$date) {
                continue;
            }

            $source =
                $dimensions[1]
                ?? null;

            $medium =
                $dimensions[2]
                ?? null;

            $campaign =
                $dimensions[3]
                ?? null;

            $channel =
                $dimensions[4]
                ?? null;

            $landing =
                $dimensions[5]
                ?? null;

            $hash =
                hash(
                    'sha256',
                    implode(
                        '|',
                        [
                            $property->id,
                            $date,
                            $source ?? '',
                            $medium ?? '',
                            $campaign ?? '',
                            $channel ?? '',
                            $landing ?? '',
                        ]
                    )
                );

            $records[] = [
                'analytics_property_id' =>
                    $property->id,

                'date' =>
                    $date,

                'source' =>
                    $source,

                'medium' =>
                    $medium,

                'campaign' =>
                    $campaign,

                'channel_group' =>
                    $channel,

                'landing_page' =>
                    $landing,

                'active_users' =>
                    $this->integer(
                        $metrics[0]
                        ?? null
                    ),

                'new_users' =>
                    $this->integer(
                        $metrics[1]
                        ?? null
                    ),

                'sessions' =>
                    $this->integer(
                        $metrics[2]
                        ?? null
                    ),

                'engaged_sessions' =>
                    $this->integer(
                        $metrics[3]
                        ?? null
                    ),

                'engagement_rate' =>
                    $this->decimal(
                        $metrics[4]
                        ?? null
                    ),

                'key_events' =>
                    $this->decimal(
                        $metrics[5]
                        ?? null
                    ),

                'total_revenue' =>
                    $this->decimal(
                        $metrics[6]
                        ?? null
                    ),

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

        $this->upsertChunks(
            'analytics_traffic_metrics',
            $records,
            [
                'analytics_property_id',
                'date',
                'dimension_hash',
            ],
            [
                'source',
                'medium',
                'campaign',
                'channel_group',
                'landing_page',
                'active_users',
                'new_users',
                'sessions',
                'engaged_sessions',
                'engagement_rate',
                'key_events',
                'total_revenue',
                'synced_at',
                'updated_at',
            ]
        );

        return count(
            $records
        );
    }

    protected function syncEvents(
        AnalyticsProperty $property,
        Carbon $from,
        Carbon $to
    ): int {

        $rows =
            $this->client
                ->runPaginatedReport(
                    $property,
                    [
                        'dateRanges' => [
                            [
                                'startDate' =>
                                    $from
                                        ->toDateString(),

                                'endDate' =>
                                    $to
                                        ->toDateString(),
                            ],
                        ],

                        'dimensions' => [
                            [
                                'name' =>
                                    'date',
                            ],

                            [
                                'name' =>
                                    'eventName',
                            ],
                        ],

                        'metrics' => [
                            [
                                'name' =>
                                    'eventCount',
                            ],

                            [
                                'name' =>
                                    'totalUsers',
                            ],

                            [
                                'name' =>
                                    'keyEvents',
                            ],

                            [
                                'name' =>
                                    'eventValue',
                            ],
                        ],
                    ]
                );

        $records = [];

        foreach (
            $rows as $row
        ) {

            $dimensions =
                $this->dimensions(
                    $row
                );

            $metrics =
                $this->metrics(
                    $row
                );

            $date =
                $this->gaDate(
                    $dimensions[0]
                    ?? null
                );

            $event =
                $dimensions[1]
                ?? null;

            if (
                !$date
                || !$event
            ) {
                continue;
            }

            $records[] = [
                'analytics_property_id' =>
                    $property->id,

                'date' =>
                    $date,

                'event_name' =>
                    $event,

                'event_count' =>
                    $this->integer(
                        $metrics[0]
                        ?? null
                    ),

                'total_users' =>
                    $this->integer(
                        $metrics[1]
                        ?? null
                    ),

                'key_events' =>
                    $this->decimal(
                        $metrics[2]
                        ?? null
                    ),

                'event_value' =>
                    $this->decimal(
                        $metrics[3]
                        ?? null
                    ),

                'synced_at' =>
                    now(),

                'created_at' =>
                    now(),

                'updated_at' =>
                    now(),
            ];
        }

        $this->upsertChunks(
            'analytics_event_metrics',
            $records,
            [
                'analytics_property_id',
                'date',
                'event_name',
            ],
            [
                'event_count',
                'total_users',
                'key_events',
                'event_value',
                'synced_at',
                'updated_at',
            ]
        );

        return count(
            $records
        );
    }

    protected function pageMetricDefinitions(): array
    {
        return [
            [
                'name' =>
                    'activeUsers',
            ],

            [
                'name' =>
                    'sessions',
            ],

            [
                'name' =>
                    'engagedSessions',
            ],

            [
                'name' =>
                    'screenPageViews',
            ],

            [
                'name' =>
                    'eventCount',
            ],

            [
                'name' =>
                    'keyEvents',
            ],

            [
                'name' =>
                    'engagementRate',
            ],

            [
                'name' =>
                    'averageSessionDuration',
            ],
        ];
    }

    protected function pageRecord(
        AnalyticsProperty $property,
        string $date,
        string $grain,
        array $metrics,
        string $hash,
        ?string $pagePath = null,
        ?string $pageTitle = null,
        ?string $landingPage = null,
    ): array {

        return [
            'analytics_property_id' =>
                $property->id,

            'date' =>
                $date,

            'grain' =>
                $grain,

            'page_path' =>
                $pagePath,

            'page_title' =>
                $pageTitle,

            'landing_page' =>
                $landingPage,

            'active_users' =>
                $this->integer(
                    $metrics[0]
                    ?? null
                ),

            'sessions' =>
                $this->integer(
                    $metrics[1]
                    ?? null
                ),

            'engaged_sessions' =>
                $this->integer(
                    $metrics[2]
                    ?? null
                ),

            'screen_page_views' =>
                $this->integer(
                    $metrics[3]
                    ?? null
                ),

            'event_count' =>
                $this->integer(
                    $metrics[4]
                    ?? null
                ),

            'key_events' =>
                $this->decimal(
                    $metrics[5]
                    ?? null
                ),

            'engagement_rate' =>
                $this->decimal(
                    $metrics[6]
                    ?? null
                ),

            'average_session_duration' =>
                $this->decimal(
                    $metrics[7]
                    ?? null
                ),

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

    protected function upsertPageRecords(
        array $records
    ): void {

        $this->upsertChunks(
            'analytics_page_metrics',
            $records,
            [
                'analytics_property_id',
                'date',
                'grain',
                'dimension_hash',
            ],
            [
                'page_path',
                'page_title',
                'landing_page',
                'active_users',
                'sessions',
                'engaged_sessions',
                'screen_page_views',
                'event_count',
                'key_events',
                'engagement_rate',
                'average_session_duration',
                'synced_at',
                'updated_at',
            ]
        );
    }

    protected function upsertChunks(
        string $table,
        array $records,
        array $uniqueBy,
        array $update
    ): void {

        foreach (
            array_chunk(
                $records,
                1000
            )
            as $chunk
        ) {

            DB::table(
                $table
            )->upsert(
                $chunk,
                $uniqueBy,
                $update
            );
        }
    }

    protected function dimensions(
        array $row
    ): array {

        return collect(
            $row[
                'dimensionValues'
            ]
            ?? []
        )
            ->map(
                fn ($value) =>
                    $value[
                        'value'
                    ]
                    ?? null
            )
            ->all();
    }

    protected function metrics(
        array $row
    ): array {

        return collect(
            $row[
                'metricValues'
            ]
            ?? []
        )
            ->map(
                fn ($value) =>
                    $value[
                        'value'
                    ]
                    ?? null
            )
            ->all();
    }

    protected function integer(
        mixed $value
    ): int {

        return (int) (
            $value
            ?? 0
        );
    }

    protected function decimal(
        mixed $value
    ): float {

        return (float) (
            $value
            ?? 0
        );
    }

    protected function gaDate(
        ?string $value
    ): ?string {

        if (
            !$value
            || strlen($value) !== 8
        ) {
            return null;
        }

        return Carbon::createFromFormat(
            'Ymd',
            $value
        )->toDateString();
    }
}