<?php

namespace App\Integrations\Meta;

use App\Models\MetaAdSet;
use App\Models\MetaCampaign;

class MetaAdSetSyncService
{
    public function __construct(
        protected MetaApiClient $api
    ) {}

    public function sync(
        MetaCampaign $campaign
    ): void {

        $account =
            $campaign->adAccount;

        $sets = $this->api->getAll(
            '/' .
            $campaign->meta_campaign_id .
            '/adsets',

            $account->connection->access_token,

            [
                'fields' => implode(',', [
                    'id',
                    'name',
                    'status',
                    'effective_status',
                    'optimization_goal',
                    'billing_event',
                    'bid_amount',
                    'daily_budget',
                    'lifetime_budget',
                    'start_time',
                    'end_time',
                    'targeting',
                    'promoted_object',
                ]),

                'limit' => 100,
            ]
        );

        foreach ($sets as $set) {

            MetaAdSet::updateOrCreate(
                [
                    'meta_ad_set_id' =>
                        $set['id'],
                ],
                [
                    'meta_campaign_id' =>
                        $campaign->id,

                    'name' =>
                        $set['name'],

                    'status' =>
                        $set['status'] ?? null,

                    'effective_status' =>
                        $set[
                            'effective_status'
                        ] ?? null,

                    'optimization_goal' =>
                        $set[
                            'optimization_goal'
                        ] ?? null,

                    'billing_event' =>
                        $set[
                            'billing_event'
                        ] ?? null,

                    'bid_amount' =>
                        isset($set['bid_amount'])
                            ? $set['bid_amount'] / 100
                            : null,

                    'daily_budget' =>
                        isset($set['daily_budget'])
                            ? $set['daily_budget'] / 100
                            : null,

                    'lifetime_budget' =>
                        isset(
                            $set[
                                'lifetime_budget'
                            ]
                        )
                            ? $set[
                                'lifetime_budget'
                            ] / 100
                            : null,

                    'start_time' =>
                        $set['start_time'] ?? null,

                    'end_time' =>
                        $set['end_time'] ?? null,

                    'targeting' =>
                        $set['targeting'] ?? null,

                    'promoted_object' =>
                        $set[
                            'promoted_object'
                        ] ?? null,

                    'raw' => $set,
                ]
            );
        }
    }
}