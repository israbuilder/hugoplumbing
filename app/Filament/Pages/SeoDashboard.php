<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\Seo\SeoPerformanceChart;
use App\Filament\Widgets\Seo\SeoStatsOverview;
use App\Filament\Widgets\Seo\SeoTopPages;
use App\Filament\Widgets\Seo\SeoTopQueries;
use App\Filament\Widgets\Seo\SeoOpportunities;
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

class SeoDashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static string $routePath = 'seo';

    protected static ?string $title = 'SEO Dashboard';

    protected static ?string $navigationLabel = 'SEO';

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup =
        'Marketing';

    protected static ?int $navigationSort = 10;

    public function getHeading(): string
    {
        return 'SEO Performance';
    }

    public function getSubheading(): ?string
    {
        return 'Google organic search performance, keywords, landing pages and SEO opportunities.';
    }

     public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'lg' => 1,
        ];
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('From')
                            ->default(
                                now()
                                    ->subDays(28)
                                    ->toDateString()
                            )
                            ->maxDate(
                                now()->subDay()
                            )
                            ->native(false),

                        DatePicker::make('endDate')
                            ->label('To')
                            ->default(
                                now()
                                    ->subDay()
                                    ->toDateString()
                            )
                            ->maxDate(
                                now()->subDay()
                            )
                            ->native(false),

                        Select::make('siteId')
                            ->label('Search Console Property')
                            ->options(
                                SearchConsoleSite::query()
                                    ->where(
                                        'is_active',
                                        true
                                    )
                                    ->orderBy('site_url')
                                    ->pluck(
                                        'site_url',
                                        'id'
                                    )
                                    ->all()
                            )
                            ->default(
                                fn () =>
                                    SearchConsoleSite::query()
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
                            ->selectablePlaceholder(false),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' =>3,
                    ])
                     ->columnSpanFull(),
            ]);
    }

    public function getWidgets(): array
    {
        return [
            SeoStatsOverview::class,

            SeoPerformanceChart::class,

            SeoTopQueries::class,

            SeoTopPages::class,

            SeoOpportunities::class,
        ];
    }

   
}