<?php

namespace App\Integrations\Google\Analytics;

use App\Models\AnalyticsProperty;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AnalyticsDataClient
{
    private const BASE_URL =
        'https://analyticsdata.googleapis.com/v1beta';

    private const LIMIT =
        100000;

    public function __construct(
        protected AnalyticsAuthService $auth
    ) {
    }

    protected function http(
        AnalyticsProperty $property
    ): PendingRequest {

        return Http::withToken(
            $this->auth
                ->getValidAccessToken(
                    $property->account
                )
        )
            ->acceptJson()
            ->timeout(120)
            ->retry(
                times: 3,
                sleepMilliseconds: 1000
            );
    }

    public function runReport(
        AnalyticsProperty $property,
        array $payload
    ): array {

        try {

            return $this->http(
                $property
            )
                ->post(
                    sprintf(
                        '%s/properties/%s:runReport',
                        self::BASE_URL,
                        $property->property_id
                    ),
                    $payload
                )
                ->throw()
                ->json();

        } catch (
            RequestException $e
        ) {

            throw new RuntimeException(
                sprintf(
                    'GA4 runReport failed for property %s: %s',
                    $property
                        ->property_id,

                    $e->response
                        ?->body()
                ),
                previous: $e
            );
        }
    }

    public function runPaginatedReport(
        AnalyticsProperty $property,
        array $payload
    ): array {

        $rows = [];

        $offset = 0;

        do {

            $request =
                array_merge(
                    $payload,
                    [
                        'limit' =>
                            (string)
                            self::LIMIT,

                        'offset' =>
                            (string)
                            $offset,
                    ]
                );

            $response =
                $this->runReport(
                    $property,
                    $request
                );

            $batch =
                $response[
                    'rows'
                ]
                ?? [];

            foreach (
                $batch as $row
            ) {
                $rows[] =
                    $row;
            }

            $rowCount =
                (int) (
                    $response[
                        'rowCount'
                    ]
                    ?? count(
                        $batch
                    )
                );

            $offset +=
                count($batch);

        } while (
            !empty($batch)
            && $offset < $rowCount
        );

        return $rows;
    }
}