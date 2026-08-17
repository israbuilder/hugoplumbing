<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\Seo\OrganicLandingPagesTable;
use App\Filament\Widgets\Seo\OrganicPerformanceChart;
use App\Filament\Widgets\Seo\OrganicPerformanceStats;
use App\Filament\Widgets\Seo\OrganicSeoOpportunities;
use App\Models\AnalyticsProperty;
use App\Models\SearchConsoleSite;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class OrganicPerformance extends BaseDashboard
{
    use HasFiltersForm;

    protected static string $routePath = 'seo/organic-performance';

    protected static ?string $title = 'Organic Performance';

    protected static ?string $navigationLabel = 'Organic Performance';

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedPresentationChartLine;

    protected static string|UnitEnum|null $navigationGroup =
        'Marketing';

    protected static ?int $navigationSort = 12;

    protected bool $persistsFiltersInSession = true;

    public function getHeading(): string
    {
        return 'Organic Performance';
    }

    public function getSubheading(): ?string
    {
        return 'Search Console visibility combined with GA4 organic traffic and conversions.';
    }

    public function filtersForm(
        Schema $schema
    ): Schema {
        return $schema
            ->components([
                Section::make()
                    ->schema([

                        DatePicker::make('startDate')
                            ->label('From')
                            ->default(
                                now()
                                    ->subDays(29)
                                    ->toDateString()
                            )
                            ->native(false),

                        DatePicker::make('endDate')
                            ->label('To')
                            ->default(
                                now()
                                    ->subDay()
                                    ->toDateString()
                            )
                            ->native(false),

                        Select::make('searchConsoleSiteId')
                            ->label('Search Console Property')
                            ->options(
                                SearchConsoleSite::query()
                                    ->where('is_active', true)
                                    ->orderBy('site_url')
                                    ->pluck('site_url', 'id')
                                    ->all()
                            )
                            ->default(
                                fn () =>
                                    SearchConsoleSite::query()
                                        ->where('is_active', true)
                                        ->orderByDesc('is_primary')
                                        ->value('id')
                            )
                            ->searchable()
                            ->selectablePlaceholder(false),

                        Select::make('analyticsPropertyId')
                            ->label('GA4 Property')
                            ->options(
                                AnalyticsProperty::query()
                                    ->where('is_active', true)
                                    ->orderBy('display_name')
                                    ->get()
                                    ->mapWithKeys(
                                        fn (AnalyticsProperty $property) => [
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
                                        ->where('is_active', true)
                                        ->orderByDesc('is_primary')
                                        ->value('id')
                            )
                            ->searchable()
                            ->selectablePlaceholder(false),
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
            OrganicPerformanceStats::class,
            OrganicPerformanceChart::class,
            OrganicLandingPagesTable::class,
            OrganicSeoOpportunities::class,
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