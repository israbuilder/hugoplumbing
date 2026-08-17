<?php

namespace App\Integrations\Google\Ads;

use App\Models\IntegrationAccount;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleAdsClient
{
    private const BASE_URL =
        'https://googleads.googleapis.com/v25';

    public function __construct(
        protected GoogleAdsAuthService $auth
    ) {
    }

    protected function http(
        IntegrationAccount $account,
        bool $includeLoginCustomer = true
    ): PendingRequest {
        $developerToken = config(
            'services.google_ads.developer_token'
        );

        if (!$developerToken) {
            throw new RuntimeException(
                'GOOGLE_ADS_DEVELOPER_TOKEN is missing.'
            );
        }

        $headers = [
            'developer-token' => $developerToken,
        ];

        $loginCustomer = preg_replace(
            '/\D/',
            '',
            (string) config(
                'services.google_ads.login_customer_id'
            )
        );

        if (
            $includeLoginCustomer &&
            $loginCustomer
        ) {
            $headers['login-customer-id'] =
                $loginCustomer;
        }

        return Http::withToken(
            $this->auth->getValidAccessToken(
                $account
            )
        )
            ->withHeaders($headers)
            ->acceptJson()
            ->timeout(120)
            ->retry(3, 1000);
    }

    public function accessibleCustomers(
        IntegrationAccount $account
    ): array {
        try {
            return $this->http(
                $account,
                false
            )
                ->get(
                    self::BASE_URL
                    . '/customers:listAccessibleCustomers'
                )
                ->throw()
                ->json('resourceNames', []);

        } catch (RequestException $e) {
            throw new RuntimeException(
                'Could not list Google Ads customers: '
                . $e->response?->body(),
                previous: $e
            );
        }
    }

    public function searchStream(
        IntegrationAccount $account,
        string $customerId,
        string $query
    ): array {
        $customerId =
            preg_replace(
                '/\D/',
                '',
                $customerId
            );

        try {
            $response = $this->http($account)
                ->post(
                    self::BASE_URL
                    . "/customers/{$customerId}/googleAds:searchStream",
                    [
                        'query' => $query,
                    ]
                )
                ->throw()
                ->json();

        } catch (RequestException $e) {
            throw new RuntimeException(
                "Google Ads query failed for {$customerId}: "
                . $e->response?->body(),
                previous: $e
            );
        }

        $rows = [];

        foreach ($response ?? [] as $batch) {
            foreach (
                $batch['results'] ?? []
                as $result
            ) {
                $rows[] = $result;
            }
        }

        return $rows;
    }

    public function mutateCampaign(
        IntegrationAccount $account,
        string $customerId,
        array $operations
    ): array {
        return $this->http($account)
            ->post(
                self::BASE_URL
                . "/customers/{$customerId}/campaigns:mutate",
                [
                    'operations' => $operations,
                    'partialFailure' => false,
                ]
            )
            ->throw()
            ->json();
    }

    public function mutateCampaignBudget(
        IntegrationAccount $account,
        string $customerId,
        array $operations
    ): array {
        return $this->http($account)
            ->post(
                self::BASE_URL
                . "/customers/{$customerId}/campaignBudgets:mutate",
                [
                    'operations' => $operations,
                    'partialFailure' => false,
                ]
            )
            ->throw()
            ->json();
    }
}