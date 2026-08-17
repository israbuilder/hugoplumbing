<?php

namespace App\Integrations\Meta;

use App\Models\MetaAdAccount;
use App\Models\MetaCampaign;

class MetaCampaignSyncService
{
    public function __construct(
        protected MetaApiClient $api
    ) {}

    public function sync(
        MetaAdAccount $account
    ): void {

        $campaigns = $this->api->getAll(
            '/' .
            $account->meta_ad_account_id .
            '/campaigns',

            $account->connection->access_token,

            [
                'fields' => implode(',', [
                    'id',
                    'name',
                    'objective',
                    'status',
                    'effective_status',
                    'buying_type',
                    'special_ad_categories',
                    'daily_budget',
                    'lifetime_budget',
                    'start_time',
                    'stop_time',
                    'created_time',
                    'updated_time',
                ]),

                'limit' => 100,
            ]
        );

        foreach ($campaigns as $campaign) {

            MetaCampaign::updateOrCreate(
                [
                    'meta_campaign_id' =>
                        $campaign['id'],
                ],
                [
                    'meta_ad_account_id' =>
                        $account->id,

                    'name' =>
                        $campaign['name'],

                    'objective' =>
                        $campaign['objective'] ?? null,

                    'status' =>
                        $campaign['status'] ?? null,

                    'effective_status' =>
                        $campaign[
                            'effective_status'
                        ] ?? null,

                    'buying_type' =>
                        $campaign[
                            'buying_type'
                        ] ?? null,

                    'special_ad_category' =>
                        implode(
                            ',',
                            $campaign[
                                'special_ad_categories'
                            ] ?? []
                        ),

                    'daily_budget' =>
                        isset(
                            $campaign['daily_budget']
                        )
                            ? $campaign[
                                'daily_budget'
                            ] / 100
                            : null,

                    'lifetime_budget' =>
                        isset(
                            $campaign[
                                'lifetime_budget'
                            ]
                        )
                            ? $campaign[
                                'lifetime_budget'
                            ] / 100
                            : null,

                    'start_time' =>
                        $campaign[
                            'start_time'
                        ] ?? null,

                    'stop_time' =>
                        $campaign[
                            'stop_time'
                        ] ?? null,

                    'meta_created_time' =>
                        $campaign[
                            'created_time'
                        ] ?? null,

                    'meta_updated_time' =>
                        $campaign[
                            'updated_time'
                        ] ?? null,

                    'raw' => $campaign,
                ]
            );
        }
    }
}