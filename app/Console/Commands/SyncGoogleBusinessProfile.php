<?php

namespace App\Console\Commands;

use App\Integrations\Google\BusinessProfile\BusinessProfileService;
use App\Jobs\SyncBusinessProfileLocation;
use App\Models\IntegrationAccount;
use Illuminate\Console\Command;

class SyncGoogleBusinessProfile extends Command
{
    protected $signature = '
        business-profile:sync
        {--from=}
        {--to=}
        {--refresh-locations}
        {--queue}
    ';

    protected $description =
        'Synchronize Google Business Profile metrics';

    public function handle(
        BusinessProfileService $service
    ): int {
        $accounts =
            IntegrationAccount::query()
                ->whereHas(
                    'integration',
                    fn ($q) =>
                        $q->where(
                            'provider',
                            'google_business_profile'
                        )
                )
                ->where(
                    'status',
                    'connected'
                )
                ->with([
                    'token',
                    'businessProfileAccounts.locations',
                ])
                ->get();

        foreach (
            $accounts as $account
        ) {
            if (
                $this->option(
                    'refresh-locations'
                )
                || $account
                    ->businessProfileAccounts
                    ->isEmpty()
            ) {
                $service
                    ->syncAccountsAndLocations(
                        $account
                    );

                $account->load(
                    'businessProfileAccounts.locations'
                );
            }

            foreach (
                $account
                    ->businessProfileAccounts
                as $gbpAccount
            ) {
                foreach (
                    $gbpAccount
                        ->locations
                        ->where(
                            'is_active',
                            true
                        )
                    as $location
                ) {
                    $from =
                        $this->option('from')
                        ?? now()
                            ->subMonth()
                            ->startOfMonth()
                            ->toDateString();

                    $to =
                        $this->option('to')
                        ?? now()
                            ->subMonth()
                            ->endOfMonth()
                            ->toDateString();

                    $this->line(
                        "→ {$location->title}"
                    );

                    if (
                        $this->option(
                            'queue'
                        )
                    ) {
                        SyncBusinessProfileLocation::dispatch(
                            locationId:
                                $location->id,

                            from:
                                $from,

                            to:
                                $to,
                        );
                    } else {
                        SyncBusinessProfileLocation::dispatchSync(
                            locationId:
                                $location->id,

                            from:
                                $from,

                            to:
                                $to,
                        );
                    }
                }
            }
        }

        return self::SUCCESS;
    }
}