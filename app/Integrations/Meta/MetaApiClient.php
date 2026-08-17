<?php

namespace App\Integrations\Meta;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MetaApiClient
{
    public function baseUrl(): string
    {
        return sprintf(
            'https://graph.facebook.com/%s',
            config('services.meta.graph_version')
        );
    }

    public function http(): PendingRequest
    {
        return Http::acceptJson()
            ->timeout(60)
            ->retry(3, 1000);
    }

    public function get(
        string $endpoint,
        string $accessToken,
        array $params = []
    ): array {

        $params['access_token'] = $accessToken;

        $response = $this->http()->get(
            $this->baseUrl() . '/' . ltrim($endpoint, '/'),
            $params
        );

        if ($response->failed()) {
            throw new RuntimeException(
                'Meta API GET failed: ' .
                $response->body()
            );
        }

        return $response->json();
    }

    public function getAll(
        string $endpoint,
        string $accessToken,
        array $params = []
    ): array {

        $data = [];

        $response = $this->get(
            $endpoint,
            $accessToken,
            $params
        );

        while (true) {

            $data = array_merge(
                $data,
                $response['data'] ?? []
            );

            $next = data_get(
                $response,
                'paging.next'
            );

            if (!$next) {
                break;
            }

            $nextResponse = $this->http()->get($next);

            if ($nextResponse->failed()) {
                throw new RuntimeException(
                    'Meta pagination failed: ' .
                    $nextResponse->body()
                );
            }

            $response = $nextResponse->json();
        }

        return $data;
    }
}