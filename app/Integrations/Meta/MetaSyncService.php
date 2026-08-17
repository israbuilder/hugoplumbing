<?php

namespace App\Integrations\Meta;

use App\Models\MetaConnection;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class MetaSyncService
{
    public function __construct(
        protected MetaAdAccountSyncService $accounts,
        protected MetaCampaignSyncService $campaigns,
        protected MetaAdSetSyncService $adSets,
        protected MetaAdSyncService $ads,
        protected MetaAdsInsightsSyncService $insights,
        protected MetaPageSyncService $pages,
        protected MetaFacebookPostSyncService $facebookPosts,
        protected MetaInstagramSyncService $instagram,
        protected MetaInstagramMediaSyncService $instagramMedia,
    ) {}

    public function sync(
        MetaConnection $connection,
        int $days = 30
    ): void {

        try {

            $this->accounts->sync(
                $connection
            );

            $connection->refresh();

            foreach (
                $connection->adAccounts
                as $account
            ) {

                $this->campaigns->sync(
                    $account
                );

                $account->load(
                    'campaigns'
                );

                foreach (
                    $account->campaigns
                    as $campaign
                ) {

                    $this->adSets->sync(
                        $campaign
                    );

                    $campaign->load(
                        'adSets'
                    );

                    foreach (
                        $campaign->adSets
                        as $adSet
                    ) {

                        $this->ads->sync(
                            $adSet
                        );
                    }
                }

                $this->insights->sync(
                    $account,
                    now()
                        ->subDays($days)
                        ->startOfDay(),
                    now()->endOfDay(),
                    'ad'
                );
            }

            $this->pages->sync(
                $connection
            );

            $connection->load('pages');

            foreach (
                $connection->pages
                as $page
            ) {

                $this->facebookPosts->sync(
                    $page
                );

                $instagram =
                    $this->instagram->sync(
                        $page
                    );

                if ($instagram) {

                    $this->instagramMedia->sync(
                        $instagram
                    );
                }
            }

            $connection->update([
                'last_synced_at' => now(),
                'last_error' => null,
            ]);

        } catch (Throwable $e) {

            $connection->update([
                'last_error' =>
                    $e->getMessage(),
            ]);

            throw $e;
        }
    }
}