<?php

namespace App\Integrations\Google\Analytics;

use App\Models\IntegrationAccount;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AnalyticsAdminClient
{
    private const BASE_URL =
        'https://analyticsadmin.googleapis.com/v1beta';

    public function __construct(
        protected AnalyticsAuthService $auth
    ) {
    }

    protected function http(
        IntegrationAccount $account
    ): PendingRequest {

        return Http::withToken(
            $this->auth
                ->getValidAccessToken(
                    $account
                )
        )
            ->acceptJson()
            ->timeout(60)
            ->retry(
                times: 3,
                sleepMilliseconds: 1000
            );
    }

    public function accountSummaries(
        IntegrationAccount $account
    ): array {

        $summaries = [];

        $pageToken = null;

        do {

            $query = [
                'pageSize' => 200,
            ];

            if ($pageToken) {
                $query['pageToken'] =
                    $pageToken;
            }

            try {

                $response =
                    $this->http(
                        $account
                    )
                        ->get(
                            self::BASE_URL
                            . '/accountSummaries',
                            $query
                        )
                        ->throw()
                        ->json();

            } catch (
                RequestException $e
            ) {

                throw new RuntimeException(
                    'Analytics Admin accountSummaries.list failed: '
                    . $e->response?->body(),
                    previous: $e
                );
            }

            foreach (
                $response[
                    'accountSummaries'
                ] ?? []
                as $summary
            ) {
                $summaries[] =
                    $summary;
            }

            $pageToken =
                $response[
                    'nextPageToken'
                ]
                ?? null;

        } while ($pageToken);

        return $summaries;
    }

    public function property(
        IntegrationAccount $account,
        string $propertyName
    ): array {

        try {

            return $this->http(
                $account
            )
                ->get(
                    self::BASE_URL
                    . '/'
                    . $propertyName
                )
                ->throw()
                ->json();

        } catch (
            RequestException $e
        ) {

            throw new RuntimeException(
                "Could not fetch {$propertyName}: "
                . $e->response?->body(),
                previous: $e
            );
        }
    }
}