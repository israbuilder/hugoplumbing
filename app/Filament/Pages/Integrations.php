<?php

namespace App\Filament\Pages;

use App\Integrations\Google\SearchConsole\SearchConsoleService;
use App\Integrations\Google\Analytics\AnalyticsService;
use App\Integrations\Google\BusinessProfile\BusinessProfileService;
use App\Jobs\SyncBusinessProfileLocation;
use App\Jobs\SyncAnalyticsProperty;
use App\Jobs\SyncSearchConsoleSite;
use App\Models\Integration;
use App\Models\IntegrationAccount;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Throwable;
use UnitEnum;
use BackedEnum;

class Integrations extends Page
{
    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedPuzzlePiece;

    protected static string|UnitEnum|null $navigationGroup =
        'Settings';

    protected static ?string $navigationLabel =
        'Integrations';

    protected static ?string $title =
        'Integrations';

    protected static ?int $navigationSort = 100;

    protected string $view =
        'filament.pages.integrations';

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    

    public function getLatestSearchConsoleBackfill()
{
    $account =
        $this->getSearchConsoleAccount();

    if (!$account) {
        return null;
    }

    $siteIds =
        $account
            ->searchConsoleSites
            ->pluck('id');

    if ($siteIds->isEmpty()) {
        return null;
    }

    return \App\Models\SearchConsoleBackfill::query()
        ->whereIn(
            'search_console_site_id',
            $siteIds
        )
        ->latest()
        ->first();
}

    public function getSubheading(): ?string
    {
        return 'Connect and manage external marketing and analytics platforms.';
    }

    public function getIntegrationsProperty(): Collection
    {
        return Integration::query()
            ->with([
                'accounts' => function ($query) {
                    $query
                        ->latest('connected_at')
                        ->with([
                            'token',
                            'searchConsoleSites',
                        ]);
                },
            ])
            ->orderBy('category')
            ->orderBy('name')
            ->get();
    }

    public function getSearchConsoleAccount(): ?IntegrationAccount
    {
        return IntegrationAccount::query()
            ->whereHas(
                'integration',
                fn ($query) =>
                    $query->where(
                        'provider',
                        'google_search_console'
                    )
            )
            ->whereIn('status', [
                'connected',
                'reauthorization_required',
            ])
            ->with([
                'integration',
                'token',
                'searchConsoleSites',
            ])
            ->latest('connected_at')
            ->first();
    }

    public function refreshSearchConsoleProperties(): void
    {
        $account = $this->getSearchConsoleAccount();

        if (!$account) {
            Notification::make()
                ->title('Search Console is not connected')
                ->warning()
                ->send();

            return;
        }

        try {
            /** @var SearchConsoleService $service */
            $service = app(
                SearchConsoleService::class
            );

            $sites = $service->syncSites(
                $account
            );

            Notification::make()
                ->title(
                    'Search Console properties refreshed'
                )
                ->body(
                    "{$sites->count()} properties found."
                )
                ->success()
                ->send();

        } catch (Throwable $e) {
            report($e);

            Notification::make()
                ->title(
                    'Could not refresh Search Console'
                )
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function syncSearchConsole(): void
    {
        $account = $this->getSearchConsoleAccount();

        if (!$account) {
            Notification::make()
                ->title(
                    'Search Console is not connected'
                )
                ->warning()
                ->send();

            return;
        }

        $sites = $account
            ->searchConsoleSites
            ->where('is_active', true);

        if ($sites->isEmpty()) {
            Notification::make()
                ->title(
                    'No active Search Console properties'
                )
                ->warning()
                ->send();

            return;
        }

        foreach ($sites as $site) {
            SyncSearchConsoleSite::dispatch(
                siteId: $site->id,
                from: now()
                    ->subDays(3)
                    ->toDateString(),
                to: now()
                    ->subDay()
                    ->toDateString(),
            );
        }

        Notification::make()
            ->title(
                'Search Console synchronization started'
            )
            ->body(
                "{$sites->count()} properties queued."
            )
            ->success()
            ->send();
    }

    public function disconnectSearchConsole(): void
    {
        $account = $this->getSearchConsoleAccount();

        if (!$account) {
            return;
        }

        try {
            if ($account->token) {
                $account->token->delete();
            }

            $account->update([
                'status' => 'disconnected',
            ]);

            Notification::make()
                ->title(
                    'Google Search Console disconnected'
                )
                ->success()
                ->send();

        } catch (Throwable $e) {
            report($e);

            Notification::make()
                ->title(
                    'Could not disconnect Search Console'
                )
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getAnalyticsAccount(): ?IntegrationAccount
    {
        return IntegrationAccount::query()
            ->whereHas(
                'integration',
                fn ($query) =>
                    $query->where(
                        'provider',
                        'google_analytics'
                    )
            )
            ->whereIn(
                'status',
                [
                    'connected',
                    'reauthorization_required',
                ]
            )
            ->with([
                'integration',
                'token',
                'analyticsProperties',
            ])
            ->latest(
                'connected_at'
            )
            ->first();
    }
    public function refreshAnalyticsProperties(): void
    {
        $account =
            $this->getAnalyticsAccount();

        if (!$account) {

            Notification::make()
                ->title(
                    'Google Analytics is not connected'
                )
                ->warning()
                ->send();

            return;
        }

        try {

            $service =
                app(
                    AnalyticsService::class
                );

            $properties =
                $service
                    ->syncProperties(
                        $account
                    );

            Notification::make()
                ->title(
                    'Analytics properties refreshed'
                )
                ->body(
                    $properties->count()
                    . ' properties found.'
                )
                ->success()
                ->send();

        } catch (
            Throwable $e
        ) {

            report($e);

            Notification::make()
                ->title(
                    'Could not refresh Analytics'
                )
                ->body(
                    $e->getMessage()
                )
                ->danger()
                    ->send();
        }
    }

        public function syncAnalytics(): void
{
    $account =
        $this->getAnalyticsAccount();

    if (!$account) {

        Notification::make()
            ->title(
                'Google Analytics is not connected'
            )
            ->warning()
            ->send();

        return;
    }

    $properties =
        $account
            ->analyticsProperties
            ->where(
                'is_active',
                true
            );

    if (
        $properties->isEmpty()
    ) {

        Notification::make()
            ->title(
                'No active GA4 properties'
            )
            ->warning()
            ->send();

        return;
    }

    foreach (
        $properties
        as $property
    ) {

        SyncAnalyticsProperty::dispatch(
            propertyId:
                $property->id,

            from:
                now()
                    ->subDays(3)
                    ->toDateString(),

            to:
                now()
                    ->subDay()
                    ->toDateString(),
        );
    }

    Notification::make()
        ->title(
            'Google Analytics sync started'
        )
        ->body(
            $properties->count()
            . ' properties queued.'
        )
        ->success()
        ->send();
}

public function getBusinessProfileAccount(): ?IntegrationAccount
{
    return IntegrationAccount::query()
        ->whereHas(
            'integration',
            fn ($query) =>
                $query->where(
                    'provider',
                    'google_business_profile'
                )
        )
        ->whereIn(
            'status',
            [
                'connected',
                'reauthorization_required',
            ]
        )
        ->with([
            'token',
            'businessProfileAccounts.locations',
        ])
        ->latest(
            'connected_at'
        )
        ->first();
}

public function refreshBusinessProfile(): void
{
    $account =
        $this->getBusinessProfileAccount();

    if (!$account) {
        return;
    }

    try {
        $service =
            app(
                BusinessProfileService::class
            );

        $accounts =
            $service
                ->syncAccountsAndLocations(
                    $account
                );

        Notification::make()
            ->title(
                'Business Profile refreshed'
            )
            ->body(
                $accounts->count()
                . ' accounts found.'
            )
            ->success()
            ->send();

    } catch (Throwable $e) {
        report($e);

        Notification::make()
            ->title(
                'GBP refresh failed'
            )
            ->body(
                $e->getMessage()
            )
            ->danger()
            ->send();
    }
}

    public function syncBusinessProfile(): void
    {
        $account =
            $this->getBusinessProfileAccount();

        if (!$account) {
            return;
        }

        $locations =
            $account
                ->businessProfileAccounts
                ->flatMap(
                    fn ($account) =>
                        $account
                            ->locations
                            ->where(
                                'is_active',
                                true
                            )
                );

        foreach (
            $locations
            as $location
        ) {
            SyncBusinessProfileLocation::dispatch(
                locationId:
                    $location->id,

                from:
                    now()
                        ->subMonth()
                        ->startOfMonth()
                        ->toDateString(),

                to:
                    now()
                        ->subMonth()
                        ->endOfMonth()
                        ->toDateString(),
            );
        }

        Notification::make()
            ->title(
                'GBP synchronization started'
            )
            ->body(
                $locations->count()
                . ' location(s) queued.'
            )
            ->success()
            ->send();
    }

    public function getGoogleAdsAccount(): ?IntegrationAccount
        {
            return IntegrationAccount::query()
                ->whereHas(
                    'integration',
                    fn ($query) =>
                        $query->where(
                            'provider',
                            'google_ads'
                        )
                )
                ->whereIn(
                    'status',
                    [
                        'connected',
                        'reauthorization_required',
                    ]
                )
                ->with([
                    'integration',
                    'token',

                    'googleAdsCustomers' => fn ($query) =>
                        $query
                            ->orderByDesc('is_primary')
                            ->orderBy('descriptive_name'),

                    'googleAdsCustomers.campaigns' =>
                        fn ($query) =>
                            $query
                                ->orderByDesc(
                                    'is_local_services'
                                )
                                ->orderBy('name'),
                ])
                ->latest('connected_at')
                ->first();
        }

        public function refreshGoogleAdsCustomers(): void
{
    $account =
        $this->getGoogleAdsAccount();

    if (!$account) {

        Notification::make()
            ->title(
                'Google Ads is not connected'
            )
            ->warning()
            ->send();

        return;
    }

    try {

        /** @var \App\Integrations\Google\Ads\GoogleAdsService $service */
        $service =
            app(
                \App\Integrations\Google\Ads\GoogleAdsService::class
            );

        $customers =
            $service->syncCustomers(
                $account
            );

        /*
         * Also discover campaigns now,
         * so the LSA badge can appear
         * without waiting for the scheduler.
         */
        $sync =
            app(
                \App\Integrations\Google\Ads\GoogleAdsSyncService::class
            );

        foreach (
            $customers
                ->where(
                    'is_manager',
                    false
                )
            as $customer
        ) {
            $sync->syncCampaigns(
                $customer
            );
        }

        Notification::make()
            ->title(
                'Google Ads accounts refreshed'
            )
            ->body(
                $customers->count()
                . ' account(s) found.'
            )
            ->success()
            ->send();

    } catch (\Throwable $e) {

        report($e);

        Notification::make()
            ->title(
                'Could not refresh Google Ads'
            )
            ->body(
                $e->getMessage()
            )
            ->danger()
            ->send();
    }
}

public function syncGoogleAds(): void
{
    $account =
        $this->getGoogleAdsAccount();

    if (!$account) {

        Notification::make()
            ->title(
                'Google Ads is not connected'
            )
            ->warning()
            ->send();

        return;
    }

    $customers =
        $account
            ->googleAdsCustomers
            ->where(
                'is_active',
                true
            )
            ->where(
                'is_manager',
                false
            );

    if ($customers->isEmpty()) {

        Notification::make()
            ->title(
                'No Google Ads customer accounts found'
            )
            ->warning()
            ->send();

        return;
    }

    foreach (
        $customers
        as $customer
    ) {

        \App\Jobs\SyncGoogleAdsCustomer::dispatch(
            customerId:
                $customer->id,

            from:
                now()
                    ->subDays(7)
                    ->toDateString(),

            to:
                now()
                    ->toDateString(),
        );
    }

    Notification::make()
        ->title(
            'Google Ads synchronization started'
        )
        ->body(
            $customers->count()
            . ' account(s) queued.'
        )
        ->success()
        ->send();
}

public function disconnectGoogleAds(): void
{
    $account =
        $this->getGoogleAdsAccount();

    if (!$account) {
        return;
    }

    try {

        $account
            ->token
            ?->delete();

        $account->update([
            'status' =>
                'disconnected',
        ]);

        Notification::make()
            ->title(
                'Google Ads disconnected'
            )
            ->success()
            ->send();

    } catch (\Throwable $e) {

        report($e);

        Notification::make()
            ->title(
                'Could not disconnect Google Ads'
            )
            ->body(
                $e->getMessage()
            )
            ->danger()
            ->send();
    }
}
}