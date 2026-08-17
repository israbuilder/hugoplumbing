<?php

namespace App\Services\Analytics;

use App\Models\SearchConsoleMetric;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SeoAnalyticsService
{
    public function resolveDates(
        ?string $startDate,
        ?string $endDate
    ): array {
        $end = $endDate
            ? Carbon::parse($endDate)
            : now()->subDay();

        $start = $startDate
            ? Carbon::parse($startDate)
            : $end->copy()->subDays(27);

        if ($start->greaterThan($end)) {
            [$start, $end] = [$end, $start];
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
        $days = $start->diffInDays($end) + 1;

        $previousEnd =
            $start
                ->copy()
                ->subDay();

        $previousStart =
            $previousEnd
                ->copy()
                ->subDays($days - 1);

        return [
            $previousStart,
            $previousEnd,
        ];
    }

    protected function query(
        int $siteId,
        Carbon $start,
        Carbon $end,
        string $grain = 'site'
    ): Builder {
        return SearchConsoleMetric::query()
            ->where(
                'search_console_site_id',
                $siteId
            )
            ->where(
                'grain',
                $grain
            )
            ->whereBetween(
                'date',
                [
                    $start->toDateString(),
                    $end->toDateString(),
                ]
            );
    }

    public function summary(
        int $siteId,
        Carbon $start,
        Carbon $end
    ): array {
        $data = $this->query(
            $siteId,
            $start,
            $end,
            'site'
        )
            ->selectRaw('
                COALESCE(SUM(clicks), 0) AS clicks,
                COALESCE(SUM(impressions), 0) AS impressions,
                CASE
                    WHEN SUM(impressions) > 0
                    THEN SUM(clicks)::decimal
                        / SUM(impressions)
                    ELSE 0
                END AS ctr,
                CASE
                    WHEN SUM(impressions) > 0
                    THEN SUM(
                        position * impressions
                    ) / SUM(impressions)
                    ELSE 0
                END AS position
            ')
            ->first();

        return [
            'clicks' =>
                (int) ($data->clicks ?? 0),

            'impressions' =>
                (int) ($data->impressions ?? 0),

            'ctr' =>
                (float) ($data->ctr ?? 0),

            'position' =>
                (float) ($data->position ?? 0),
        ];
    }

    public function comparison(
        int $siteId,
        Carbon $start,
        Carbon $end
    ): array {
        $current = $this->summary(
            $siteId,
            $start,
            $end
        );

        [
            $previousStart,
            $previousEnd
        ] = $this->previousPeriod(
            $start,
            $end
        );

        $previous = $this->summary(
            $siteId,
            $previousStart,
            $previousEnd
        );

        return [
            'current' => $current,

            'previous' => $previous,

            'change' => [
                'clicks' =>
                    $this->percentChange(
                        $current['clicks'],
                        $previous['clicks']
                    ),

                'impressions' =>
                    $this->percentChange(
                        $current['impressions'],
                        $previous['impressions']
                    ),

                'ctr' =>
                    $this->percentChange(
                        $current['ctr'],
                        $previous['ctr']
                    ),

                /*
                 * Position works differently:
                 * lower is better.
                 *
                 * 9.5 -> 6.2
                 * improvement = +3.3
                 */
                'position' =>
                    $previous['position']
                        - $current['position'],
            ],

            'previous_dates' => [
                'start' => $previousStart,
                'end' => $previousEnd,
            ],
        ];
    }

    protected function percentChange(
        float|int $current,
        float|int $previous
    ): float {
        if ((float) $previous === 0.0) {
            return (float) $current > 0
                ? 100.0
                : 0.0;
        }

        return (
            ($current - $previous)
            / abs($previous)
        ) * 100;
    }

    public function timeline(
        int $siteId,
        Carbon $start,
        Carbon $end
    ): Collection {
        return $this->query(
            $siteId,
            $start,
            $end,
            'site'
        )
            ->select([
                'date',
                'clicks',
                'impressions',
                'ctr',
                'position',
            ])
            ->orderBy('date')
            ->get();
    }

    public function topQueries(
        int $siteId,
        Carbon $start,
        Carbon $end,
        int $limit = 15
    ): Collection {
        return $this->query(
            $siteId,
            $start,
            $end,
            'query'
        )
            ->whereNotNull('query')
            ->selectRaw('
                query,

                SUM(clicks) AS clicks,

                SUM(impressions) AS impressions,

                CASE
                    WHEN SUM(impressions) > 0
                    THEN SUM(clicks)::decimal
                        / SUM(impressions)
                    ELSE 0
                END AS ctr,

                CASE
                    WHEN SUM(impressions) > 0
                    THEN SUM(
                        position * impressions
                    ) / SUM(impressions)
                    ELSE 0
                END AS position
            ')
            ->groupBy('query')
            ->orderByDesc('clicks')
            ->limit($limit)
            ->get();
    }

    public function topPages(
        int $siteId,
        Carbon $start,
        Carbon $end,
        int $limit = 15
    ): Collection {
        return $this->query(
            $siteId,
            $start,
            $end,
            'page'
        )
            ->whereNotNull('page')
            ->selectRaw('
                page,

                SUM(clicks) AS clicks,

                SUM(impressions) AS impressions,

                CASE
                    WHEN SUM(impressions) > 0
                    THEN SUM(clicks)::decimal
                        / SUM(impressions)
                    ELSE 0
                END AS ctr,

                CASE
                    WHEN SUM(impressions) > 0
                    THEN SUM(
                        position * impressions
                    ) / SUM(impressions)
                    ELSE 0
                END AS position
            ')
            ->groupBy('page')
            ->orderByDesc('clicks')
            ->limit($limit)
            ->get();
    }

    public function opportunities(
        int $siteId,
        Carbon $start,
        Carbon $end,
        int $limit = 20
    ): Collection {
        return $this->query(
            $siteId,
            $start,
            $end,
            'query'
        )
            ->whereNotNull('query')
            ->selectRaw('
                query,

                SUM(clicks) AS clicks,

                SUM(impressions) AS impressions,

                CASE
                    WHEN SUM(impressions) > 0
                    THEN SUM(clicks)::decimal
                        / SUM(impressions)
                    ELSE 0
                END AS ctr,

                CASE
                    WHEN SUM(impressions) > 0
                    THEN SUM(
                        position * impressions
                    ) / SUM(impressions)
                    ELSE 0
                END AS position
            ')
            ->groupBy('query')

            /*
             * SEO opportunity:
             *
             * enough impressions
             * ranking between 4 and 20
             */
            ->havingRaw(
                'SUM(impressions) >= ?',
                [50]
            )
            ->havingRaw('
                (
                    SUM(position * impressions)
                    / NULLIF(
                        SUM(impressions),
                        0
                    )
                )
                BETWEEN 4 AND 20
            ')
            ->orderByDesc('impressions')
            ->limit($limit)
            ->get();
    }
}