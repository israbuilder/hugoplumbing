<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\Analytics\AnalyticsChannelChart;
use App\Filament\Widgets\Analytics\AnalyticsStatsOverview;
use App\Filament\Widgets\Analytics\AnalyticsTopEvents;
use App\Filament\Widgets\Analytics\AnalyticsTopLandingPages;
use App\Filament\Widgets\Analytics\AnalyticsTopSources;
use App\Filament\Widgets\Analytics\AnalyticsTrafficChart;
use App\Models\AnalyticsProperty;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class AnalyticsDashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static string $routePath = 'analytics';

    protected static ?string $title = 'Analytics';

    protected static ?string $navigationLabel = 'Analytics';

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedChartPie;

    protected static string|UnitEnum|null $navigationGroup =
        'Marketing';

    protected static ?int $navigationSort = 20;

    protected bool $persistsFiltersInSession = true;

    public function getHeading(): string
    {
        return 'Google Analytics';
    }

    public function getSubheading(): ?string
    {
        return 'Website traffic, acquisition, engagement, landing pages and conversions.';
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
                            ->maxDate(
                                now()
                            )
                            ->native(false),

                        DatePicker::make(
                            'endDate'
                        )
                            ->label('To')
                            ->default(
                                now()
                                    ->subDay()
                                    ->toDateString()
                            )
                            ->maxDate(
                                now()
                            )
                            ->native(false),

                        Select::make(
                            'propertyId'
                        )
                            ->label(
                                'GA4 Property'
                            )
                            ->options(
                                AnalyticsProperty::query()
                                    ->where(
                                        'is_active',
                                        true
                                    )
                                    ->orderBy(
                                        'display_name'
                                    )
                                    ->get()
                                    ->mapWithKeys(
                                        fn (
                                            AnalyticsProperty $property
                                        ) => [
                                            $property->id =>
                                                sprintf(
                                                    '%s (%s)',
                                                    $property->display_name,
                                                    $property->property_id
                                                ),
                                        ]
                                    )
                                    ->all()
                            )
                            ->default(
                                fn () =>
                                    AnalyticsProperty::query()
                                        ->where(
                                            'is_active',
                                            true
                                        )
                                        ->orderByDesc(
                                            'is_primary'
                                        )
                                        ->value('id')
                            )
                            ->searchable()
                            ->selectablePlaceholder(
                                false
                            ),

                        Select::make(
                            'channel'
                        )
                            ->label('Channel')
                            ->options([
                                'Organic Search' =>
                                    'Organic Search',

                                'Paid Search' =>
                                    'Paid Search',

                                'Direct' =>
                                    'Direct',

                                'Referral' =>
                                    'Referral',

                                'Organic Social' =>
                                    'Organic Social',

                                'Paid Social' =>
                                    'Paid Social',

                                'Email' =>
                                    'Email',

                                'Display' =>
                                    'Display',
                            ])
                            ->placeholder(
                                'All channels'
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
            AnalyticsStatsOverview::class,

            AnalyticsTrafficChart::class,

            AnalyticsChannelChart::class,

            AnalyticsTopLandingPages::class,

            AnalyticsTopSources::class,

            AnalyticsTopEvents::class,
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