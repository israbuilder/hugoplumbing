<?php

namespace App\Integrations\Meta;

use App\Models\MetaPage;
use App\Models\MetaPagePost;

class MetaFacebookPostSyncService
{
    public function __construct(
        protected MetaApiClient $api
    ) {}

    public function sync(
        MetaPage $page
    ): void {

        $posts = $this->api->getAll(
            '/' .
            $page->meta_page_id .
            '/posts',

            $page->page_access_token,

            [
                'fields' => implode(',', [
                    'id',
                    'message',
                    'created_time',
                    'permalink_url',
                    'full_picture',
                    'shares',
                ]),

                'limit' => 100,
            ]
        );

        foreach ($posts as $post) {

            MetaPagePost::updateOrCreate(
                [
                    'meta_post_id' =>
                        $post['id'],
                ],
                [
                    'meta_page_id' =>
                        $page->id,

                    'message' =>
                        $post[
                            'message'
                        ] ?? null,

                    'permalink_url' =>
                        $post[
                            'permalink_url'
                        ] ?? null,

                    'shares' =>
                        data_get(
                            $post,
                            'shares.count',
                            0
                        ),

                    'published_at' =>
                        $post[
                            'created_time'
                        ] ?? null,

                    'raw' => $post,
                ]
            );
        }
    }
}