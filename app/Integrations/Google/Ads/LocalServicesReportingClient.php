<?php

namespace App\Integrations\Google\Ads;

use App\Models\IntegrationAccount;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class LocalServicesReportingClient
{
    private const URL =
        'https://localservices.googleapis.com/v1/accountReports:search';

    public function __construct(
        protected GoogleAdsAuthService $auth
    ) {
    }

    public function accountReport(
        IntegrationAccount $account,
        string $customerId,
        Carbon $from,
        Carbon $to
    ): ?array {
        $managerId = preg_replace(
            '/\D/',
            '',
            (string) config(
                'services.google_ads.login_customer_id'
            )
        );

        if (!$managerId) {
            return null;
        }

        $customerId =
            preg_replace(
                '/\D/',
                '',
                $customerId
            );

        $response = Http::withToken(
            $this->auth
                ->getValidAccessToken(
                    $account
                )
        )
            ->acceptJson()
            ->timeout(90)
            ->get(self::URL, [
                'query' =>
                    "manager_customer_id:{$managerId};customer_id:{$customerId}",

                'startDate.year' =>
                    $from->year,

                'startDate.month' =>
                    $from->month,

                'startDate.day' =>
                    $from->day,

                'endDate.year' =>
                    $to->year,

                'endDate.month' =>
                    $to->month,

                'endDate.day' =>
                    $to->day,

                'pageSize' => 1000,
            ])
            ->throw()
            ->json();

        $reports =
            $response['accountReports']
            ?? $response['account_reports']
            ?? [];

        return collect($reports)
            ->first(
                fn ($row) =>
                    (string) (
                        $row['accountId']
                        ?? $row['account_id']
                        ?? ''
                    )
                    === $customerId
            );
    }
}