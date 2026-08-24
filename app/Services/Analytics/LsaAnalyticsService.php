<?php

namespace App\Services\Analytics;

use App\Models\GoogleAdsCampaign;
use App\Models\GoogleAdsCampaignDailyMetric;
use App\Models\GoogleAdsCustomer;
use App\Models\GoogleAdsLsaConversation;
use App\Models\GoogleAdsLsaDailyMetric;
use App\Models\GoogleAdsLsaLead;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LsaAnalyticsService
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
                : now();

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
            $end->endOfDay(),
        ];
    }

    public function previousPeriod(
        Carbon $start,
        Carbon $end
    ): array {
        $days =
            $start
                ->copy()
                ->startOfDay()
                ->diffInDays(
                    $end
                        ->copy()
                        ->startOfDay()
                )
            + 1;

        $previousEnd =
            $start
                ->copy()
                ->subDay()
                ->endOfDay();

        $previousStart =
            $previousEnd
                ->copy()
                ->subDays(
                    $days - 1
                )
                ->startOfDay();

        return [
            $previousStart,
            $previousEnd,
        ];
    }

    public function defaultCustomerId(): ?int
    {
        return GoogleAdsCustomer::query()
            ->where(
                'is_active',
                true
            )
            ->where(
                'is_manager',
                false
            )
            ->orderByDesc(
                'is_primary'
            )
            ->value('id');
    }

    public function defaultCampaignId(
        ?int $customerId = null
    ): ?int {
        $query =
            GoogleAdsCampaign::query()
                ->where(
                    'is_local_services',
                    true
                )
                ->where(
                    'is_active',
                    true
                );

        if ($customerId) {
            $query->where(
                'google_ads_customer_id',
                $customerId
            );
        }

        return $query->value('id');
    }

    public function summary(
        int $customerId,
        Carbon $start,
        Carbon $end,
        ?int $campaignId = null
    ): array {
        /*
         * Official LSA account report metrics.
         */
        $lsa =
            GoogleAdsLsaDailyMetric::query()
                ->where(
                    'google_ads_customer_id',
                    $customerId
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
                    COALESCE(
                        SUM(total_cost),
                        0
                    ) AS spend,

                    COALESCE(
                        SUM(charged_leads),
                        0
                    ) AS charged_leads,

                    COALESCE(
                        SUM(phone_calls),
                        0
                    ) AS phone_calls,

                    COALESCE(
                        SUM(connected_phone_calls),
                        0
                    ) AS connected_phone_calls,

                    AVG(
                        NULLIF(
                            phone_lead_responsiveness,
                            0
                        )
                    ) AS phone_lead_responsiveness,

                    AVG(
                        NULLIF(
                            rating,
                            0
                        )
                    ) AS rating,

                    MAX(
                        review_count
                    ) AS review_count,

                    MAX(
                        average_weekly_budget
                    ) AS weekly_budget
                ')
                ->first();

        /*
         * Actual LSA lead records.
         */
        $leadQuery =
            GoogleAdsLsaLead::query()
                ->where(
                    'google_ads_customer_id',
                    $customerId
                )
                ->whereBetween(
                    'lead_created_at',
                    [
                        $start,
                        $end,
                    ]
                );

        $totalLeads =
            (clone $leadQuery)
                ->count();

        $bookedLeads =
            (clone $leadQuery)
                ->where(
                    'lead_status',
                    'BOOKED'
                )
                ->count();

        $chargedLeadRows =
            (clone $leadQuery)
                ->where(
                    'lead_charged',
                    true
                )
                ->count();

        $creditedLeads =
            (clone $leadQuery)
                ->whereNotNull(
                    'credit_state'
                )
                ->whereNotIn(
                    'credit_state',
                    [
                        'UNSPECIFIED',
                        'UNKNOWN',
                        'PENDING',
                    ]
                )
                ->count();

        /*
         * Conversations: real phone call records.
         */
        $callQuery =
            GoogleAdsLsaConversation::query()
                ->where(
                    'channel',
                    'PHONE_CALL'
                )
                ->whereBetween(
                    'event_at',
                    [
                        $start,
                        $end,
                    ]
                )
                ->whereHas(
                    'lead',
                    fn (Builder $query) =>
                        $query->where(
                            'google_ads_customer_id',
                            $customerId
                        )
                );

        $conversationCalls =
            (clone $callQuery)
                ->count();

        $callsOver30Seconds =
            (clone $callQuery)
                ->where(
                    'call_duration_millis',
                    '>=',
                    30000
                )
                ->count();

        $averageCallMillis =
            (float) (
                (clone $callQuery)
                    ->whereNotNull(
                        'call_duration_millis'
                    )
                    ->avg(
                        'call_duration_millis'
                    )
                ?? 0
            );

        $totalCallMillis =
            (int) (
                (clone $callQuery)
                    ->sum(
                        'call_duration_millis'
                    )
            );

        /*
         * General Google Ads campaign metrics.
         */
        $campaignQuery =
            GoogleAdsCampaignDailyMetric::query()
                ->whereBetween(
                    'date',
                    [
                        $start
                            ->toDateString(),

                        $end
                            ->toDateString(),
                    ]
                )
                ->whereHas(
                    'campaign',
                    function (
                        Builder $query
                    ) use (
                        $customerId,
                        $campaignId
                    ) {
                        $query
                            ->where(
                                'google_ads_customer_id',
                                $customerId
                            )
                            ->where(
                                'is_local_services',
                                true
                            );

                        if ($campaignId) {
                            $query->where(
                                'id',
                                $campaignId
                            );
                        }
                    }
                );

        $campaign =
            $campaignQuery
                ->selectRaw('
                    COALESCE(
                        SUM(impressions),
                        0
                    ) AS impressions,

                    COALESCE(
                        SUM(clicks),
                        0
                    ) AS clicks,

                    COALESCE(
                        SUM(cost_micros),
                        0
                    ) AS cost_micros,

                    COALESCE(
                        SUM(conversions),
                        0
                    ) AS conversions,

                    COALESCE(
                        SUM(all_conversions),
                        0
                    ) AS all_conversions,

                    COALESCE(
                        SUM(conversion_value),
                        0
                    ) AS conversion_value
                ')
                ->first();

        $spend =
            (float) (
                $lsa->spend
                ?? 0
            );

        $chargedLeads =
            (int) (
                $lsa->charged_leads
                ?? 0
            );

        $phoneCalls =
            (int) (
                $lsa->phone_calls
                ?? 0
            );

        $connectedCalls =
            (int) (
                $lsa->connected_phone_calls
                ?? 0
            );

        return [
            'spend' =>
                $spend,

            'charged_leads' =>
                $chargedLeads,

            'cpl' =>
                $chargedLeads > 0
                    ? $spend
                        / $chargedLeads
                    : 0,

            'phone_calls' =>
                $phoneCalls,

            'connected_calls' =>
                $connectedCalls,

            'connect_rate' =>
                $phoneCalls > 0
                    ? $connectedCalls
                        / $phoneCalls
                    : 0,

            'cost_per_connected_call' =>
                $connectedCalls > 0
                    ? $spend
                        / $connectedCalls
                    : 0,

            'phone_lead_responsiveness' =>
                (float) (
                    $lsa
                        ->phone_lead_responsiveness
                    ?? 0
                ),

            'rating' =>
                (float) (
                    $lsa->rating
                    ?? 0
                ),

            'review_count' =>
                (int) (
                    $lsa->review_count
                    ?? 0
                ),

            'weekly_budget' =>
                (float) (
                    $lsa->weekly_budget
                    ?? 0
                ),

            'total_leads' =>
                $totalLeads,

            'charged_lead_rows' =>
                $chargedLeadRows,

            'booked_leads' =>
                $bookedLeads,

            'book_rate' =>
                $totalLeads > 0
                    ? $bookedLeads
                        / $totalLeads
                    : 0,

            'credited_leads' =>
                $creditedLeads,

            'conversation_calls' =>
                $conversationCalls,

            'calls_over_30_seconds' =>
                $callsOver30Seconds,

            'average_call_duration_ms' =>
                $averageCallMillis,

            'total_call_duration_ms' =>
                $totalCallMillis,

            'impressions' =>
                (int) (
                    $campaign
                        ->impressions
                    ?? 0
                ),

            'clicks' =>
                (int) (
                    $campaign
                        ->clicks
                    ?? 0
                ),

            'ads_cost' =>
                (
                    (int) (
                        $campaign
                            ->cost_micros
                        ?? 0
                    )
                )
                / 1_000_000,

            'conversions' =>
                (float) (
                    $campaign
                        ->conversions
                    ?? 0
                ),

            'all_conversions' =>
                (float) (
                    $campaign
                        ->all_conversions
                    ?? 0
                ),

            'conversion_value' =>
                (float) (
                    $campaign
                        ->conversion_value
                    ?? 0
                ),
        ];
    }

    public function comparison(
        int $customerId,
        Carbon $start,
        Carbon $end,
        ?int $campaignId = null
    ): array {
        $current =
            $this->summary(
                $customerId,
                $start,
                $end,
                $campaignId
            );

        [
            $previousStart,
            $previousEnd,
        ] =
            $this->previousPeriod(
                $start,
                $end
            );

        $previous =
            $this->summary(
                $customerId,
                $previousStart,
                $previousEnd,
                $campaignId
            );

        $fields = [
            'spend',
            'charged_leads',
            'cpl',
            'phone_calls',
            'connected_calls',
            'connect_rate',
            'booked_leads',
            'book_rate',
            'impressions',
            'clicks',
            'conversions',
        ];

        $changes = [];

        foreach ($fields as $field) {
            $changes[$field] =
                $this->percentageChange(
                    $current[$field],
                    $previous[$field]
                );
        }

        return [
            'current' =>
                $current,

            'previous' =>
                $previous,

            'changes' =>
                $changes,

            'previous_dates' => [
                'start' =>
                    $previousStart,

                'end' =>
                    $previousEnd,
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
                - $previous
            )
            / abs($previous)
        ) * 100;
    }

    public function performanceTimeline(
        int $customerId,
        Carbon $start,
        Carbon $end
    ): Collection {
        return GoogleAdsLsaDailyMetric::query()
            ->where(
                'google_ads_customer_id',
                $customerId
            )
            ->whereBetween(
                'date',
                [
                    $start->toDateString(),
                    $end->toDateString(),
                ]
            )
            ->selectRaw('
                date,

                SUM(total_cost)
                    AS spend,

                SUM(charged_leads)
                    AS charged_leads,

                SUM(phone_calls)
                    AS phone_calls,

                SUM(connected_phone_calls)
                    AS connected_phone_calls
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    public function campaignTimeline(
        int $customerId,
        Carbon $start,
        Carbon $end,
        ?int $campaignId = null
    ): Collection {
        $query =
            GoogleAdsCampaignDailyMetric::query()
                ->whereBetween(
                    'date',
                    [
                        $start->toDateString(),
                        $end->toDateString(),
                    ]
                )
                ->whereHas(
                    'campaign',
                    function (
                        Builder $query
                    ) use (
                        $customerId,
                        $campaignId
                    ) {
                        $query
                            ->where(
                                'google_ads_customer_id',
                                $customerId
                            )
                            ->where(
                                'is_local_services',
                                true
                            );

                        if ($campaignId) {
                            $query->where(
                                'id',
                                $campaignId
                            );
                        }
                    }
                );

        return $query
            ->selectRaw('
                date,

                SUM(impressions)
                    AS impressions,

                SUM(clicks)
                    AS clicks,

                SUM(cost_micros)
                    AS cost_micros,

                SUM(conversions)
                    AS conversions
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    public function leadStatuses(
        int $customerId,
        Carbon $start,
        Carbon $end
    ): Collection {
        return GoogleAdsLsaLead::query()
            ->where(
                'google_ads_customer_id',
                $customerId
            )
            ->whereBetween(
                'lead_created_at',
                [
                    $start,
                    $end,
                ]
            )
            ->selectRaw("
                COALESCE(
                    lead_status,
                    'UNKNOWN'
                ) AS status,

                COUNT(*) AS total,

                SUM(
                    CASE
                        WHEN lead_charged = true
                        THEN 1
                        ELSE 0
                    END
                ) AS charged
            ")
            ->groupBy(
                'lead_status'
            )
            ->orderByDesc(
                'total'
            )
            ->get();
    }

    public function recentLeads(
        int $customerId,
        Carbon $start,
        Carbon $end,
        int $limit = 30
    ): Collection {
        return GoogleAdsLsaLead::query()
            ->where(
                'google_ads_customer_id',
                $customerId
            )
            ->whereBetween(
                'lead_created_at',
                [
                    $start,
                    $end,
                ]
            )
            ->withCount(
                'conversations'
            )
            ->with([
                'conversations' =>
                    fn ($query) =>
                        $query
                            ->orderBy(
                                'event_at'
                            ),
            ])
            ->orderByDesc(
                'lead_created_at'
            )
            ->limit(
                $limit
            )
            ->get();
    }

    public function calls(
        int $customerId,
        Carbon $start,
        Carbon $end,
        int $limit = 50
    ): Collection {
        return GoogleAdsLsaConversation::query()
            ->where(
                'channel',
                'PHONE_CALL'
            )
            ->whereBetween(
                'event_at',
                [
                    $start,
                    $end,
                ]
            )
            ->whereHas(
                'lead',
                fn (Builder $query) =>
                    $query->where(
                        'google_ads_customer_id',
                        $customerId
                    )
            )
            ->with([
                'lead:id,google_ads_customer_id,lead_id,lead_type,lead_status,service_id,lead_charged,credit_state,lead_created_at',
            ])
            ->orderByDesc(
                'event_at'
            )
            ->limit(
                $limit
            )
            ->get();
    }

    public function leadTypeBreakdown(
        int $customerId,
        Carbon $start,
        Carbon $end
    ): Collection {
        return GoogleAdsLsaLead::query()
            ->where(
                'google_ads_customer_id',
                $customerId
            )
            ->whereBetween(
                'lead_created_at',
                [
                    $start,
                    $end,
                ]
            )
            ->selectRaw("
                COALESCE(
                    lead_type,
                    'UNKNOWN'
                ) AS lead_type,

                COUNT(*) AS total
            ")
            ->groupBy(
                'lead_type'
            )
            ->orderByDesc(
                'total'
            )
            ->get();
    }

    public function formatDuration(
        float|int|null $milliseconds
    ): string {
        if (!$milliseconds) {
            return '0s';
        }

        $seconds =
            (int) round(
                $milliseconds
                / 1000
            );

        $minutes =
            intdiv(
                $seconds,
                60
            );

        $remaining =
            $seconds % 60;

        if ($minutes === 0) {
            return $remaining . 's';
        }

        return sprintf(
            '%dm %02ds',
            $minutes,
            $remaining
        );
    }
}