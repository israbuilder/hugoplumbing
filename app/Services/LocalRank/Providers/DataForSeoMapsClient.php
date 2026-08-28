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
                    'DataForSEO task_get HTTP failed: ' .
                    $response->body()
                );
            }

            $json = $response->json();

            $statusCode = (int) ($json['status_code'] ?? 0);

            if ($statusCode !== 20000) {
                throw new RuntimeException(
                    'DataForSEO task_get API error ' .
                    $statusCode . ': ' .
                    ($json['status_message'] ?? 'Unknown error')
                );
            }

            $task = $json['tasks'][0] ?? null;

            if (!$task) {
                return $json;
            }

            $taskStatus = (int) ($task['status_code'] ?? 0);

            /*
            * 40102 = DataForSEO completed the request,
            * but Google returned no search results.
            *
            * This is NOT a system failure for our GeoGrid.
            */
            if ($taskStatus === 40102) {
                $json['_local_rank_no_results'] = true;

                return $json;
            }

            /*
            * Other 40000+ statuses are actual errors.
            */
            if ($taskStatus >= 40000) {
                throw new RuntimeException(
                    'DataForSEO task error ' .
                    $taskStatus . ': ' .
                    ($task['status_message'] ?? 'Unknown task error')
                );
            }

            return $json;
        }
}