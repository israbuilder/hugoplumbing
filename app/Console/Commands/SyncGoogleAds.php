<?php

namespace App\Console\Commands;

use App\Integrations\Google\Ads\GoogleAdsService;
use App\Jobs\SyncGoogleAdsCustomer;
use App\Models\IntegrationAccount;
use Illuminate\Console\Command;

class SyncGoogleAds extends Command
{
    protected $signature = '
        google-ads:sync
        {--from=}
        {--to=}
        {--refresh-customers}
        {--queue}
    ';

    protected $description =
        'Synchronize Google Ads and Local Services Ads';

    public function handle(
        GoogleAdsService $service
    ): int {
        $accounts =
            IntegrationAccount::query()
                ->whereHas(
                    'integration',
                    fn ($query) =>
                        $query->where(
                            'provider',
                            'google_ads'
                        )
                )
                ->where(
                    'status',
                    'connected'
                )
                ->with([
                    'token',
                    'googleAdsCustomers',
                ])
                ->get();

        if ($accounts->isEmpty()) {

            $this->warn(
                'No connected Google Ads accounts.'
            );

            return self::SUCCESS;
        }

        $from =
            $this->option('from')
            ?? now()
                ->subDays(7)
                ->toDateString();

        $to =
            $this->option('to')
            ?? now()
                ->toDateString();

        foreach ($accounts as $account) {

            if (
                $this->option(
                    'refresh-customers'
                )
                || $account
                    ->googleAdsCustomers
                    ->isEmpty()
            ) {
                $this->info(
                    'Discovering customers...'
                );

                $service->syncCustomers(
                    $account
                );

                $account->load(
                    'googleAdsCustomers'
                );
            }

            foreach (
                $account
                    ->googleAdsCustomers
                    ->where(
                        'is_active',
                        true
                    )
                    ->where(
                        'is_manager',
                        false
                    )
                as $customer
            ) {
                $this->line(
                    sprintf(
                        '→ %s (%s)',
                        $customer
                            ->descriptive_name
                            ?? 'Google Ads',

                        $customer
                            ->customer_id
                    )
                );

                if (
                    $this->option('queue')
                ) {

                    SyncGoogleAdsCustomer::dispatch(
                        customerId:
                            $customer->id,

                        from:
                            $from,

                        to:
                            $to,
                    );

                } else {

                    SyncGoogleAdsCustomer::dispatchSync(
                        customerId:
                            $customer->id,

                        from:
                            $from,

                        to:
                            $to,
                    );
                }
            }
        }

        return self::SUCCESS;
    }
}