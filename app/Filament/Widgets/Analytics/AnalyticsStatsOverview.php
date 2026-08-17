<?php

namespace App\Filament\Widgets\Analytics;

use App\Services\Analytics\GoogleAnalyticsService;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AnalyticsStatsOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected int|string|array $columnSpan =
        'full';

    protected ?string $pollingInterval =
        null;

    protected function getStats(): array
    {
        $service =
            app(
                GoogleAnalyticsService::class
            );

        $propertyId =
            $this->propertyId(
                $service
            );

        if (!$propertyId) {

            return [
                Stat::make(
                    'Users',
                    'No property'
                ),

                Stat::make(
                    'Sessions',
                    'No property'
                ),

                Stat::make(
                    'New Users',
                    'No property'
                ),

                Stat::make(
                    'Engagement',
                    'No property'
                ),

                Stat::make(
                    'Key Events',
                    'No property'
                ),

                Stat::make(
                    'Page Views',
                    'No property'
                ),
            ];
        }

        [
            $start,
            $end
        ] =
            $service
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
                        ?? null
                );

        $comparison =
            $service
                ->comparison(
                    $propertyId,
                    $start,
                    $end
                );

        $current =
            $comparison[
                'current'
            ];

        $changes =
            $comparison[
                'changes'
            ];

        return [

            $this->stat(
                label:
                    'Users',

                value:
                    number_format(
                        $current[
                            'active_users'
                        ]
                    ),

                change:
                    $changes[
                        'active_users'
                    ]
            ),

            $this->stat(
                label:
                    'Sessions',

                value:
                    number_format(
                        $current[
                            'sessions'
                        ]
                    ),

                change:
                    $changes[
                        'sessions'
                    ]
            ),

            $this->stat(
                label:
                    'New Users',

                value:
                    number_format(
                        $current[
                            'new_users'
                        ]
                    ),

                change:
                    $changes[
                        'new_users'
                    ]
            ),

            $this->stat(
                label:
                    'Engagement Rate',

                value:
                    number_format(
                        $current[
                            'engagement_rate'
                        ]
                        * 100,
                        1
                    )
                    . '%',

                change:
                    $changes[
                        'engagement_rate'
                    ]
            ),

            $this->stat(
                label:
                    'Key Events',

                value:
                    number_format(
                        $current[
                            'key_events'
                        ],
                        0
                    ),

                change:
                    $changes[
                        'key_events'
                    ]
            ),

            $this->stat(
                label:
                    'Page Views',

                value:
                    number_format(
                        $current[
                            'screen_page_views'
                        ]
                    ),

                change:
                    $changes[
                        'screen_page_views'
                    ]
            ),
        ];
    }

    protected function stat(
        string $label,
        string $value,
        float $change
    ): Stat {
        return Stat::make(
            $label,
            $value
        )
            ->description(
                sprintf(
                    '%s%.1f%% vs previous period',
                    $change >= 0
                        ? '+'
                        : '',
                    $change
                )
            )
            ->descriptionIcon(
                $change >= 0
                    ? Heroicon::ArrowTrendingUp
                    : Heroicon::ArrowTrendingDown
            )
            ->color(
                $change >= 0
                    ? 'success'
                    : 'danger'
            );
    }

    protected function propertyId(
        GoogleAnalyticsService $service
    ): ?int {
        $propertyId =
            $this->pageFilters[
                'propertyId'
            ]
            ?? null;

        return $propertyId
            ? (int) $propertyId
            : $service
                ->defaultPropertyId();
    }
}