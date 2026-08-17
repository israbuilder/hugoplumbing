<?php

namespace App\Integrations\Google\Ads;

use App\Models\GoogleAdsCustomer;
use App\Models\IntegrationAccount;
use Illuminate\Support\Collection;

class GoogleAdsService
{
    public function __construct(
        protected GoogleAdsClient $client
    ) {
    }

    public function syncCustomers(
        IntegrationAccount $account
    ): Collection {
        $configuredCustomer = preg_replace(
            '/\D/',
            '',
            (string) config(
                'services.google_ads.customer_id'
            )
        );

        $loginCustomer = preg_replace(
            '/\D/',
            '',
            (string) config(
                'services.google_ads.login_customer_id'
            )
        );

        $customerIds = [];

        if ($configuredCustomer) {
            $customerIds[] = $configuredCustomer;
        }

        if ($loginCustomer) {

            $rows = $this->client->searchStream(
                $account,
                $loginCustomer,
                <<<'GAQL'
SELECT
    customer_client.id,
    customer_client.descriptive_name,
    customer_client.currency_code,
    customer_client.time_zone,
    customer_client.manager,
    customer_client.level
FROM customer_client
WHERE customer_client.level <= 1
GAQL
            );

            foreach ($rows as $row) {

                $client =
                    $row['customerClient']
                    ?? [];

                $id =
                    (string) (
                        $client['id']
                        ?? ''
                    );

                if (!$id) {
                    continue;
                }

                $customerIds[] = $id;

                GoogleAdsCustomer::updateOrCreate(
                    [
                        'integration_account_id' =>
                            $account->id,

                        'customer_id' => $id,
                    ],
                    [
                        'descriptive_name' =>
                            $client[
                                'descriptiveName'
                            ]
                            ?? null,

                        'currency_code' =>
                            $client[
                                'currencyCode'
                            ]
                            ?? null,

                        'time_zone' =>
                            $client[
                                'timeZone'
                            ]
                            ?? null,

                        'is_manager' =>
                            (bool) (
                                $client['manager']
                                ?? false
                            ),

                        'is_active' => true,

                        'is_primary' =>
                            $id ===
                            $configuredCustomer,

                        'metadata' =>
                            $client,
                    ]
                );
            }

        } else {

            foreach (
                $this->client
                    ->accessibleCustomers(
                        $account
                    )
                as $resource
            ) {
                $customerIds[] =
                    str_replace(
                        'customers/',
                        '',
                        $resource
                    );
            }
        }

        $customerIds =
            array_values(
                array_unique(
                    array_filter(
                        $customerIds
                    )
                )
            );

        foreach ($customerIds as $id) {

            if (
                GoogleAdsCustomer::query()
                    ->where(
                        'integration_account_id',
                        $account->id
                    )
                    ->where(
                        'customer_id',
                        $id
                    )
                    ->exists()
            ) {
                continue;
            }

            $rows = $this->client->searchStream(
                $account,
                $id,
                <<<'GAQL'
SELECT
    customer.id,
    customer.descriptive_name,
    customer.currency_code,
    customer.time_zone,
    customer.manager,
    customer.test_account
FROM customer
LIMIT 1
GAQL
            );

            $remote =
                $rows[0]['customer']
                ?? [];

            GoogleAdsCustomer::updateOrCreate(
                [
                    'integration_account_id' =>
                        $account->id,

                    'customer_id' => $id,
                ],
                [
                    'descriptive_name' =>
                        $remote[
                            'descriptiveName'
                        ]
                        ?? null,

                    'currency_code' =>
                        $remote[
                            'currencyCode'
                        ]
                        ?? null,

                    'time_zone' =>
                        $remote[
                            'timeZone'
                        ]
                        ?? null,

                    'is_manager' =>
                        (bool) (
                            $remote[
                                'manager'
                            ]
                            ?? false
                        ),

                    'is_test_account' =>
                        (bool) (
                            $remote[
                                'testAccount'
                            ]
                            ?? false
                        ),

                    'is_primary' =>
                        $id ===
                        $configuredCustomer,

                    'is_active' =>
                        true,

                    'metadata' =>
                        $remote,
                ]
            );
        }

        return GoogleAdsCustomer::query()
            ->where(
                'integration_account_id',
                $account->id
            )
            ->whereIn(
                'customer_id',
                $customerIds
            )
            ->get();
    }
}