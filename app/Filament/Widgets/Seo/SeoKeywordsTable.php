<?php

namespace App\Filament\Widgets\Seo;

use App\Models\SearchConsoleSite;
use App\Services\Analytics\SeoAnalyticsService;
use App\Services\Analytics\SeoKeywordAnalyticsService;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SeoKeywordsTable extends TableWidget
{
    use InteractsWithPageFilters;
    use InteractsWithTable;

    protected string $view =
        'filament.widgets.seo.keywords-table';

    protected int|string|array $columnSpan =
        'full';

    protected static ?int $sort = 10;

    public function table(
        Table $table
    ): Table {
        return $table
            ->records(
                function (
                    ?string $search,
                    ?string $sortColumn,
                    ?string $sortDirection,
                    int $page,
                    int $recordsPerPage,
                ): LengthAwarePaginator {

                    $siteId =
                        $this->siteId();

                    if (!$siteId) {
                        return new \Illuminate\Pagination\LengthAwarePaginator(
                            [],
                            0,
                            $recordsPerPage,
                            $page
                        );
                    }

                    $analytics =
                        app(
                            SeoAnalyticsService::class
                        );

                    [
                        $start,
                        $end
                    ] =
                        $analytics
                            ->resolveDates(
                                $this
                                    ->pageFilters[
                                        'startDate'
                                    ]
                                    ?? null,

                                $this
                                    ->pageFilters[
                                        'endDate'
                                    ]
                                    ?? null,
                            );

                    $service =
                        app(
                            SeoKeywordAnalyticsService::class
                        );

                    return $service
                        ->paginate(
                            siteId: $siteId,

                            start: $start,

                            end: $end,

                            page: $page,

                            perPage:
                                $recordsPerPage,

                            search:
                                $search,

                            sortColumn:
                                $sortColumn,

                            sortDirection:
                                $sortDirection,

                            positionGroup:
                                $this
                                    ->pageFilters[
                                        'positionGroup'
                                    ]
                                    ?? null,

                            intent:
                                $this
                                    ->pageFilters[
                                        'intent'
                                    ]
                                    ?? 'all',

                            opportunity:
                                $this
                                    ->pageFilters[
                                        'opportunity'
                                    ]
                                    ?? null,
                        );
                }
            )

            ->columns([

                TextColumn::make('query')
                    ->label('Keyword')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->wrap(),

                TextColumn::make('clicks')
                    ->label('Clicks')
                    ->numeric()
                    ->sortable(),

                TextColumn::make(
                    'clicks_change'
                )
                    ->label('Clicks Δ')
                    ->sortable()
                    ->formatStateUsing(
                        fn ($state) =>
                            sprintf(
                                '%+.1f%%',
                                $state
                            )
                    )
                    ->color(
                        fn ($state) =>
                            $state > 0
                                ? 'success'
                                : (
                                    $state < 0
                                        ? 'danger'
                                        : 'gray'
                                )
                    ),

                TextColumn::make(
                    'impressions'
                )
                    ->label('Impressions')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('ctr')
                    ->label('CTR')
                    ->sortable()
                    ->formatStateUsing(
                        fn ($state) =>
                            number_format(
                                $state * 100,
                                2
                            ) . '%'
                    ),

                TextColumn::make(
                    'position'
                )
                    ->label('Position')
                    ->sortable()
                    ->formatStateUsing(
                        fn ($state) =>
                            number_format(
                                $state,
                                1
                            )
                    ),

                TextColumn::make(
                    'position_change'
                )
                    ->label('Position Δ')
                    ->sortable()
                    ->placeholder('New')
                    ->formatStateUsing(
                        function ($state) {

                            if ($state === null) {
                                return 'New';
                            }

                            return sprintf(
                                '%+.1f',
                                $state
                            );
                        }
                    )
                    ->color(
                        function ($state) {

                            if ($state === null) {
                                return 'info';
                            }

                            if ($state > 0) {
                                return 'success';
                            }

                            if ($state < 0) {
                                return 'danger';
                            }

                            return 'gray';
                        }
                    ),

                TextColumn::make(
                    'landing_page'
                )
                    ->label('Landing Page')
                    ->formatStateUsing(
                        function ($state) {

                            if (!$state) {
                                return '—';
                            }

                            return parse_url(
                                $state,
                                PHP_URL_PATH
                            ) ?: '/';
                        }
                    )
                    ->url(
                        fn (
                            array $record
                        ) =>
                            $record[
                                'landing_page'
                            ]
                            ?? null
                    )
                    ->openUrlInNewTab()
                    ->limit(35),

                TextColumn::make(
                    'opportunity'
                )
                    ->label('Status')
                    ->badge()
                    ->color(
                        fn ($state) =>
                            match ($state) {

                                'Growing'
                                    => 'success',

                                'Top 3 opportunity'
                                    => 'success',

                                'Page 1'
                                    => 'info',

                                'Near page 1'
                                    => 'warning',

                                'Low CTR'
                                    => 'warning',

                                'Declining'
                                    => 'danger',

                                default
                                    => 'gray',
                            }
                    ),
            ])

            ->defaultSort(
                'clicks',
                'desc'
            )

            ->paginated([
                10,
                25,
                50,
                100,
            ])

            ->defaultPaginationPageOption(
                25
            )

            ->striped()

            ->emptyStateHeading(
                'No keywords found'
            )

            ->emptyStateDescription(
                'Try changing the date range or Search Console property.'
            );
    }

    protected function siteId(): ?int
    {
        $siteId =
            $this->pageFilters['siteId']
            ?? null;

        if ($siteId) {
            return (int) $siteId;
        }

        return SearchConsoleSite::query()
            ->where(
                'is_active',
                true
            )
            ->orderByDesc(
                'is_primary'
            )
            ->value('id');
    }
}