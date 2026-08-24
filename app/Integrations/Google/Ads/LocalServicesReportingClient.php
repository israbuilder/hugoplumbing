<?php

namespace App\Integrations\Google\Ads;

use App\Models\IntegrationAccount;
use Carbon\Carbon;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

        $customerId = preg_replace(
            '/\D/',
            '',
            $customerId
        );

        try {

            $response = Http::withToken(
                $this->auth->getValidAccessToken(
                    $account
                )
            )
                ->acceptJson()
                ->timeout(90)
                ->retry(
                    3,
                    1000,
                    throw: false
                )
                ->get(
                    self::URL,
                    [
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

                        'pageSize' =>
                            1000,
                    ]
                );

            if ($response->failed()) {

                Log::warning(
                    'Local Services account report failed',
                    [
                        'manager_customer_id' =>
                            $managerId,

                        'customer_id' =>
                            $customerId,

                        'from' =>
                            $from->toDateString(),

                        'to' =>
                            $to->toDateString(),

                        'status' =>
                            $response->status(),

                        'body' =>
                            $response->json()
                            ?? $response->body(),
                    ]
                );

                return null;
            }

            $payload =
                $response->json();

            $reports =
                $payload['accountReports']
                ?? $payload['account_reports']
                ?? [];

            return collect(
                $reports
            )->first(
                function ($row) use (
                    $customerId
                ) {
                    $accountId =
                        (string) (
                            $row['accountId']
                            ?? $row['account_id']
                            ?? ''
                        );

                    return preg_replace(
                        '/\D/',
                        '',
                        $accountId
                    ) === $customerId;
                }
            );

        } catch (RequestException $e) {

            Log::warning(
                'Local Services request exception',
                [
                    'manager_customer_id' =>
                        $managerId,

                    'customer_id' =>
                        $customerId,

                    'from' =>
                        $from->toDateString(),

                    'to' =>
                        $to->toDateString(),

                    'error' =>
                        $e->getMessage(),
                ]
            );

            return null;

        } catch (\Throwable $e) {

            Log::warning(
                'Local Services unexpected error',
                [
                    'manager_customer_id' =>
                        $managerId,

                    'customer_id' =>
                        $customerId,

                    'from' =>
                        $from->toDateString(),

                    'to' =>
                        $to->toDateString(),

                    'error' =>
                        $e->getMessage(),
                ]
            );

            return null;
        }
    }
}