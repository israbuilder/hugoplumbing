<?php

namespace App\Integrations\Google\BusinessProfile;

use App\Models\IntegrationAccount;
use Illuminate\Support\Facades\Http;

class BusinessProfileAccountClient
{
    private const BASE_URL =
        'https://mybusinessaccountmanagement.googleapis.com/v1';

    public function __construct(
        protected BusinessProfileAuthService $auth
    ) {
    }

    public function accounts(
        IntegrationAccount $account
    ): array {
        $results = [];
        $pageToken = null;

        do {
            $query = [
                'pageSize' => 20,
            ];

            if ($pageToken) {
                $query['pageToken'] =
                    $pageToken;
            }

            $response =
                Http::withToken(
                    $this->auth
                        ->getValidAccessToken(
                            $account
                        )
                )
                    ->acceptJson()
                    ->timeout(60)
                    ->get(
                        self::BASE_URL
                        . '/accounts',
                        $query
                    )
                    ->throw()
                    ->json();

            foreach (
                $response['accounts']
                ?? []
                as $row
            ) {
                $results[] = $row;
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