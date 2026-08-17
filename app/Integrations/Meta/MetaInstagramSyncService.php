<?php

namespace App\Integrations\Meta;

use App\Models\MetaInstagramAccount;
use App\Models\MetaPage;

class MetaInstagramSyncService
{
    public function __construct(
        protected MetaApiClient $api
    ) {}

    public function sync(
        MetaPage $page
    ): ?MetaInstagramAccount {

        if (!$page->instagram_business_account_id) {
            return null;
        }

        $data = $this->api->get(
            '/' .
            $page->instagram_business_account_id,

            $page->page_access_token,

            [
                'fields' => implode(',', [
                    'id',
                    'username',
                    'name',
                    'profile_picture_url',
                    'followers_count',
                    'follows_count',
                    'media_count',
                ]),
            ]
        );

        return MetaInstagramAccount::updateOrCreate(
            [
                'meta_instagram_id' =>
                    $data['id'],
            ],
            [
                'meta_page_id' =>
                    $page->id,

                'username' =>
                    $data['username'] ?? null,

                'name' =>
                    $data['name'] ?? null,

                'profile_picture_url' =>
                    $data[
                        'profile_picture_url'
                    ] ?? null,

                'followers_count' =>
                    $data[
                        'followers_count'
                    ] ?? 0,

                'follows_count' =>
                    $data[
                        'follows_count'
                    ] ?? 0,

                'media_count' =>
                    $data[
                        'media_count'
                    ] ?? 0,

                'raw' => $data,
            ]
        );
    }
}