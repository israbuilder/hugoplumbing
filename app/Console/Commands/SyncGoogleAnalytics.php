<?php

namespace App\Console\Commands;

use App\Integrations\Google\Analytics\AnalyticsService;
use App\Jobs\SyncAnalyticsProperty;
use App\Models\IntegrationAccount;
use Illuminate\Console\Command;

class SyncGoogleAnalytics extends Command
{
    protected $signature = '
        analytics:sync
        {--from= : Start date YYYY-MM-DD}
        {--to= : End date YYYY-MM-DD}
        {--sync-properties : Refresh GA4 properties}
        {--queue : Dispatch to queue}
    ';

    protected $description =
        'Synchronize Google Analytics 4 data';

    public function handle(
        AnalyticsService $analytics
    ): int {

        $accounts =
            IntegrationAccount::query()
                ->whereHas(
                    'integration',
                    fn ($query) =>
                        $query->where(
                            'provider',
                            'google_analytics'
                        )
                )
                ->where(
                    'status',
                    'connected'
                )
                ->with([
                    'integration',
                    'token',
                    'analyticsProperties',
                ])
                ->get();

        if (
            $accounts->isEmpty()
        ) {

            $this->warn(
                'No connected Google Analytics accounts found.'
            );

            return self::SUCCESS;
        }

        $from =
            $this->option(
                'from'
            )
            ?: now()
                ->subDays(3)
                ->toDateString();

        $to =
            $this->option(
                'to'
            )
            ?: now()
                ->subDay()
                ->toDateString();

        foreach (
            $accounts
            as $account
        ) {

            $this->info(
                sprintf(
                    'Analytics account: %s',
                    $account->email
                    ?? "#{$account->id}"
                )
            );

            if (
                $this->option(
                    'sync-properties'
                )
                || $account
                    ->analyticsProperties
                    ->isEmpty()
            ) {

                $this->line(
                    'Refreshing properties...'
                );

                $analytics
                    ->syncProperties(
                        $account
                    );

                $account->load(
                    'analyticsProperties'
                );
            }

            foreach (
                $account
                    ->analyticsProperties
                    ->where(
                        'is_active',
                        true
                    )
                as $property
            ) {

                $this->line(
                    sprintf(
                        ' → %s (%s)',
                        $property
                            ->display_name,

                        $property
                            ->property_id
                    )
                );

                if (
                    $this->option(
                        'queue'
                    )
                ) {

                    SyncAnalyticsProperty::dispatch(
                        propertyId:
                            $property->id,

                        from:
                            $from,

                        to:
                            $to,
                    );

                } else {

                    SyncAnalyticsProperty::dispatchSync(
                        propertyId:
                            $property->id,

                        from:
                            $from,

                        to:
                            $to,
                    );
                }
            }
        }

        $this->newLine();

        $this->info(
            'Google Analytics synchronization completed.'
        );

        return self::SUCCESS;
    }
}