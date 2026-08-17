<?php

namespace App\Services\Analytics;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator as LaravelPaginator;
use Illuminate\Support\Facades\DB;

class SeoKeywordAnalyticsService
{
    public function __construct(
        protected SeoAnalyticsService $seoAnalytics
    ) {
    }

    public function paginate(
        int $siteId,
        Carbon $start,
        Carbon $end,
        int $page = 1,
        int $perPage = 25,
        ?string $search = null,
        ?string $sortColumn = null,
        ?string $sortDirection = null,
        ?string $positionGroup = null,
        string $intent = 'all',
        ?string $opportunity = null,
    ): LengthAwarePaginator {

        [
            $previousStart,
            $previousEnd,
        ] = $this->seoAnalytics->previousPeriod(
            $start,
            $end
        );

        $current = $this->periodQuery(
            $siteId,
            $start,
            $end
        );

        $previous = $this->periodQuery(
            $siteId,
            $previousStart,
            $previousEnd
        );

        $pages = $this->landingPagesQuery(
            $siteId,
            $start,
            $end
        );

        $query = DB::query()
            ->fromSub($current, 'current')
            ->leftJoinSub(
                $previous,
                'previous',
                'previous.query',
                '=',
                'current.query'
            )
            ->leftJoinSub(
                $pages,
                'landing',
                'landing.query',
                '=',
                'current.query'
            )
            ->select([
                'current.query',
                'current.clicks',
                'current.impressions',
                'current.ctr',
                'current.position',

                DB::raw(
                    'COALESCE(previous.clicks, 0) AS previous_clicks'
                ),

                DB::raw(
                    'COALESCE(previous.impressions, 0) AS previous_impressions'
                ),

                DB::raw(
                    'COALESCE(previous.ctr, 0) AS previous_ctr'
                ),

                DB::raw(
                    'COALESCE(previous.position, 0) AS previous_position'
                ),

                DB::raw(
                    '
                    CASE
                        WHEN previous.position IS NULL
                        THEN NULL
                        ELSE previous.position - current.position
                    END AS position_change
                    '
                ),

                DB::raw(
                    '
                    CASE
                        WHEN COALESCE(previous.clicks, 0) = 0
                        THEN CASE
                            WHEN current.clicks > 0 THEN 100
                            ELSE 0
                        END
                        ELSE (
                            (current.clicks - previous.clicks)::decimal
                            / ABS(previous.clicks)
                        ) * 100
                    END AS clicks_change
                    '
                ),

                'landing.page AS landing_page',
            ]);

        if ($search) {
            $query->where(
                'current.query',
                'ilike',
                '%' . $search . '%'
            );
        }

        $this->applyPositionFilter(
            $query,
            $positionGroup
        );

        $this->applyIntentFilter(
            $query,
            $intent
        );

        $this->applyOpportunityFilter(
            $query,
            $opportunity
        );

        $allowedSorts = [
            'query',
            'clicks',
            'impressions',
            'ctr',
            'position',
            'clicks_change',
            'position_change',
        ];

        $sortColumn = in_array(
            $sortColumn,
            $allowedSorts,
            true
        )
            ? $sortColumn
            : 'clicks';

        $sortDirection =
            strtolower((string) $sortDirection) === 'asc'
                ? 'asc'
                : 'desc';

        $sortMap = [
            'query' => 'current.query',
            'clicks' => 'current.clicks',
            'impressions' => 'current.impressions',
            'ctr' => 'current.ctr',
            'position' => 'current.position',
        ];

        if (
            in_array(
                $sortColumn,
                ['clicks_change', 'position_change'],
                true
            )
        ) {
            $query->orderBy(
                $sortColumn,
                $sortDirection
            );
        } else {
            $query->orderBy(
                $sortMap[$sortColumn],
                $sortDirection
            );
        }

        $countQuery = clone $query;

        $total = DB::query()
            ->fromSub(
                $countQuery->reorder(),
                'keyword_count'
            )
            ->count();

        $records = $query
            ->forPage(
                $page,
                $perPage
            )
            ->get()
            ->map(
                fn ($row) =>
                    $this->transformRow($row)
            );

        return new LaravelPaginator(
            items: $records,
            total: $total,
            perPage: $perPage,
            currentPage: $page,
            options: [
                'path' => request()->url(),
            ],

        );
    }

    protected function periodQuery(
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
                'query'
            )
            ->whereNotNull('query')
            ->whereBetween(
                'date',
                [
                    $start->toDateString(),
                    $end->toDateString(),
                ]
            )
            ->groupBy('query')
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
                    THEN SUM(position * impressions)
                        / SUM(impressions)
                    ELSE 0
                END AS position
            ');
    }

    protected function landingPagesQuery(
        int $siteId,
        Carbon $start,
        Carbon $end
    ): Builder {
        $ranked = DB::table(
            'search_console_metrics'
        )
            ->where(
                'search_console_site_id',
                $siteId
            )
            ->where(
                'grain',
                'query_page'
            )
            ->whereNotNull('query')
            ->whereNotNull('page')
            ->whereBetween(
                'date',
                [
                    $start->toDateString(),
                    $end->toDateString(),
                ]
            )
            ->groupBy([
                'query',
                'page',
            ])
            ->selectRaw('
                query,
                page,
                SUM(clicks) AS clicks,
                SUM(impressions) AS impressions,

                ROW_NUMBER() OVER (
                    PARTITION BY query
                    ORDER BY
                        SUM(clicks) DESC,
                        SUM(impressions) DESC
                ) AS row_number
            ');

        return DB::query()
            ->fromSub(
                $ranked,
                'ranked_pages'
            )
            ->where(
                'row_number',
                1
            )
            ->select([
                'query',
                'page',
            ]);
    }

    protected function applyPositionFilter(
        Builder $query,
        ?string $positionGroup
    ): void {
        match ($positionGroup) {
            'top_3' =>
                $query->where(
                    'current.position',
                    '<=',
                    3
                ),

            'top_10' =>
                $query->where(
                    'current.position',
                    '<=',
                    10
                ),

            'top_20' =>
                $query->where(
                    'current.position',
                    '<=',
                    20
                ),

            'page_2' =>
                $query
                    ->where(
                        'current.position',
                        '>',
                        10
                    )
                    ->where(
                        'current.position',
                        '<=',
                        20
                    ),

            'outside_20' =>
                $query->where(
                    'current.position',
                    '>',
                    20
                ),

            default => null,
        };
    }

    protected function applyIntentFilter(
        Builder $query,
        string $intent
    ): void {
        $brandTerms = [
            'hugo plumbing',
            'hugo plumber',
            'hugo plumbing houston',
        ];

        if ($intent === 'branded') {

            $query->where(
                function ($query) use ($brandTerms) {

                    foreach ($brandTerms as $term) {
                        $query->orWhere(
                            'current.query',
                            'ilike',
                            '%' . $term . '%'
                        );
                    }
                }
            );
        }

        if ($intent === 'non_branded') {

            $query->where(
                function ($query) use ($brandTerms) {

                    foreach ($brandTerms as $term) {
                        $query->where(
                            'current.query',
                            'not ilike',
                            '%' . $term . '%'
                        );
                    }
                }
            );
        }
    }

    protected function applyOpportunityFilter(
        Builder $query,
        ?string $opportunity
    ): void {
        match ($opportunity) {

            'top_3' =>
                $query
                    ->where(
                        'current.position',
                        '>',
                        3
                    )
                    ->where(
                        'current.position',
                        '<=',
                        6
                    ),

            'page_1' =>
                $query
                    ->where(
                        'current.position',
                        '>',
                        3
                    )
                    ->where(
                        'current.position',
                        '<=',
                        10
                    ),

            'page_2' =>
                $query
                    ->where(
                        'current.position',
                        '>',
                        10
                    )
                    ->where(
                        'current.position',
                        '<=',
                        20
                    ),

            'low_ctr' =>
                $query
                    ->where(
                        'current.impressions',
                        '>=',
                        50
                    )
                    ->where(
                        'current.ctr',
                        '<',
                        0.02
                    ),

            'declining' =>
                $query->whereRaw(
                    '
                    previous.position IS NOT NULL
                    AND current.position
                        - previous.position >= 2
                    '
                ),

            'growing' =>
                $query->whereRaw(
                    '
                    previous.position IS NOT NULL
                    AND previous.position
                        - current.position >= 2
                    '
                ),

            default => null,
        };
    }

    protected function transformRow(
        object $row
    ): array {
        $position =
            (float) $row->position;

        $positionChange =
            $row->position_change !== null
                ? (float) $row->position_change
                : null;

        $ctr =
            (float) $row->ctr;

        return [
            'id' =>
                md5(
                    (string) $row->query
                ),

            'query' =>
                (string) $row->query,

            'clicks' =>
                (int) $row->clicks,

            'previous_clicks' =>
                (int) $row->previous_clicks,

            'clicks_change' =>
                (float) $row->clicks_change,

            'impressions' =>
                (int) $row->impressions,

            'ctr' =>
                $ctr,

            'position' =>
                $position,

            'previous_position' =>
                (float)
                $row->previous_position,

            'position_change' =>
                $positionChange,

            'landing_page' =>
                $row->landing_page,

            'opportunity' =>
                $this->opportunityLabel(
                    $position,
                    $ctr,
                    $positionChange,
                    (int) $row->impressions
                ),
        ];
    }

    protected function opportunityLabel(
        float $position,
        float $ctr,
        ?float $positionChange,
        int $impressions
    ): string {
        if (
            $positionChange !== null
            && $positionChange <= -2
        ) {
            return 'Declining';
        }

        if (
            $positionChange !== null
            && $positionChange >= 2
        ) {
            return 'Growing';
        }

        if (
            $position > 3
            && $position <= 6
        ) {
            return 'Top 3 opportunity';
        }

        if (
            $position > 6
            && $position <= 10
        ) {
            return 'Page 1';
        }

        if (
            $position > 10
            && $position <= 20
        ) {
            return 'Near page 1';
        }

        if (
            $impressions >= 50
            && $ctr < 0.02
        ) {
            return 'Low CTR';
        }

        return 'Monitor';
    }
}
