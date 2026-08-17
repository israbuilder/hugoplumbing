<?php

namespace App\Integrations\Google\BusinessProfile;

use App\Models\BusinessProfileLocation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class BusinessProfilePerformanceClient
{
    private const BASE_URL =
        'https://businessprofileperformance.googleapis.com/v1';

    public const DAILY_METRICS = [
        'BUSINESS_IMPRESSIONS_DESKTOP_MAPS',
        'BUSINESS_IMPRESSIONS_DESKTOP_SEARCH',
        'BUSINESS_IMPRESSIONS_MOBILE_MAPS',
        'BUSINESS_IMPRESSIONS_MOBILE_SEARCH',
        'BUSINESS_CONVERSATIONS',
        'BUSINESS_DIRECTION_REQUESTS',
        'CALL_CLICKS',
        'WEBSITE_CLICKS',
        'BUSINESS_BOOKINGS',
    ];

    public function __construct(
        protected BusinessProfileAuthService $auth
    ) {
    }

    public function dailyMetrics(
        BusinessProfileLocation $location,
        Carbon $from,
        Carbon $to
    ): array {
        $account =
            $location
                ->businessProfileAccount
                ->integrationAccount;

        $query = [];

        foreach (
            self::DAILY_METRICS
            as $metric
        ) {
            $query[] =
                'dailyMetrics='
                . urlencode(
                    $metric
                );
        }

        $query[] =
            'dailyRange.start_date.year='
            . $from->year;

        $query[] =
            'dailyRange.start_date.month='
            . $from->month;

        $query[] =
            'dailyRange.start_date.day='
            . $from->day;

        $query[] =
            'dailyRange.end_date.year='
            . $to->year;

        $query[] =
            'dailyRange.end_date.month='
            . $to->month;

        $query[] =
            'dailyRange.end_date.day='
            . $to->day;

        $url =
            sprintf(
                '%s/locations/%s:fetchMultiDailyMetricsTimeSeries?%s',
                self::BASE_URL,
                $location->location_id,
                implode('&', $query)
            );

        return Http::withToken(
            $this->auth
                ->getValidAccessToken(
                    $account
                )
        )
            ->acceptJson()
            ->timeout(90)
            ->get($url)
            ->throw()
            ->json();
    }

    public function searchKeywords(
        BusinessProfileLocation $location,
        Carbon $from,
        Carbon $to
    ): array {
        $account =
            $location
                ->businessProfileAccount
                ->integrationAccount;

        $results = [];
        $pageToken = null;

        do {
            $query = [
                'monthlyRange.start_month.year'
                    => $from->year,

                'monthlyRange.start_month.month'
                    => $from->month,

                'monthlyRange.end_month.year'
                    => $to->year,

                'monthlyRange.end_month.month'
                    => $to->month,

                'pageSize' => 100,
            ];

            if ($pageToken) {
                $query[
                    'pageToken'
                ] = $pageToken;
            }

            $response =
                Http::withToken(
                    $this->auth
                        ->getValidAccessToken(
                            $account
                        )
                )
                    ->acceptJson()
                    ->timeout(90)
                    ->get(
                        sprintf(
                            '%s/locations/%s/searchkeywords/impressions/monthly',
                            self::BASE_URL,
                            $location
                                ->location_id
                        ),
                        $query
                    )
                    ->throw()
                    ->json();

            foreach (
                $response[
                    'searchKeywordsCounts'
                ]
                ?? []
                as $row
            ) {
                $results[] =
                    $row;
            }

            $pageToken =
                $response[
                    'nextPageToken'
                ]
                ?? null;

        } while ($pageToken);

        return $results;
    }
}