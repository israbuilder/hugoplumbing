<?php

namespace App\Services\LocalRank\Providers;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class DataForSeoMapsClient
{
    protected PendingRequest $http;

    public function __construct()
    {
        $login = config('services.dataforseo.login');
        $password = config('services.dataforseo.password');

        if (!$login || !$password) {
            throw new RuntimeException(
                'DataForSEO credentials are not configured.'
            );
        }

        $this->http = Http::baseUrl(
            rtrim(
                config(
                    'services.dataforseo.base_url',
                    'https://api.dataforseo.com'
                ),
                '/'
            )
        )
            ->withBasicAuth($login, $password)
            ->acceptJson()
            ->asJson()
            ->timeout(60)
            ->retry(3, 1000);
    }

    /**
     * Post up to 100 GeoGrid points in one HTTP request.
     */
    public function postTasks(array $tasks): array
    {
        if (empty($tasks)) {
            return [];
        }

        $response = $this->http->post(
            '/v3/serp/google/maps/task_post',
            $tasks
        );

        if ($response->failed()) {
            throw new RuntimeException(
                'DataForSEO task_post failed: ' .
                $response->body()
            );
        }

        $json = $response->json();

        if (($json['status_code'] ?? 0) !== 20000) {
            throw new RuntimeException(
                'DataForSEO error: ' .
                ($json['status_message'] ?? 'Unknown error')
            );
        }

        return $json;
    }

    public function getTask(string $taskId): array
    {
        $response = $this->http->get(
            "/v3/serp/google/maps/task_get/advanced/{$taskId}"
        );

        if ($response->failed()) {
            throw new RuntimeException(
                'DataForSEO task_get failed: ' .
                $response->body()
            );
        }

        return $response->json();
    }
}