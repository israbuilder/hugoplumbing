<?php

namespace App\Integrations\Meta;

use App\Models\MetaAd;
use App\Models\MetaAdSet;

class MetaAdSyncService
{
    public function __construct(
        protected MetaApiClient $api
    ) {}

    public function sync(
        MetaAdSet $adSet
    ): void {

        $campaign =
            $adSet->campaign;

        $account =
            $campaign->adAccount;

        $ads = $this->api->getAll(
            '/' .
            $adSet->meta_ad_set_id .
            '/ads',

            $account->connection->access_token,

            [
                'fields' => implode(',', [
                    'id',
                    'name',
                    'status',
                    'effective_status',
                    'creative{id,name,title,body,object_url,thumbnail_url}',
                    'tracking_specs',
                    'conversion_specs',
                ]),

                'limit' => 100,
            ]
        );

        foreach ($ads as $ad) {

            MetaAd::updateOrCreate(
                [
                    'meta_ad_id' =>
                        $ad['id'],
                ],
                [
                    'meta_ad_set_id' =>
                        $adSet->id,

                    'name' =>
                        $ad['name'],

                    'status' =>
                        $ad['status'] ?? null,

                    'effective_status' =>
                        $ad[
                            'effective_status'
                        ] ?? null,

                    'creative_id' =>
                        data_get(
                            $ad,
                            'creative.id'
                        ),

                    'creative' =>
                        $ad[
                            'creative'
                        ] ?? null,

                    'tracking_specs' =>
                        $ad[
                            'tracking_specs'
                        ] ?? null,

                    'conversion_specs' =>
                        $ad[
                            'conversion_specs'
                        ] ?? null,

                    'raw' => $ad,
                ]
            );
        }
    }
}