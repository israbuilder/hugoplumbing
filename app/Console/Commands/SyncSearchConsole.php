<?php

namespace App\Console\Commands;

use App\Integrations\Google\SearchConsole\SearchConsoleService;
use App\Jobs\SyncSearchConsoleSite;
use App\Models\IntegrationAccount;
use Illuminate\Console\Command;

class SyncSearchConsole extends Command
{
    protected $signature =
        'search-console:sync
        {--from= : Start date YYYY-MM-DD}
        {--to= : End date YYYY-MM-DD}
        {--sync-sites : Refresh Search Console properties first}
        {--queue : Dispatch jobs to queue}';

    protected $description =
        'Synchronize Google Search Console performance data';

    public function handle(
        SearchConsoleService $searchConsole
    ): int {
        $accounts =
            IntegrationAccount::query()
                ->whereHas(
                    'integration',
                    fn ($query) =>
                        $query->where(
                            'provider',
                            'google_search_console'
                        )
                )
                ->where(
                    'status',
                    'connected'
                )
                ->with([
                    'integration',
                    'token',
                    'searchConsoleSites',
                ])
                ->get();

        if ($accounts->isEmpty()) {

            $this->warn(
                'No connected Search Console accounts found.'
            );

            return self::SUCCESS;
        }

        $from =
            $this->option('from')
            ?: now()
                ->subDays(3)
                ->toDateString();

        $to =
            $this->option('to')
            ?: now()
                ->subDays(1)
                ->toDateString();

        foreach ($accounts as $account) {

            $this->info(
                "Account #{$account->id}"
            );

            if (
                $this->option('sync-sites')
                || $account
                    ->searchConsoleSites
                    ->isEmpty()
            ) {
                $this->info(
                    'Refreshing properties...'
                );

                $searchConsole
                    ->syncSites($account);

                $account->load(
                    'searchConsoleSites'
                );
            }

            foreach (
                $account->searchConsoleSites
                    ->where('is_active', true)
                as $site
            ) {

                $this->line(
                    " → {$site->site_url}"
                );

                if ($this->option('queue')) {

                    SyncSearchConsoleSite::dispatch(
                        siteId: $site->id,
                        from: $from,
                        to: $to,
                    );

                } else {

                    SyncSearchConsoleSite::dispatchSync(
                        siteId: $site->id,
                        from: $from,
                        to: $to,
                    );
                }
            }
        }

        $this->newLine();

        $this->info(
            'Search Console sync completed.'
        );

        return self::SUCCESS;
    }
}