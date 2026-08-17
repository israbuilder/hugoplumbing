<?php

namespace App\Integrations\Google\SearchConsole;

use App\Models\IntegrationAccount;
use App\Models\SearchConsoleSite;
use Illuminate\Support\Collection;

class SearchConsoleService
{
    public function __construct(
        protected SearchConsoleClient $client
    ) {
    }

    public function syncSites(
        IntegrationAccount $account
    ): Collection {
        $sites = $this->client->sites($account);

        $ids = [];

        foreach ($sites as $siteData) {

            $siteUrl = $siteData['siteUrl'];

            $site = SearchConsoleSite::updateOrCreate(
                [
                    'integration_account_id' =>
                        $account->id,

                    'site_url' => $siteUrl,
                ],
                [
                    'property_type' =>
                        $this->detectPropertyType(
                            $siteUrl
                        ),

                    'permission_level' =>
                        $siteData['permissionLevel']
                        ?? null,

                    'is_active' => true,

                    'metadata' => $siteData,
                ]
            );

            $ids[] = $site->id;
        }

        /*
         * Mark properties no longer returned by Google
         * as inactive.
         */
        $account->searchConsoleSites()
            ->when(
                count($ids) > 0,
                fn ($query) =>
                    $query->whereNotIn('id', $ids)
            )
            ->update([
                'is_active' => false,
            ]);

        return SearchConsoleSite::query()
            ->whereIn('id', $ids)
            ->get();
    }

    protected function detectPropertyType(
        string $siteUrl
    ): string {
        if (
            str_starts_with(
                $siteUrl,
                'sc-domain:'
            )
        ) {
            return 'domain';
        }

        return 'url_prefix';
    }
}