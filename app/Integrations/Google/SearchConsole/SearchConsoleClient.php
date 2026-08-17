<?php

namespace App\Integrations\Google\SearchConsole;

use App\Models\IntegrationAccount;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SearchConsoleClient
{
    private const BASE_URL =
        'https://www.googleapis.com/webmasters/v3';

    public function __construct(
        protected SearchConsoleAuthService $auth
    ) {
    }

    protected function http(
        IntegrationAccount $account
    ): PendingRequest {
        $accessToken =
            $this->auth->getValidAccessToken($account);

        return Http::withToken($accessToken)
            ->acceptJson()
            ->timeout(60)
            ->retry(
                times: 3,
                sleepMilliseconds: 1000
            );
    }

    /**
     * Return all Search Console properties
     * accessible to this Google account.
     */
    public function sites(
        IntegrationAccount $account
    ): array {
        try {
            $response = $this->http($account)
                ->get(self::BASE_URL . '/sites')
                ->throw();

            return $response->json(
                'siteEntry',
                []
            );

        } catch (RequestException $e) {
            throw new RuntimeException(
                'Search Console sites.list failed: ' .
                $e->response?->body(),
                previous: $e
            );
        }
    }

    public function query(
        IntegrationAccount $account,
        string $siteUrl,
        array $payload
    ): array {
        $url = sprintf(
            '%s/sites/%s/searchAnalytics/query',
            self::BASE_URL,
            rawurlencode($siteUrl)
        );

        try {
            return $this->http($account)
                ->post($url, $payload)
                ->throw()
                ->json();

        } catch (RequestException $e) {
            throw new RuntimeException(
                sprintf(
                    'Search Console Search Analytics query failed for %s: %s',
                    $siteUrl,
                    $e->response?->body()
                ),
                previous: $e
            );
        }
    }
}