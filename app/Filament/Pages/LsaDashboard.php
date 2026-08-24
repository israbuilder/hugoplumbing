<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\GoogleAds\LsaCampaignPerformanceChart;
use App\Filament\Widgets\GoogleAds\LsaCallsTable;
use App\Filament\Widgets\GoogleAds\LsaLeadStatusChart;
use App\Filament\Widgets\GoogleAds\LsaLeadsTable;
use App\Filament\Widgets\GoogleAds\LsaPerformanceChart;
use App\Filament\Widgets\GoogleAds\LsaStatsOverview;
use App\Models\GoogleAdsCampaign;
use App\Models\GoogleAdsCustomer;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class LsaDashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static string $routePath =
        'google-ads/lsa';

    protected static ?string $title =
        'Local Services Ads';

    protected static ?string $navigationLabel =
        'Google Ads / LSA';

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedMegaphone;

    protected static string|UnitEnum|null $navigationGroup =
        'Marketing';

    protected static ?int $navigationSort =
        30;

    protected bool $persistsFiltersInSession =
        true;

    public function getHeading(): string
    {
        return 'Google Local Services Ads';
    }

    public function getSubheading(): ?string
    {
        return 'Spend, charged leads, calls, bookings, lead quality and campaign performance.';
    }

    public function filtersForm(
        Schema $schema
    ): Schema {
        return $schema
            ->components([
                Section::make()
                    ->schema([

                        DatePicker::make(
                            'startDate'
                        )
                            ->label('From')
                            ->default(
                                now()
                                    ->subDays(29)
                                    ->toDateString()
                            )
                            ->native(false),

                        DatePicker::make(
                            'endDate'
                        )
                            ->label('To')
                            ->default(
                                now()
                                    ->toDateString()
                            )
                            ->native(false),

                        Select::make(
                            'customerId'
                        )
                            ->label(
                                'Google Ads Account'
                            )
                            ->options(
                                GoogleAdsCustomer::query()
                                    ->where(
                                        'is_active',
                                        true
                                    )
                                    ->where(
                                        'is_manager',
                                        false
                                    )
                                    ->orderByDesc(
                                        'is_primary'
                                    )
                                    ->orderBy(
                                        'descriptive_name'
                                    )
                                    ->get()
                                    ->mapWithKeys(
                                        fn (
                                            GoogleAdsCustomer $customer
                                        ) => [
                                            $customer->id =>
                                                sprintf(
                                                    '%s (%s)',
                                                    $customer
                                                        ->descriptive_name
                                                        ?? 'Google Ads',

                                                    $customer
                                                        ->customer_id
                                                ),
                                        ]
                                    )
                                    ->all()
                            )
                            ->default(
                                fn () =>
                                    GoogleAdsCustomer::query()
                                        ->where(
                                            'is_active',
                                            true
                                        )
                                        ->where(
                                            'is_manager',
                                            false
                                        )
                                        ->orderByDesc(
                                            'is_primary'
                                        )
                                        ->value('id')
                            )
                            ->live()
                            ->searchable()
                            ->selectablePlaceholder(
                                false
                            ),

                        Select::make(
                            'campaignId'
                        )
                            ->label(
                                'LSA Campaign'
                            )
                            ->options(
                                GoogleAdsCampaign::query()
                                    ->where(
                                        'is_local_services',
                                        true
                                    )
                                    ->where(
                                        'is_active',
                                        true
                                    )
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(
                                        fn (
                                            GoogleAdsCampaign $campaign
                                        ) => [
                                            $campaign->id =>
                                                sprintf(
                                                    '%s (%s)',
                                                    $campaign
                                                        ->name
                                                        ?? 'Local Services',

                                                    $campaign
                                                        ->campaign_id
                                                ),
                                        ]
                                    )
                                    ->all()
                            )
                            ->default(
                                fn () =>
                                    GoogleAdsCampaign::query()
                                        ->where(
                                            'is_local_services',
                                            true
                                        )
                                        ->where(
                                            'is_active',
                                            true
                                        )
                                        ->value('id')
                            )
                            ->searchable()
                            ->placeholder(
                                'All LSA campaigns'
                            ),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 4,
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function getWidgets(): array
    {
        return [
            LsaStatsOverview::class,
            LsaPerformanceChart::class,
            LsaCampaignPerformanceChart::class,
            LsaLeadStatusChart::class,
            LsaLeadsTable::class,
            LsaCallsTable::class,
        ];
    }

    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'lg' => 2,
        ];
    }
}