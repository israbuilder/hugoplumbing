<?php

namespace App\Integrations\Google\BusinessProfile;

use App\Models\IntegrationAccount;
use Illuminate\Support\Facades\Http;

class BusinessProfileInformationClient
{
    private const BASE_URL =
        'https://mybusinessbusinessinformation.googleapis.com/v1';

    public function __construct(
        protected BusinessProfileAuthService $auth
    ) {
    }

    public function locations(
        IntegrationAccount $integrationAccount,
        string $accountResourceName
    ): array {
        $results = [];
        $pageToken = null;

        $readMask = implode(
            ',',
            [
                'name',
                'title',
                'storeCode',
                'phoneNumbers',
                'websiteUri',
                'categories',
                'storefrontAddress',
                'latlng',
                'metadata',
            ]
        );

        do {
            $query = [
                'pageSize' => 100,
                'readMask' => $readMask,
            ];

            if ($pageToken) {
                $query['pageToken'] =
                    $pageToken;
            }

            $response =
                Http::withToken(
                    $this->auth
                        ->getValidAccessToken(
                            $integrationAccount
                        )
                )
                    ->acceptJson()
                    ->timeout(60)
                    ->get(
                        sprintf(
                            '%s/%s/locations',
                            self::BASE_URL,
                            $accountResourceName
                        ),
                        $query
                    )
                    ->throw()
                    ->json();

            foreach (
                $response['locations']
                ?? []
                as $location
            ) {
                $results[] =
                    $location;
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