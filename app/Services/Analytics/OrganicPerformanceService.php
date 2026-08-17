<?php

namespace App\Services\Analytics;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrganicPerformanceService
{
    public function resolveDates(
        ?string $startDate,
        ?string $endDate
    ): array {
        $end =
            $endDate
                ? Carbon::parse($endDate)
                : now()->subDay();

        $start =
            $startDate
                ? Carbon::parse($startDate)
                : $end->copy()->subDays(29);

        if ($start->greaterThan($end)) {
            [$start, $end] = [
                $end,
                $start,
            ];
        }

        return [
            $start->startOfDay(),
            $end->startOfDay(),
        ];
    }

    protected function searchConsolePages(
        int $siteId,
        Carbon $start,
        Carbon $end
    ): Builder {
        return DB::table(
            'search_console_metrics'
        )
            ->where(
                'search_console_site_id',
                $siteId
            )
            ->where(
                'grain',
                'page'
            )
            ->whereNotNull('page')
            ->whereBetween(
                'date',
                [
                    $start->toDateString(),
                    $end->toDateString(),
                ]
            )
            ->selectRaw("
                date,

                regexp_replace(
                    regexp_replace(
                        page,
                        '^https?://[^/]+',
                        ''
                    ),
                    '[?#].*$',
                    ''
                ) AS path,

                clicks,
                impressions,
                ctr,
                position
            ");
    }

    protected function analyticsOrganicPages(
        int $propertyId,
        Carbon $start,
        Carbon $end
    ): Builder {
        return DB::table(
            'analytics_traffic_metrics'
        )
            ->where(
                'analytics_property_id',
                $propertyId
            )
            ->where(
                'channel_group',
                'Organic Search'
            )
            ->whereBetween(
                'date',
                [
                    $start->toDateString(),
                    $end->toDateString(),
                ]
            )
            ->whereNotNull(
                'landing_page'
            )
            ->selectRaw("
                date,

                CASE
                    WHEN landing_page = '(not set)'
                    THEN NULL
                    ELSE split_part(
                        landing_page,
                        '?',
                        1
                    )
                END AS path,

                sessions,
                engaged_sessions,
                key_events,
                active_users
            ");
    }

    public function landingPages(
        int $siteId,
        int $propertyId,
        Carbon $start,
        Carbon $end,
        int $limit = 50
    ): Collection {
        $searchConsole =
            $this->searchConsolePages(
                $siteId,
                $start,
                $end
            );

        $analytics =
            $this->analyticsOrganicPages(
                $propertyId,
                $start,
                $end
            );

        $scAggregated =
            DB::query()
                ->fromSub(
                    $searchConsole,
                    'sc'
                )
                ->whereNotNull('path')
                ->groupBy('path')
                ->selectRaw("
                    path,

                    SUM(clicks)
                        AS search_clicks,

                    SUM(impressions)
                        AS impressions,

                    CASE
                        WHEN SUM(impressions) > 0
                        THEN
                            SUM(clicks)::decimal
                            / SUM(impressions)
                        ELSE 0
                    END AS ctr,

                    CASE
                        WHEN SUM(impressions) > 0
                        THEN
                            SUM(
                                position
                                * impressions
                            )
                            / SUM(impressions)
                        ELSE 0
                    END AS position
                ");

        $gaAggregated =
            DB::query()
                ->fromSub(
                    $analytics,
                    'ga'
                )
                ->whereNotNull('path')
                ->groupBy('path')
                ->selectRaw("
                    path,

                    SUM(sessions)
                        AS sessions,

                    SUM(engaged_sessions)
                        AS engaged_sessions,

                    SUM(key_events)
                        AS key_events,

                    SUM(active_users)
                        AS active_users
                ");

        return DB::query()
            ->fromSub(
                $scAggregated,
                'sc'
            )
            ->leftJoinSub(
                $gaAggregated,
                'ga',
                'ga.path',
                '=',
                'sc.path'
            )
            ->selectRaw("
                sc.path,

                sc.search_clicks,
                sc.impressions,
                sc.ctr,
                sc.position,

                COALESCE(
                    ga.sessions,
                    0
                ) AS sessions,

                COALESCE(
                    ga.engaged_sessions,
                    0
                ) AS engaged_sessions,

                COALESCE(
                    ga.key_events,
                    0
                ) AS key_events,

                COALESCE(
                    ga.active_users,
                    0
                ) AS active_users,

                CASE
                    WHEN COALESCE(
                        ga.sessions,
                        0
                    ) > 0
                    THEN
                        ga.engaged_sessions::decimal
                        / ga.sessions
                    ELSE 0
                END AS engagement_rate,

                CASE
                    WHEN COALESCE(
                        ga.sessions,
                        0
                    ) > 0
                    THEN
                        ga.key_events::decimal
                        / ga.sessions
                    ELSE 0
                END AS key_event_rate,

                CASE
                    WHEN sc.search_clicks > 0
                    THEN
                        COALESCE(
                            ga.sessions,
                            0
                        )::decimal
                        / sc.search_clicks
                    ELSE 0
                END AS session_click_ratio
            ")
            ->orderByDesc(
                'sc.search_clicks'
            )
            ->limit(
                $limit
            )
            ->get();
    }

    public function summary(
        int $siteId,
        int $propertyId,
        Carbon $start,
        Carbon $end
    ): array {
        $rows =
            $this->landingPages(
                $siteId,
                $propertyId,
                $start,
                $end,
                10000
            );

        $clicks =
            $rows->sum(
                'search_clicks'
            );

        $impressions =
            $rows->sum(
                'impressions'
            );

        $sessions =
            $rows->sum(
                'sessions'
            );

        $engaged =
            $rows->sum(
                'engaged_sessions'
            );

        $keyEvents =
            $rows->sum(
                'key_events'
            );

        return [
            'clicks' =>
                (int) $clicks,

            'impressions' =>
                (int) $impressions,

            'sessions' =>
                (int) $sessions,

            'engaged_sessions' =>
                (int) $engaged,

            'key_events' =>
                (float) $keyEvents,

            'engagement_rate' =>
                $sessions > 0
                    ? $engaged
                        / $sessions
                    : 0,

            'key_event_rate' =>
                $sessions > 0
                    ? $keyEvents
                        / $sessions
                    : 0,

            'session_click_ratio' =>
                $clicks > 0
                    ? $sessions
                        / $clicks
                    : 0,
        ];
    }

    public function timeline(
        int $siteId,
        int $propertyId,
        Carbon $start,
        Carbon $end
    ): Collection {
        $searchConsole =
            DB::table(
                'search_console_metrics'
            )
                ->where(
                    'search_console_site_id',
                    $siteId
                )
                ->where(
                    'grain',
                    'site'
                )
                ->whereBetween(
                    'date',
                    [
                        $start->toDateString(),
                        $end->toDateString(),
                    ]
                )
                ->groupBy('date')
                ->selectRaw("
                    date,
                    SUM(clicks) AS clicks,
                    SUM(impressions) AS impressions
                ");

        $analytics =
            DB::table(
                'analytics_traffic_metrics'
            )
                ->where(
                    'analytics_property_id',
                    $propertyId
                )
                ->where(
                    'channel_group',
                    'Organic Search'
                )
                ->whereBetween(
                    'date',
                    [
                        $start->toDateString(),
                        $end->toDateString(),
                    ]
                )
                ->groupBy('date')
                ->selectRaw("
                    date,
                    SUM(sessions)
                        AS sessions,
                    SUM(key_events)
                        AS key_events
                ");

        return DB::query()
            ->fromSub(
                $searchConsole,
                'sc'
            )
            ->leftJoinSub(
                $analytics,
                'ga',
                'ga.date',
                '=',
                'sc.date'
            )
            ->selectRaw("
                sc.date,
                sc.clicks,
                sc.impressions,

                COALESCE(
                    ga.sessions,
                    0
                ) AS sessions,

                COALESCE(
                    ga.key_events,
                    0
                ) AS key_events
            ")
            ->orderBy(
                'sc.date'
            )
            ->get();
    }

    public function opportunities(
        int $siteId,
        int $propertyId,
        Carbon $start,
        Carbon $end,
        int $limit = 20
    ): Collection {
        return $this->landingPages(
            $siteId,
            $propertyId,
            $start,
            $end,
            500
        )
            ->map(
                function ($row) {

                    $row->opportunity =
                        $this
                            ->opportunityType(
                                $row
                            );

                    return $row;
                }
            )
            ->filter(
                fn ($row) =>
                    $row->opportunity
                    !== 'Monitor'
            )
            ->sortByDesc(
                function ($row) {

                    return match (
                        $row->opportunity
                    ) {
                        'High traffic, low conversion'
                            => 100000
                                + $row->sessions,

                        'High impressions, low CTR'
                            => 80000
                                + $row->impressions,

                        'Near top 3'
                            => 60000
                                + $row->impressions,

                        'Traffic mismatch'
                            => 40000
                                + $row->search_clicks,

                        default =>
                            $row->search_clicks,
                    };
                }
            )
            ->take(
                $limit
            )
            ->values();
    }

    public function opportunityType(
        object $row
    ): string {
        if (
            $row->sessions >= 30
            && $row->key_event_rate
                < 0.03
        ) {
            return 'High traffic, low conversion';
        }

        if (
            $row->impressions >= 100
            && $row->ctr < 0.02
        ) {
            return 'High impressions, low CTR';
        }

        if (
            $row->position > 3
            && $row->position <= 6
        ) {
            return 'Near top 3';
        }

        if (
            $row->search_clicks >= 20
            && $row->session_click_ratio
                < 0.60
        ) {
            return 'Traffic mismatch';
        }

        return 'Monitor';
    }
}