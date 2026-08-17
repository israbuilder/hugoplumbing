<?php

namespace App\Services\Analytics;

use App\Models\AnalyticsDailyMetric;
use App\Models\AnalyticsEventMetric;
use App\Models\AnalyticsPageMetric;
use App\Models\AnalyticsProperty;
use App\Models\AnalyticsTrafficMetric;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class GoogleAnalyticsService
{
    public function resolveDates(
        ?string $startDate,
        ?string $endDate
    ): array {
        $end =
            $endDate
                ? Carbon::parse(
                    $endDate
                )
                : now()->subDay();

        $start =
            $startDate
                ? Carbon::parse(
                    $startDate
                )
                : $end
                    ->copy()
                    ->subDays(29);

        if (
            $start->greaterThan(
                $end
            )
        ) {
            [
                $start,
                $end
            ] = [
                $end,
                $start
            ];
        }

        return [
            $start->startOfDay(),
            $end->startOfDay(),
        ];
    }

    public function previousPeriod(
        Carbon $start,
        Carbon $end
    ): array {
        $days =
            $start
                ->diffInDays(
                    $end
                )
            + 1;

        $previousEnd =
            $start
                ->copy()
                ->subDay();

        $previousStart =
            $previousEnd
                ->copy()
                ->subDays(
                    $days - 1
                );

        return [
            $previousStart,
            $previousEnd,
        ];
    }

    public function defaultPropertyId(): ?int
    {
        return AnalyticsProperty::query()
            ->where(
                'is_active',
                true
            )
            ->orderByDesc(
                'is_primary'
            )
            ->value('id');
    }

    public function summary(
        int $propertyId,
        Carbon $start,
        Carbon $end
    ): array {
        $query =
            AnalyticsDailyMetric::query()
                ->where(
                    'analytics_property_id',
                    $propertyId
                )
                ->whereBetween(
                    'date',
                    [
                        $start
                            ->toDateString(),

                        $end
                            ->toDateString(),
                    ]
                );

        $data =
            $query
                ->selectRaw('
                    COALESCE(
                        SUM(active_users),
                        0
                    ) AS active_users,

                    COALESCE(
                        SUM(total_users),
                        0
                    ) AS total_users,

                    COALESCE(
                        SUM(new_users),
                        0
                    ) AS new_users,

                    COALESCE(
                        SUM(sessions),
                        0
                    ) AS sessions,

                    COALESCE(
                        SUM(engaged_sessions),
                        0
                    ) AS engaged_sessions,

                    CASE
                        WHEN SUM(sessions) > 0
                        THEN
                            SUM(engaged_sessions)::decimal
                            / SUM(sessions)
                        ELSE 0
                    END AS engagement_rate,

                    CASE
                        WHEN SUM(sessions) > 0
                        THEN
                            SUM(
                                average_session_duration
                                * sessions
                            )
                            / SUM(sessions)
                        ELSE 0
                    END AS average_session_duration,

                    COALESCE(
                        SUM(screen_page_views),
                        0
                    ) AS screen_page_views,

                    COALESCE(
                        SUM(event_count),
                        0
                    ) AS event_count,

                    COALESCE(
                        SUM(key_events),
                        0
                    ) AS key_events
                ')
                ->first();

        return [
            'active_users' =>
                (int)
                (
                    $data
                        ->active_users
                    ?? 0
                ),

            'total_users' =>
                (int)
                (
                    $data
                        ->total_users
                    ?? 0
                ),

            'new_users' =>
                (int)
                (
                    $data
                        ->new_users
                    ?? 0
                ),

            'sessions' =>
                (int)
                (
                    $data
                        ->sessions
                    ?? 0
                ),

            'engaged_sessions' =>
                (int)
                (
                    $data
                        ->engaged_sessions
                    ?? 0
                ),

            'engagement_rate' =>
                (float)
                (
                    $data
                        ->engagement_rate
                    ?? 0
                ),

            'average_session_duration' =>
                (float)
                (
                    $data
                        ->average_session_duration
                    ?? 0
                ),

            'screen_page_views' =>
                (int)
                (
                    $data
                        ->screen_page_views
                    ?? 0
                ),

            'event_count' =>
                (int)
                (
                    $data
                        ->event_count
                    ?? 0
                ),

            'key_events' =>
                (float)
                (
                    $data
                        ->key_events
                    ?? 0
                ),
        ];
    }

    public function comparison(
        int $propertyId,
        Carbon $start,
        Carbon $end
    ): array {
        $current =
            $this->summary(
                $propertyId,
                $start,
                $end
            );

        [
            $previousStart,
            $previousEnd
        ] =
            $this->previousPeriod(
                $start,
                $end
            );

        $previous =
            $this->summary(
                $propertyId,
                $previousStart,
                $previousEnd
            );

        return [
            'current' =>
                $current,

            'previous' =>
                $previous,

            'changes' => [

                'active_users' =>
                    $this
                        ->percentageChange(
                            $current[
                                'active_users'
                            ],
                            $previous[
                                'active_users'
                            ]
                        ),

                'sessions' =>
                    $this
                        ->percentageChange(
                            $current[
                                'sessions'
                            ],
                            $previous[
                                'sessions'
                            ]
                        ),

                'new_users' =>
                    $this
                        ->percentageChange(
                            $current[
                                'new_users'
                            ],
                            $previous[
                                'new_users'
                            ]
                        ),

                'engagement_rate' =>
                    $this
                        ->percentageChange(
                            $current[
                                'engagement_rate'
                            ],
                            $previous[
                                'engagement_rate'
                            ]
                        ),

                'key_events' =>
                    $this
                        ->percentageChange(
                            $current[
                                'key_events'
                            ],
                            $previous[
                                'key_events'
                            ]
                        ),

                'screen_page_views' =>
                    $this
                        ->percentageChange(
                            $current[
                                'screen_page_views'
                            ],
                            $previous[
                                'screen_page_views'
                            ]
                        ),
            ],
        ];
    }

    protected function percentageChange(
        float|int $current,
        float|int $previous
    ): float {
        if (
            (float) $previous
            === 0.0
        ) {
            return
                (float) $current > 0
                    ? 100.0
                    : 0.0;
        }

        return (
            (
                $current
                -
                $previous
            )
            /
            abs(
                $previous
            )
        ) * 100;
    }

    public function timeline(
        int $propertyId,
        Carbon $start,
        Carbon $end
    ): Collection {
        return AnalyticsDailyMetric::query()
            ->where(
                'analytics_property_id',
                $propertyId
            )
            ->whereBetween(
                'date',
                [
                    $start
                        ->toDateString(),

                    $end
                        ->toDateString(),
                ]
            )
            ->orderBy(
                'date'
            )
            ->get([
                'date',
                'active_users',
                'sessions',
                'new_users',
                'key_events',
            ]);
    }

    public function channels(
        int $propertyId,
        Carbon $start,
        Carbon $end,
        ?string $channel = null
    ): Collection {
        $query =
            AnalyticsTrafficMetric::query()
                ->where(
                    'analytics_property_id',
                    $propertyId
                )
                ->whereBetween(
                    'date',
                    [
                        $start
                            ->toDateString(),

                        $end
                            ->toDateString(),
                    ]
                );

        if ($channel) {
            $query->where(
                'channel_group',
                $channel
            );
        }

        return $query
            ->selectRaw('
                COALESCE(
                    channel_group,
                    \'Unassigned\'
                ) AS channel,

                SUM(sessions)
                    AS sessions,

                SUM(active_users)
                    AS users,

                SUM(key_events)
                    AS key_events
            ')
            ->groupBy(
                'channel_group'
            )
            ->orderByDesc(
                'sessions'
            )
            ->get();
    }

    public function topLandingPages(
        int $propertyId,
        Carbon $start,
        Carbon $end,
        ?string $channel = null,
        int $limit = 15
    ): Collection {
        /*
         * If a channel filter is used,
         * traffic_metrics is the better source
         * because it includes landing page + channel.
         */
        if ($channel) {

            return AnalyticsTrafficMetric::query()
                ->where(
                    'analytics_property_id',
                    $propertyId
                )
                ->where(
                    'channel_group',
                    $channel
                )
                ->whereNotNull(
                    'landing_page'
                )
                ->whereBetween(
                    'date',
                    [
                        $start
                            ->toDateString(),

                        $end
                            ->toDateString(),
                    ]
                )
                ->selectRaw('
                    landing_page,

                    SUM(sessions)
                        AS sessions,

                    SUM(engaged_sessions)
                        AS engaged_sessions,

                    CASE
                        WHEN SUM(sessions) > 0
                        THEN
                            SUM(engaged_sessions)::decimal
                            / SUM(sessions)
                        ELSE 0
                    END
                        AS engagement_rate,

                    SUM(key_events)
                        AS key_events
                ')
                ->groupBy(
                    'landing_page'
                )
                ->orderByDesc(
                    'sessions'
                )
                ->limit(
                    $limit
                )
                ->get();
        }

        return AnalyticsPageMetric::query()
            ->where(
                'analytics_property_id',
                $propertyId
            )
            ->where(
                'grain',
                'landing_page'
            )
            ->whereNotNull(
                'landing_page'
            )
            ->whereBetween(
                'date',
                [
                    $start
                        ->toDateString(),

                    $end
                        ->toDateString(),
                ]
            )
            ->selectRaw('
                landing_page,

                SUM(sessions)
                    AS sessions,

                SUM(engaged_sessions)
                    AS engaged_sessions,

                CASE
                    WHEN SUM(sessions) > 0
                    THEN
                        SUM(engaged_sessions)::decimal
                        / SUM(sessions)
                    ELSE 0
                END
                    AS engagement_rate,

                SUM(key_events)
                    AS key_events,

                SUM(screen_page_views)
                    AS page_views
            ')
            ->groupBy(
                'landing_page'
            )
            ->orderByDesc(
                'sessions'
            )
            ->limit(
                $limit
            )
            ->get();
    }

    public function topSources(
        int $propertyId,
        Carbon $start,
        Carbon $end,
        ?string $channel = null,
        int $limit = 15
    ): Collection {
        $query =
            AnalyticsTrafficMetric::query()
                ->where(
                    'analytics_property_id',
                    $propertyId
                )
                ->whereBetween(
                    'date',
                    [
                        $start
                            ->toDateString(),

                        $end
                            ->toDateString(),
                    ]
                );

        if ($channel) {

            $query->where(
                'channel_group',
                $channel
            );
        }

        return $query
            ->selectRaw('
                COALESCE(
                    source,
                    \'(not set)\'
                ) AS source,

                COALESCE(
                    medium,
                    \'(not set)\'
                ) AS medium,

                SUM(sessions)
                    AS sessions,

                SUM(active_users)
                    AS users,

                SUM(new_users)
                    AS new_users,

                SUM(engaged_sessions)
                    AS engaged_sessions,

                CASE
                    WHEN SUM(sessions) > 0
                    THEN
                        SUM(engaged_sessions)::decimal
                        / SUM(sessions)
                    ELSE 0
                END
                    AS engagement_rate,

                SUM(key_events)
                    AS key_events
            ')
            ->groupBy([
                'source',
                'medium',
            ])
            ->orderByDesc(
                'sessions'
            )
            ->limit(
                $limit
            )
            ->get();
    }

    public function topEvents(
        int $propertyId,
        Carbon $start,
        Carbon $end,
        int $limit = 15
    ): Collection {
        return AnalyticsEventMetric::query()
            ->where(
                'analytics_property_id',
                $propertyId
            )
            ->whereBetween(
                'date',
                [
                    $start
                        ->toDateString(),

                    $end
                        ->toDateString(),
                ]
            )
            ->selectRaw('
                event_name,

                SUM(event_count)
                    AS event_count,

                SUM(total_users)
                    AS total_users,

                SUM(key_events)
                    AS key_events,

                SUM(event_value)
                    AS event_value
            ')
            ->groupBy(
                'event_name'
            )
            ->orderByDesc(
                'event_count'
            )
            ->limit(
                $limit
            )
            ->get();
    }
}