<?php

namespace App\Integrations\Meta;

use App\Models\MetaConnection;
use App\Models\MetaPage;

class MetaPageSyncService
{
    public function __construct(
        protected MetaApiClient $api
    ) {}

    public function sync(
        MetaConnection $connection
    ): void {

        $pages = $this->api->getAll(
            '/me/accounts',
            $connection->access_token,
            [
                'fields' => implode(',', [
                    'id',
                    'name',
                    'category',
                    'access_token',
                    'tasks',
                    'instagram_business_account',
                ]),

                'limit' => 100,
            ]
        );

        foreach ($pages as $page) {

            MetaPage::updateOrCreate(
                [
                    'meta_page_id' =>
                        $page['id'],
                ],
                [
                    'meta_connection_id' =>
                        $connection->id,

                    'name' =>
                        $page['name'],

                    'category' =>
                        $page[
                            'category'
                        ] ?? null,

                    'page_access_token' =>
                        $page[
                            'access_token'
                        ] ?? null,

                    'instagram_business_account_id' =>
                        data_get(
                            $page,
                            'instagram_business_account.id'
                        ),

                    'tasks' =>
                        $page['tasks'] ?? null,

                    'raw' => $page,
                ]
            );
        }
    }
}