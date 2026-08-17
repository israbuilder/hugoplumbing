<?php

namespace App\Integrations\Meta;

use App\Models\MetaInstagramAccount;
use App\Models\MetaInstagramMedia;

class MetaInstagramMediaSyncService
{
    public function __construct(
        protected MetaApiClient $api
    ) {}

    public function sync(
        MetaInstagramAccount $account
    ): void {

        $page =
            $account->page;

        $media = $this->api->getAll(
            '/' .
            $account->meta_instagram_id .
            '/media',

            $page->page_access_token,

            [
                'fields' => implode(',', [
                    'id',
                    'caption',
                    'media_type',
                    'media_product_type',
                    'media_url',
                    'thumbnail_url',
                    'permalink',
                    'timestamp',
                ]),

                'limit' => 100,
            ]
        );

        foreach ($media as $item) {

            MetaInstagramMedia::updateOrCreate(
                [
                    'meta_media_id' =>
                        $item['id'],
                ],
                [
                    'meta_instagram_account_id' =>
                        $account->id,

                    'media_type' =>
                        $item[
                            'media_type'
                        ] ?? null,

                    'media_product_type' =>
                        $item[
                            'media_product_type'
                        ] ?? null,

                    'caption' =>
                        $item[
                            'caption'
                        ] ?? null,

                    'permalink' =>
                        $item[
                            'permalink'
                        ] ?? null,

                    'media_url' =>
                        $item[
                            'media_url'
                        ] ?? null,

                    'thumbnail_url' =>
                        $item[
                            'thumbnail_url'
                        ] ?? null,

                    'published_at' =>
                        $item[
                            'timestamp'
                        ] ?? null,

                    'raw' => $item,
                ]
            );
        }
    }
}