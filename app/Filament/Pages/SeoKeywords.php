<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\Seo\SeoKeywordsTable;
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

class SeoKeywords extends BaseDashboard
{
    use HasFiltersForm;

    protected static string $routePath = 'seo/keywords';

    protected static ?string $title = 'SEO Keywords';

    protected static ?string $navigationLabel = 'Keywords';

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedMagnifyingGlass;

    protected static string|UnitEnum|null $navigationGroup =
        'Marketing';

    protected static ?int $navigationSort = 11;

    public function getHeading(): string
    {
        return 'SEO Keywords';
    }

    public function getSubheading(): ?string
    {
        return 'Search queries, ranking changes, traffic and SEO opportunities.';
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
                            ->native(false),

                        DatePicker::make('endDate')
                            ->label('To')
                            ->default(
                                now()
                                    ->subDay()
                                    ->toDateString()
                            )
                            ->native(false),

                        Select::make('siteId')
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

                        Select::make('positionGroup')
                            ->label('Ranking')
                            ->options([
                                'top_3' => 'Top 3',
                                'top_10' => 'Top 10',
                                'top_20' => 'Top 20',
                                'page_2' => 'Positions 11–20',
                                'outside_20' => 'Outside Top 20',
                            ])
                            ->placeholder('All rankings'),

                        Select::make('intent')
                            ->label('Keyword type')
                            ->options([
                                'all' => 'All',
                                'branded' => 'Branded',
                                'non_branded' => 'Non-branded',
                            ])
                            ->default('all'),

                        Select::make('opportunity')
                            ->label('Opportunity')
                            ->options([
                                'top_3' => 'Close to Top 3',
                                'page_1' => 'Page 1',
                                'page_2' => 'Near Page 1',
                                'low_ctr' => 'Low CTR',
                                'declining' => 'Declining',
                                'growing' => 'Growing',
                            ])
                            ->placeholder('All opportunities'),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 3,
                        'xl' => 6,
                    ])
                     ->columnSpanFull(),
            ]);
    }

    public function getWidgets(): array
    {
        return [
            SeoKeywordsTable::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 1;
    }
}