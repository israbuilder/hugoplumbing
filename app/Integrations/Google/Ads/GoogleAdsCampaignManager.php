<?php

namespace App\Integrations\Google\Ads;

use App\Models\GoogleAdsCampaign;
use InvalidArgumentException;

class GoogleAdsCampaignManager
{
    public function __construct(
        protected GoogleAdsClient $client
    ) {
    }

    public function setStatus(
        GoogleAdsCampaign $campaign,
        string $status
    ): void {
        $status =
            strtoupper($status);

        if (
            !in_array(
                $status,
                [
                    'ENABLED',
                    'PAUSED',
                ],
                true
            )
        ) {
            throw new InvalidArgumentException(
                'Status must be ENABLED or PAUSED.'
            );
        }

        $customer =
            $campaign->customer;

        $this->client->mutateCampaign(
            $customer->integrationAccount,
            $customer->customer_id,
            [
                [
                    'update' => [
                        'resourceName' =>
                            $campaign
                                ->resource_name,

                        'status' =>
                            $status,
                    ],

                    'updateMask' =>
                        'status',
                ],
            ]
        );

        $campaign->update([
            'status' => $status,
        ]);
    }

    public function setBudget(
        GoogleAdsCampaign $campaign,
        float $amount
    ): void {
        if (
            !$campaign
                ->budget_resource_name
        ) {
            throw new InvalidArgumentException(
                'Campaign has no budget resource.'
            );
        }

        if ($amount <= 0) {
            throw new InvalidArgumentException(
                'Budget must be greater than zero.'
            );
        }

        $micros =
            (int) round(
                $amount * 1_000_000
            );

        $customer =
            $campaign->customer;

        $this->client
            ->mutateCampaignBudget(
                $customer
                    ->integrationAccount,

                $customer
                    ->customer_id,

                [
                    [
                        'update' => [
                            'resourceName' =>
                                $campaign
                                    ->budget_resource_name,

                            'amountMicros' =>
                                (string) $micros,
                        ],

                        'updateMask' =>
                            'amount_micros',
                    ],
                ]
            );

        $campaign->update([
            'budget_amount_micros' =>
                $micros,
        ]);
    }
}