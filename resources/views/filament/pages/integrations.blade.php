<x-filament-panels::page>

    @php
        $searchConsole = $this->getSearchConsoleAccount();
        $searchConsoleConnected = $searchConsole?->status === 'connected';
        $searchConsoleNeedsAuth = $searchConsole?->status === 'reauthorization_required';
        $backfill = $this->getLatestSearchConsoleBackfill();
    @endphp

    @if($backfill)

    <div class="rounded-xlborder border-gray-200p-4dark:border-white/10">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-sm font-medium text-gray-950 dark:text-white">
                    Historical Import
                </div>
                <div class="mt-1 text-xs text-gray-500">
                    {{ $backfill->date_from->format('M j, Y')}} — {{$backfill->date_to->format('M j, Y')}}
                </div>

            </div>

            <x-filament::badge :color="match($backfill->status) 
            {'completed' => 'success','running' => 'info','partial'=> 'warning','failed'=> 'danger',default => 'gray',}">
                {{ucfirst($backfill->status)}}
            </x-filament::badge>

        </div>


        <div class="mt-4">

            <div class="flex justify-between text-xs text-gray-500">

                <span>
                    {{$backfill->completed_chunks}}/{{$backfill->total_chunks}} chunks
                </span>

                <span>{{number_format($backfill->progress(),1)}}%</span>

            </div>


            <div class="mt-2 h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-white/10 ">
                <div class=" h-full rounded-full bg-primary-600" style="width: {{min(100,$backfill->progress())}}%">

                </div>

            </div>

        </div>


        <div class=" mt-3 text-xs text-gray-500">
            {{number_format($backfill->rows_processed)}}
            rows processed
        </div>

    </div>

@endif

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">

        {{-- GOOGLE SEARCH CONSOLE --}}
        <x-filament::section class="mb-1" style="margin-bottom: 20px">

            <x-slot name="heading">
                Google Search Console
            </x-slot>

            <x-slot name="description">
                Organic Google search performance,
                keywords, pages, clicks,
                impressions, CTR and positions.
            </x-slot>

            <div class="space-y-5">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <div class="text-sm font-medium text-gray-950 dark:text-white">
                            Connection
                        </div>
                        <div class="mt-1">
                            @if($searchConsoleConnected)
                                <x-filament::badge color="success">
                                    Connected
                                </x-filament::badge>
                            @elseif($searchConsoleNeedsAuth)
                                <x-filament::badge color="warning">
                                    Reauthorization required
                                </x-filament::badge>
                            @else
                                <x-filament::badge color="gray">
                                    Not connected
                                </x-filament::badge>
                            @endif
                        </div>
                    </div>
                </div>

                @if($searchConsole)
                    <div class="divide-y divide-gray-20 rounded-xl border border-gray-200 dark:divide-white/10 dark:border-white/10">
                        <div
                            class="flex items-center justify-between px-4 py-3">
                            <span class=" text-sm text-gray-500 dark:text-gray-400 ">
                                Properties
                            </span>

                            <span class="
                                    text-sm
                                    font-semibold
                                    text-gray-950
                                    dark:text-white
                                "
                            >
                                {{
                                    $searchConsole
                                        ->searchConsoleSites
                                        ->where(
                                            'is_active',
                                            true
                                        )
                                        ->count()
                                }}
                            </span>
                        </div>


                        <div
                            class="
                                flex
                                items-center
                                justify-between
                                px-4
                                py-3
                            "
                        >
                            <span
                                class="
                                    text-sm
                                    text-gray-500
                                    dark:text-gray-400
                                "
                            >
                                Last sync
                            </span>

                            <span
                                class="
                                    text-sm
                                    font-medium
                                    text-gray-950
                                    dark:text-white
                                "
                            >

                                @if(
                                    $searchConsole
                                        ->last_synced_at
                                )

                                    {{
                                        $searchConsole
                                            ->last_synced_at
                                            ->diffForHumans()
                                    }}

                                @else

                                    Never

                                @endif

                            </span>
                        </div>


                        <div
                            class="
                                flex
                                items-center
                                justify-between
                                px-4
                                py-3
                            "
                        >
                            <span
                                class="
                                    text-sm
                                    text-gray-500
                                    dark:text-gray-400
                                "
                            >
                                OAuth token
                            </span>

                            <span
                                class="
                                    text-sm
                                    font-medium
                                "
                            >
                                @if(
                                    $searchConsole->token
                                    && ! $searchConsole
                                        ->token
                                        ->isExpired()
                                )

                                    <span
                                        class="
                                            text-success-600
                                        "
                                    >
                                        Valid
                                    </span>

                                @else

                                    <span
                                        class="
                                            text-warning-600
                                        "
                                    >
                                        Refresh required
                                    </span>

                                @endif
                            </span>

                        </div>

                    </div>


                    @if(
                        $searchConsole
                            ->searchConsoleSites
                            ->isNotEmpty()
                    )

                        <div class="space-y-2">

                            <div
                                class="
                                    text-sm
                                    font-medium
                                    text-gray-950
                                    dark:text-white
                                "
                            >
                                Properties
                            </div>

                            @foreach(
                                $searchConsole
                                    ->searchConsoleSites
                                    ->where(
                                        'is_active',
                                        true
                                    )
                                as $site
                            )

                                <div
                                    class="
                                        rounded-lg
                                        bg-gray-50
                                        px-3
                                        py-2
                                        text-sm
                                        dark:bg-white/5
                                    "
                                >

                                    <div
                                        class="
                                            font-medium
                                            text-gray-950
                                            dark:text-white
                                        "
                                    >
                                        {{ $site->site_url }}
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            flex
                                            flex-wrap
                                            gap-2
                                            text-xs
                                            text-gray-500
                                            dark:text-gray-400
                                        "
                                    >

                                        <span>
                                            {{
                                                ucfirst(
                                                    str_replace(
                                                        '_',
                                                        ' ',
                                                        $site
                                                            ->property_type
                                                    )
                                                )
                                            }}
                                        </span>

                                        @if(
                                            $site
                                                ->permission_level
                                        )
                                            <span>
                                                ·
                                            </span>

                                            <span>
                                                {{
                                                    $site
                                                        ->permission_level
                                                }}
                                            </span>
                                        @endif

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    @endif


                    <div
                        class="
                            flex
                            flex-wrap
                            gap-2
                        "
                    >

                        @if($searchConsoleConnected)

                            <x-filament::button
                                wire:click="
                                    syncSearchConsole
                                "
                                wire:loading.attr="
                                    disabled
                                "
                            >
                                Sync now
                            </x-filament::button>


                            <x-filament::button
                                color="gray"
                                wire:click="
                                    refreshSearchConsoleProperties
                                "
                                wire:loading.attr="
                                    disabled
                                "
                            >
                                Refresh properties
                            </x-filament::button>


                            <x-filament::button
                                color="danger"
                                outlined
                                wire:click="
                                    disconnectSearchConsole
                                "
                                wire:confirm="
                                    Are you sure you want
                                    to disconnect Google
                                    Search Console?
                                "
                            >
                                Disconnect
                            </x-filament::button>

                        @else

                            <x-filament::button
                                tag="a"
                                href="{{
                                    route(
                                        'integrations.google.search-console.connect'
                                    )
                                }}"
                            >
                                Connect Google
                            </x-filament::button>

                        @endif

                    </div>

                @else

                    <div
                        class="
                            rounded-xl
                            bg-gray-50
                            p-4
                            text-sm
                            text-gray-600
                            dark:bg-white/5
                            dark:text-gray-400
                        "
                    >
                        Connect Search Console to begin
                        importing organic search
                        performance data.
                    </div>

                    <x-filament::button
                        tag="a"
                        href="{{
                            route(
                                'integrations.google.search-console.connect'
                            )
                        }}"
                    >
                        Connect Google Search Console
                    </x-filament::button>

                @endif

            </div>

        </x-filament::section>



        {{-- GOOGLE ANALYTICS --}}
       @php
            $analytics = $this->getAnalyticsAccount();

            $analyticsConnected = $analytics?->status === 'connected';
      @endphp


<x-filament::section style="margin-bottom: 20px">

    <x-slot name="heading">
        Google Analytics 4
    </x-slot>

    <x-slot name="description">
        Website traffic, users, sessions,
        landing pages, acquisition,
        engagement and conversions.
    </x-slot>

    <div class="space-y-5">

        @if($analyticsConnected)

            <x-filament::badge
                color="success"
            >
                Connected
            </x-filament::badge>

        @elseif($analytics)

            <x-filament::badge
                color="warning"
            >
                Reauthorization required
            </x-filament::badge>

        @else

            <x-filament::badge
                color="gray"
            >
                Not connected
            </x-filament::badge>

        @endif


        @if($analytics)

            <div
                class="
                    rounded-xl
                    border
                    border-gray-200
                    dark:border-white/10
                    divide-y
                    divide-gray-200
                    dark:divide-white/10
                "
            >

                <div
                    class="
                        flex
                        justify-between
                        px-4
                        py-3
                    "
                >

                    <span
                        class="
                            text-sm
                            text-gray-500
                        "
                    >
                        Google Account
                    </span>

                    <span
                        class="
                            text-sm
                            font-medium
                        "
                    >
                        {{
                            $analytics->email
                            ?? 'Google'
                        }}
                    </span>

                </div>


                <div
                    class="
                        flex
                        justify-between
                        px-4
                        py-3
                    "
                >

                    <span
                        class="
                            text-sm
                            text-gray-500
                        "
                    >
                        Properties
                    </span>

                    <span
                        class="
                            text-sm
                            font-semibold
                        "
                    >
                        {{
                            $analytics
                                ->analyticsProperties
                                ->where(
                                    'is_active',
                                    true
                                )
                                ->count()
                        }}
                    </span>

                </div>


                <div
                    class="
                        flex
                        justify-between
                        px-4
                        py-3
                    "
                >

                    <span
                        class="
                            text-sm
                            text-gray-500
                        "
                    >
                        Last Sync
                    </span>

                    <span
                        class="
                            text-sm
                            font-medium
                        "
                    >

                        {{
                            $analytics
                                ->last_synced_at
                                ?->diffForHumans()
                            ?? 'Never'
                        }}

                    </span>

                </div>

            </div>


            @if(
                $analytics
                    ->analyticsProperties
                    ->isNotEmpty()
            )

                <div class="space-y-2">

                    <div
                        class="
                            text-sm
                            font-medium
                        "
                    >
                        GA4 Properties
                    </div>


                    @foreach(
                        $analytics
                            ->analyticsProperties
                            ->where(
                                'is_active',
                                true
                            )
                        as $property
                    )

                        <div
                            class="
                                rounded-lg
                                bg-gray-50
                                p-3
                                dark:bg-white/5
                            "
                        >

                            <div
                                class="
                                    font-medium
                                "
                            >
                                {{
                                    $property
                                        ->display_name
                                }}
                            </div>

                            <div
                                class="
                                    mt-1
                                    text-xs
                                    text-gray-500
                                "
                            >
                                Property ID:
                                {{
                                    $property
                                        ->property_id
                                }}
                            </div>

                            @if(
                                $property
                                    ->time_zone
                            )

                                <div
                                    class="
                                        mt-1
                                        text-xs
                                        text-gray-500
                                    "
                                >
                                    {{
                                        $property
                                            ->time_zone
                                    }}
                                </div>

                            @endif

                        </div>

                    @endforeach

                </div>

            @endif


            <div
                class="
                    flex
                    flex-wrap
                    gap-2
                "
            >

                @if($analyticsConnected)

                    <x-filament::button
                        wire:click="
                            syncAnalytics
                        "
                    >
                        Sync now
                    </x-filament::button>


                    <x-filament::button
                        color="gray"
                        wire:click="
                            refreshAnalyticsProperties
                        "
                    >
                        Refresh properties
                    </x-filament::button>

                @else

                    <x-filament::button
                        tag="a"
                        href="{{
                            route(
                                'integrations.google.analytics.connect'
                            )
                        }}"
                    >
                        Reconnect Google
                    </x-filament::button>

                @endif

            </div>

        @else

            <p
                class="
                    text-sm
                    text-gray-500
                "
            >
                Connect GA4 to import website traffic,
                acquisition, pages and conversion events.
            </p>

            <x-filament::button
                tag="a"
                href="{{
                    route(
                        'integrations.google.analytics.connect'
                    )
                }}"
            >
                Connect Google Analytics
            </x-filament::button>

        @endif

    </div>

</x-filament::section>


        {{-- GOOGLE BUSINESS PROFILE --}}
       @php
            $gbp = $this->getBusinessProfileAccount();
            $gbpConnected = $gbp?->status === 'connected';
            $gbpLocations = $gbp?->businessProfileAccounts->flatMap(fn ($account) => $account->locations) ?? collect();
       @endphp


<x-filament::section style="margin-bottom:20px">

    <x-slot name="heading">
        Google Business Profile
    </x-slot>

    <x-slot name="description">
        Google Search and Maps visibility,
        website clicks, call clicks,
        directions and search keywords.
    </x-slot>

    <div class="space-y-5">

        @if($gbpConnected)

            <x-filament::badge color="success">
                Connected
            </x-filament::badge>

        @else

            <x-filament::badge color="gray">
                Not connected
            </x-filament::badge>

        @endif


        @if($gbp)

            <div
                class="
                    rounded-xl
                    border
                    border-gray-200
                    divide-y
                    divide-gray-200
                    dark:border-white/10
                    dark:divide-white/10
                "
            >

                <div
                    class="
                        flex
                        justify-between
                        px-4
                        py-3
                    "
                >
                    <span>
                        Accounts
                    </span>

                    <strong>
                        {{
                            $gbp
                                ->businessProfileAccounts
                                ->count()
                        }}
                    </strong>
                </div>

                <div
                    class="
                        flex
                        justify-between
                        px-4
                        py-3
                    "
                >
                    <span>
                        Locations
                    </span>

                    <strong>
                        {{
                            $gbpLocations
                                ->count()
                        }}
                    </strong>
                </div>

            </div>


            @foreach(
                $gbpLocations
                as $location
            )

                <div
                    class="
                        rounded-lg
                        bg-gray-50
                        p-3
                        dark:bg-white/5
                    "
                >

                    <div class="font-medium">
                        {{
                            $location
                                ->title
                        }}
                    </div>

                    <div
                        class="
                            mt-1
                            text-xs
                            text-gray-500
                        "
                    >
                        {{
                            $location
                                ->city
                        }},
                        {{
                            $location
                                ->region
                        }}
                    </div>

                    <div
                        class="
                            text-xs
                            text-gray-500
                        "
                    >
                        Location ID:
                        {{
                            $location
                                ->location_id
                        }}
                    </div>

                </div>

            @endforeach


            <div
                class="
                    flex
                    flex-wrap
                    gap-2
                "
            >

                <x-filament::button
                    wire:click="
                        syncBusinessProfile
                    "
                >
                    Sync now
                </x-filament::button>

                <x-filament::button
                    color="gray"
                    wire:click="
                        refreshBusinessProfile
                    "
                >
                    Refresh locations
                </x-filament::button>

            </div>

        @else

            <x-filament::button
                tag="a"
                href="{{
                    route(
                        'integrations.google.business-profile.connect'
                    )
                }}"
            >
                Connect Google Business Profile
            </x-filament::button>

        @endif

    </div>

</x-filament::section>



        {{-- GOOGLE ADS --}}
       {{-- GOOGLE ADS / LOCAL SERVICES ADS --}}

@php
    $googleAds =
        $this->getGoogleAdsAccount();

    $googleAdsConnected =
        $googleAds?->status === 'connected';

    $googleAdsNeedsAuth =
        $googleAds?->status === 'reauthorization_required';

    $googleAdsCustomers =
        $googleAds
            ?->googleAdsCustomers
            ?? collect();

    $activeGoogleAdsCustomers =
        $googleAdsCustomers
            ->where('is_active', true);

    $lsaCampaigns =
        $activeGoogleAdsCustomers
            ->flatMap(
                fn ($customer) =>
                    $customer
                        ->campaigns
                        ->where(
                            'is_local_services',
                            true
                        )
            );
@endphp


<x-filament::section style="margin-bottom: 20px">

    <x-slot name="heading">
        Google Ads / Local Services Ads
    </x-slot>

    <x-slot name="description">
        Google Ads campaigns, Local Services Ads,
        leads, calls, costs, budgets and conversions.
    </x-slot>


    <div class="space-y-5">

        {{-- CONNECTION STATUS --}}
        <div
            class="
                flex
                items-center
                justify-between
                gap-4
            "
        >

            <div>

                <div
                    class="
                        text-sm
                        font-medium
                        text-gray-950
                        dark:text-white
                    "
                >
                    Connection
                </div>


                <div class="mt-1">

                    @if($googleAdsConnected)

                        <x-filament::badge
                            color="success"
                        >
                            Connected
                        </x-filament::badge>

                    @elseif($googleAdsNeedsAuth)

                        <x-filament::badge
                            color="warning"
                        >
                            Reauthorization required
                        </x-filament::badge>

                    @else

                        <x-filament::badge
                            color="gray"
                        >
                            Not connected
                        </x-filament::badge>

                    @endif

                </div>

            </div>

        </div>


        @if($googleAds)

            {{-- ACCOUNT SUMMARY --}}
            <div
                class="
                    divide-y
                    divide-gray-200
                    rounded-xl
                    border
                    border-gray-200
                    dark:divide-white/10
                    dark:border-white/10
                "
            >

                {{-- GOOGLE ADS ACCOUNTS --}}
                <div
                    class="
                        flex
                        items-center
                        justify-between
                        gap-4
                        px-4
                        py-3
                    "
                >

                    <span
                        class="
                            text-sm
                            text-gray-500
                            dark:text-gray-400
                        "
                    >
                        Google Ads Accounts
                    </span>


                    <span
                        class="
                            text-sm
                            font-semibold
                            text-gray-950
                            dark:text-white
                        "
                    >
                        {{
                            $activeGoogleAdsCustomers
                                ->count()
                        }}
                    </span>

                </div>


                {{-- LSA CAMPAIGNS --}}
                <div
                    class="
                        flex
                        items-center
                        justify-between
                        gap-4
                        px-4
                        py-3
                    "
                >

                    <span
                        class="
                            text-sm
                            text-gray-500
                            dark:text-gray-400
                        "
                    >
                        Local Services Campaigns
                    </span>


                    <span
                        class="
                            text-sm
                            font-semibold
                            text-gray-950
                            dark:text-white
                        "
                    >
                        {{
                            $lsaCampaigns->count()
                        }}
                    </span>

                </div>


                {{-- LAST SYNC --}}
                <div
                    class="
                        flex
                        items-center
                        justify-between
                        gap-4
                        px-4
                        py-3
                    "
                >

                    <span
                        class="
                            text-sm
                            text-gray-500
                            dark:text-gray-400
                        "
                    >
                        Last Sync
                    </span>


                    <span
                        class="
                            text-sm
                            font-medium
                            text-gray-950
                            dark:text-white
                        "
                    >
                        {{
                            $googleAds
                                ->last_synced_at
                                ?->diffForHumans()
                            ?? 'Never'
                        }}
                    </span>

                </div>


                {{-- OAUTH TOKEN --}}
                <div
                    class="
                        flex
                        items-center
                        justify-between
                        gap-4
                        px-4
                        py-3
                    "
                >

                    <span
                        class="
                            text-sm
                            text-gray-500
                            dark:text-gray-400
                        "
                    >
                        OAuth Token
                    </span>


                    @if(
                        $googleAds->token
                        &&
                        ! $googleAds
                            ->token
                            ->isExpired()
                    )

                        <x-filament::badge
                            color="success"
                        >
                            Valid
                        </x-filament::badge>

                    @else

                        <x-filament::badge
                            color="warning"
                        >
                            Refresh required
                        </x-filament::badge>

                    @endif

                </div>

            </div>


            {{-- GOOGLE ADS CUSTOMERS --}}
            @if(
                $activeGoogleAdsCustomers
                    ->isNotEmpty()
            )

                <div class="space-y-3">

                    <div
                        class="
                            text-sm
                            font-medium
                            text-gray-950
                            dark:text-white
                        "
                    >
                        Google Ads Accounts
                    </div>


                    @foreach(
                        $activeGoogleAdsCustomers
                        as $customer
                    )

                        <div
                            class="
                                rounded-xl
                                border
                                border-gray-200
                                p-4
                                dark:border-white/10
                            "
                        >

                            <div
                                class="
                                    flex
                                    items-start
                                    justify-between
                                    gap-4
                                "
                            >

                                <div>

                                    <div
                                        class="
                                            font-medium
                                            text-gray-950
                                            dark:text-white
                                        "
                                    >
                                        {{
                                            $customer
                                                ->descriptive_name
                                            ?: 'Google Ads Account'
                                        }}
                                    </div>


                                    <div
                                        class="
                                            mt-1
                                            text-xs
                                            text-gray-500
                                            dark:text-gray-400
                                        "
                                    >
                                        Customer ID:
                                        {{
                                            $customer
                                                ->customer_id
                                        }}
                                    </div>

                                </div>


                                <div
                                    class="
                                        flex
                                        flex-wrap
                                        gap-2
                                    "
                                >

                                    @if(
                                        $customer
                                            ->is_manager
                                    )

                                        <x-filament::badge
                                            color="info"
                                        >
                                            Manager / MCC
                                        </x-filament::badge>

                                    @endif


                                    @if(
                                        $customer
                                            ->is_primary
                                    )

                                        <x-filament::badge
                                            color="success"
                                        >
                                            Primary
                                        </x-filament::badge>

                                    @endif

                                </div>

                            </div>


                            <div
                                class="
                                    mt-3
                                    grid
                                    grid-cols-2
                                    gap-3
                                    text-xs
                                    text-gray-500
                                    md:grid-cols-3
                                    dark:text-gray-400
                                "
                            >

                                <div>
                                    <span class="block">
                                        Currency
                                    </span>

                                    <strong
                                        class="
                                            text-gray-950
                                            dark:text-white
                                        "
                                    >
                                        {{
                                            $customer
                                                ->currency_code
                                            ?? '—'
                                        }}
                                    </strong>
                                </div>


                                <div>
                                    <span class="block">
                                        Time Zone
                                    </span>

                                    <strong
                                        class="
                                            text-gray-950
                                            dark:text-white
                                        "
                                    >
                                        {{
                                            $customer
                                                ->time_zone
                                            ?? '—'
                                        }}
                                    </strong>
                                </div>


                                <div>
                                    <span class="block">
                                        Last Sync
                                    </span>

                                    <strong
                                        class="
                                            text-gray-950
                                            dark:text-white
                                        "
                                    >
                                        {{
                                            $customer
                                                ->last_synced_at
                                                ?->diffForHumans()
                                            ?? 'Never'
                                        }}
                                    </strong>
                                </div>

                            </div>


                            {{-- CAMPAIGNS --}}
                            @if(
                                $customer
                                    ->campaigns
                                    ->where(
                                        'is_active',
                                        true
                                    )
                                    ->isNotEmpty()
                            )

                                <div
                                    class="
                                        mt-4
                                        space-y-2
                                    "
                                >

                                    <div
                                        class="
                                            text-xs
                                            font-semibold
                                            uppercase
                                            tracking-wide
                                            text-gray-500
                                        "
                                    >
                                        Campaigns
                                    </div>


                                    @foreach(
                                        $customer
                                            ->campaigns
                                            ->where(
                                                'is_active',
                                                true
                                            )
                                        as $campaign
                                    )

                                        <div
                                            class="
                                                rounded-lg
                                                bg-gray-50
                                                p-3
                                                dark:bg-white/5
                                            "
                                        >

                                            <div
                                                class="
                                                    flex
                                                    items-start
                                                    justify-between
                                                    gap-4
                                                "
                                            >

                                                <div>

                                                    <div
                                                        class="
                                                            font-medium
                                                            text-gray-950
                                                            dark:text-white
                                                        "
                                                    >
                                                        {{
                                                            $campaign
                                                                ->name
                                                            ?? 'Campaign'
                                                        }}
                                                    </div>


                                                    <div
                                                        class="
                                                            mt-1
                                                            text-xs
                                                            text-gray-500
                                                        "
                                                    >
                                                        Campaign ID:
                                                        {{
                                                            $campaign
                                                                ->campaign_id
                                                        }}
                                                    </div>

                                                </div>


                                                <div
                                                    class="
                                                        flex
                                                        flex-wrap
                                                        justify-end
                                                        gap-2
                                                    "
                                                >

                                                    @if(
                                                        $campaign
                                                            ->is_local_services
                                                    )

                                                        <x-filament::badge
                                                            color="warning"
                                                        >
                                                            LSA
                                                        </x-filament::badge>

                                                    @else

                                                        <x-filament::badge
                                                            color="gray"
                                                        >
                                                            {{
                                                                str_replace(
                                                                    '_',
                                                                    ' ',
                                                                    $campaign
                                                                        ->advertising_channel_type
                                                                    ?? 'Campaign'
                                                                )
                                                            }}
                                                        </x-filament::badge>

                                                    @endif


                                                    <x-filament::badge
                                                        :color="
                                                            match(
                                                                $campaign
                                                                    ->status
                                                            ) {
                                                                'ENABLED'
                                                                    => 'success',

                                                                'PAUSED'
                                                                    => 'warning',

                                                                'REMOVED'
                                                                    => 'danger',

                                                                default
                                                                    => 'gray',
                                                            }
                                                        "
                                                    >
                                                        {{
                                                            ucfirst(
                                                                strtolower(
                                                                    $campaign
                                                                        ->status
                                                                    ?? 'unknown'
                                                                )
                                                            )
                                                        }}
                                                    </x-filament::badge>

                                                </div>

                                            </div>


                                            {{-- CAMPAIGN DETAILS --}}
                                            <div
                                                class="
                                                    mt-3
                                                    grid
                                                    grid-cols-2
                                                    gap-3
                                                    text-xs
                                                    md:grid-cols-3
                                                "
                                            >

                                                <div>

                                                    <div
                                                        class="
                                                            text-gray-500
                                                        "
                                                    >
                                                        Budget
                                                    </div>


                                                    <div
                                                        class="
                                                            mt-1
                                                            font-semibold
                                                            text-gray-950
                                                            dark:text-white
                                                        "
                                                    >
                                                        @if(
                                                            $campaign
                                                                ->budget_amount_micros
                                                        )

                                                            ${{
                                                                number_format(
                                                                    $campaign
                                                                        ->budget_amount_micros
                                                                    / 1_000_000,
                                                                    2
                                                                )
                                                            }}

                                                        @else

                                                            —

                                                        @endif
                                                    </div>

                                                </div>


                                                <div>

                                                    <div
                                                        class="
                                                            text-gray-500
                                                        "
                                                    >
                                                        Budget Period
                                                    </div>

                                                    <div
                                                        class="
                                                            mt-1
                                                            font-semibold
                                                            text-gray-950
                                                            dark:text-white
                                                        "
                                                    >
                                                        {{
                                                            $campaign
                                                                ->budget_period
                                                            ?? '—'
                                                        }}
                                                    </div>

                                                </div>


                                                <div>

                                                    <div
                                                        class="
                                                            text-gray-500
                                                        "
                                                    >
                                                        Bidding
                                                    </div>

                                                    <div
                                                        class="
                                                            mt-1
                                                            font-semibold
                                                            text-gray-950
                                                            dark:text-white
                                                        "
                                                    >
                                                        {{
                                                            str_replace(
                                                                '_',
                                                                ' ',
                                                                $campaign
                                                                    ->bidding_strategy_type
                                                                ?? '—'
                                                            )
                                                        }}
                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                    @endforeach

                                </div>

                            @endif

                        </div>

                    @endforeach

                </div>

            @endif


            {{-- LSA SUMMARY --}}
            @if(
                $lsaCampaigns
                    ->isNotEmpty()
            )

                <div
                    class="
                        rounded-xl
                        border
                        border-warning-200
                        bg-warning-50
                        p-4
                        dark:border-warning-500/20
                        dark:bg-warning-500/5
                    "
                >

                    <div
                        class="
                            flex
                            items-start
                            gap-3
                        "
                    >

                        <div class="flex-1">

                            <div
                                class="
                                    text-sm
                                    font-semibold
                                    text-gray-950
                                    dark:text-white
                                "
                            >
                                Local Services Ads detected
                            </div>


                            <p
                                class="
                                    mt-1
                                    text-xs
                                    text-gray-600
                                    dark:text-gray-400
                                "
                            >
                                Leads, calls, conversations,
                                charged leads and campaign
                                performance can be synchronized
                                into the CRM.
                            </p>

                        </div>


                        <x-filament::badge
                            color="warning"
                        >
                            {{
                                $lsaCampaigns
                                    ->count()
                            }}
                            LSA
                        </x-filament::badge>

                    </div>

                </div>

            @endif


            {{-- ACTIONS --}}
            <div
                class="
                    flex
                    flex-wrap
                    gap-2
                "
            >

                @if($googleAdsConnected)

                    <x-filament::button
                        wire:click="
                            syncGoogleAds
                        "
                        wire:loading.attr="
                            disabled
                        "
                    >
                        Sync now
                    </x-filament::button>


                    <x-filament::button
                        color="gray"
                        wire:click="
                            refreshGoogleAdsCustomers
                        "
                        wire:loading.attr="
                            disabled
                        "
                    >
                        Refresh accounts
                    </x-filament::button>


                    <x-filament::button
                        color="danger"
                        outlined
                        wire:click="
                            disconnectGoogleAds
                        "
                        wire:confirm="
                            Are you sure you want to disconnect Google Ads?
                        "
                    >
                        Disconnect
                    </x-filament::button>

                @else

                    <x-filament::button
                        tag="a"
                        href="{{
                            route(
                                'integrations.google.google-ads.connect'
                            )
                        }}"
                    >
                        Reconnect Google Ads
                    </x-filament::button>

                @endif

            </div>

        @else

            {{-- NOT CONNECTED --}}
            <div
                class="
                    rounded-xl
                    bg-gray-50
                    p-4
                    text-sm
                    text-gray-600
                    dark:bg-white/5
                    dark:text-gray-400
                "
            >
                Connect Google Ads to import campaign
                performance, Local Services leads,
                calls, costs and conversions.
            </div>


            <x-filament::button
                tag="a"
                href="{{
                    route(
                        'integrations.google.google-ads.connect'
                    )
                }}"
            >
                Connect Google Ads
            </x-filament::button>

        @endif

    </div>

</x-filament::section>


        {{-- META --}}

        <div class="space-y-4">

            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">

                {{-- ================================================= --}}
                {{-- META PLATFORM CONNECTION --}}
                {{-- ================================================= --}}

                @php
                    $metaIntegration =\App\Models\Integration::query()->where('provider', 'meta')->first();
                    $metaConnection = \App\Models\MetaConnection::query()->where('is_active', true)->latest()->first();
                    $metaConnected = $metaConnection !== null;
                    $metaAdAccounts =  $metaConnection ? $metaConnection->adAccounts()->count() : 0;
                    $metaPages = $metaConnection ? $metaConnection->pages()->count() : 0;
                @endphp


                <x-filament::section style="margin-bottom: 20px">

                    <div class="flex h-full flex-col gap-5">

                        {{-- HEADER --}}

                        <div class="flex items-start justify-between gap-4">

                            <div>

                                <div class="flex items-center gap-3">

                                    <div
                                        class="
                                            flex h-10 w-10
                                            items-center justify-center
                                            rounded-lg
                                            bg-blue-50
                                            text-blue-600
                                            dark:bg-blue-500/10
                                            dark:text-blue-400
                                        "
                                    >

                                       

                                    </div>


                                    <div>

                                        <h3 class="text-base font-semibold">
                                            Meta
                                        </h3>

                                        <div class="text-xs text-gray-500">
                                            Facebook + Instagram
                                        </div>

                                    </div>

                                </div>

                            </div>


                            @if ($metaConnected)

                                <x-filament::badge color="success">
                                    Connected
                                </x-filament::badge>

                            @else

                                <x-filament::badge color="gray">
                                    Disconnected
                                </x-filament::badge>

                            @endif

                        </div>



                        {{-- DESCRIPTION --}}

                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Connect Meta to import advertising campaigns,
                            ad sets, ads, Facebook Pages, Instagram content,
                            engagement and marketing insights.
                        </p>



                        {{-- CONNECTION INFO --}}

                        @if ($metaConnected)

                            <div
                                class="
                                    rounded-lg
                                    border border-gray-200
                                    p-4
                                    dark:border-white/10
                                "
                            >

                                <div
                                    class="
                                        grid grid-cols-2
                                        gap-4
                                        text-sm
                                    "
                                >

                                    <div>

                                        <div
                                            class="
                                                text-xs font-medium
                                                uppercase tracking-wide
                                                text-gray-500
                                            "
                                        >
                                            User
                                        </div>

                                        <div class="mt-1 font-medium">
                                            {{ $metaConnection->name ?? 'Meta Account' }}
                                        </div>

                                    </div>


                                    <div>

                                        <div
                                            class="
                                                text-xs font-medium
                                                uppercase tracking-wide
                                                text-gray-500
                                            "
                                        >
                                            Ad Accounts
                                        </div>

                                        <div class="mt-1 font-medium">
                                            {{ number_format($metaAdAccounts) }}
                                        </div>

                                    </div>


                                    <div>

                                        <div
                                            class="
                                                text-xs font-medium
                                                uppercase tracking-wide
                                                text-gray-500
                                            "
                                        >
                                            Facebook Pages
                                        </div>

                                        <div class="mt-1 font-medium">
                                            {{ number_format($metaPages) }}
                                        </div>

                                    </div>


                                    <div>

                                        <div
                                            class="
                                                text-xs font-medium
                                                uppercase tracking-wide
                                                text-gray-500
                                            "
                                        >
                                            Last Sync
                                        </div>

                                        <div class="mt-1 font-medium">

                                            @if ($metaConnection->last_synced_at)

                                                {{ $metaConnection->last_synced_at->diffForHumans() }}

                                            @else

                                                Never

                                            @endif

                                        </div>

                                    </div>

                                </div>

                            </div>


                            @if ($metaConnection->last_error)

                                <div
                                    class="
                                        rounded-lg
                                        bg-danger-50
                                        p-3
                                        text-sm
                                        text-danger-700
                                        dark:bg-danger-500/10
                                        dark:text-danger-400
                                    "
                                >
                                    {{ $metaConnection->last_error }}
                                </div>

                            @endif

                        @endif



                        {{-- ACTIONS --}}

                        <div class="mt-auto flex flex-wrap gap-3">

                            @if (! $metaConnected)

                                <x-filament::button
                                    tag="a"
                                    href="{{ route('integrations.meta.connect') }}"
                                    icon="heroicon-o-link"
                                >
                                    Connect Meta
                                </x-filament::button>

                            @else

                                <x-filament::button
                                    tag="a"
                                    href="{{ route('integrations.meta.connect') }}"
                                    color="gray"
                                    icon="heroicon-o-arrow-path"
                                >
                                    Reconnect
                                </x-filament::button>

                            @endif

                        </div>

                    </div>

                </x-filament::section>



                {{-- ================================================= --}}
                {{-- META ADS --}}
                {{-- ================================================= --}}

                <x-filament::section style="margin-bottom: 20px">

                    <div class="flex h-full flex-col gap-5">

                        <div class="flex items-start justify-between gap-4">

                            <div>

                                <h3 class="text-base font-semibold">
                                    Meta Ads
                                </h3>

                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Campaigns, ad sets, ads, spend,
                                    impressions, clicks and conversions.
                                </p>

                            </div>

                            @if ($metaConnected && $metaAdAccounts > 0)

                                <x-filament::badge color="success">
                                    Active
                                </x-filament::badge>

                            @else

                                <x-filament::badge color="gray">
                                    Not Synced
                                </x-filament::badge>

                            @endif

                        </div>


                        @if ($metaConnected)

                            @php

                                $campaignCount =
                                    \App\Models\MetaCampaign::query()
                                        ->count();

                                $adSetCount =
                                    \App\Models\MetaAdSet::query()
                                        ->count();

                                $adCount =
                                    \App\Models\MetaAd::query()
                                        ->count();

                            @endphp


                            <div class="grid grid-cols-3 gap-3">

                                <div
                                    class="
                                        rounded-lg bg-gray-50
                                        p-3
                                        dark:bg-white/5
                                    "
                                >
                                    <div class="text-xl font-semibold">
                                        {{ number_format($campaignCount) }}
                                    </div>

                                    <div class="text-xs text-gray-500">
                                        Campaigns
                                    </div>
                                </div>


                                <div
                                    class="
                                        rounded-lg bg-gray-50
                                        p-3
                                        dark:bg-white/5
                                    "
                                >
                                    <div class="text-xl font-semibold">
                                        {{ number_format($adSetCount) }}
                                    </div>

                                    <div class="text-xs text-gray-500">
                                        Ad Sets
                                    </div>
                                </div>


                                <div
                                    class="
                                        rounded-lg bg-gray-50
                                        p-3
                                        dark:bg-white/5
                                    "
                                >
                                    <div class="text-xl font-semibold">
                                        {{ number_format($adCount) }}
                                    </div>

                                    <div class="text-xs text-gray-500">
                                        Ads
                                    </div>
                                </div>

                            </div>

                        @endif


                        <div class="mt-auto">

                            @if (! $metaConnected)

                                <span class="text-sm text-gray-500">
                                    Connect Meta first.
                                </span>

                            @elseif ($metaAdAccounts === 0)

                                <span class="text-sm text-warning-600">
                                    Connected. Run the first synchronization.
                                </span>

                            @else

                                <span class="text-sm text-success-600">
                                    Advertising data available.
                                </span>

                            @endif

                        </div>

                    </div>

                </x-filament::section>



                {{-- ================================================= --}}
                {{-- ORGANIC SOCIAL --}}
                {{-- ================================================= --}}

                <x-filament::section style="margin-bottom: 20px">

                    @php

                        $facebookPosts =
                            class_exists(\App\Models\MetaPagePost::class)
                                ? \App\Models\MetaPagePost::query()->count()
                                : 0;

                        $instagramMedia =
                            class_exists(\App\Models\MetaInstagramMedia::class)
                                ? \App\Models\MetaInstagramMedia::query()->count()
                                : 0;

                    @endphp


                    <div class="flex h-full flex-col gap-5">

                        <div class="flex items-start justify-between gap-4">

                            <div>

                                <h3 class="text-base font-semibold">
                                    Organic Social
                                </h3>

                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Facebook posts, Instagram posts,
                                    Reels, engagement and organic traffic.
                                </p>

                            </div>


                            @if ($metaConnected && $metaPages > 0)

                                <x-filament::badge color="success">
                                    Active
                                </x-filament::badge>

                            @else

                                <x-filament::badge color="gray">
                                    Not Synced
                                </x-filament::badge>

                            @endif

                        </div>


                        @if ($metaConnected)

                            <div class="grid grid-cols-2 gap-3">

                                <div
                                    class="
                                        rounded-lg bg-gray-50
                                        p-3
                                        dark:bg-white/5
                                    "
                                >

                                    <div class="text-xl font-semibold">
                                        {{ number_format($facebookPosts) }}
                                    </div>

                                    <div class="text-xs text-gray-500">
                                        Facebook Posts
                                    </div>

                                </div>


                                <div
                                    class="
                                        rounded-lg bg-gray-50
                                        p-3
                                        dark:bg-white/5
                                    "
                                >

                                    <div class="text-xl font-semibold">
                                        {{ number_format($instagramMedia) }}
                                    </div>

                                    <div class="text-xs text-gray-500">
                                        Instagram Media
                                    </div>

                                </div>

                            </div>

                        @endif


                        <div class="mt-auto">

                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                Traffic attribution will be combined with GA4
                                using UTM parameters and referrer data.
                            </span>

                        </div>

                    </div>

                </x-filament::section>

            </div>

        </div>




        {{-- YOUTUBE --}}
        <x-filament::section style="margin-bottom:20px">

            <x-slot name="heading">
                YouTube
            </x-slot>

            <x-slot name="description">
                Videos, Shorts, views,
                engagement and traffic
                generated to the website.
            </x-slot>

            <div class="space-y-5">

                <x-filament::badge color="gray">
                    Not connected
                </x-filament::badge>

                <x-filament::button
                    color="gray"
                    disabled
                >
                    Coming soon
                </x-filament::button>

            </div>

        </x-filament::section>

    </div>

</x-filament-panels::page>