<?php

namespace App\Filament\Widgets\Seo;

use App\Models\AnalyticsProperty;
use App\Models\SearchConsoleSite;
use App\Services\Analytics\OrganicPerformanceService;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrganicPerformanceStats extends StatsOverviewWidget
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
                OrganicPerformanceService::class
            );

        [
            $siteId,
            $propertyId,
        ] =
            $this->ids();

        if (
            !$siteId
            || !$propertyId
        ) {
            return [
                Stat::make(
                    'Organic Clicks',
                    'No data'
                ),

                Stat::make(
                    'Organic Sessions',
                    'No data'
                ),

                Stat::make(
                    'Key Events',
                    'No data'
                ),

                Stat::make(
                    'Engagement',
                    'No data'
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

        $data =
            $service
                ->summary(
                    $siteId,
                    $propertyId,
                    $start,
                    $end
                );

        return [

            Stat::make(
                'Organic Clicks',
                number_format(
                    $data['clicks']
                )
            )
                ->description(
                    number_format(
                        $data['impressions']
                    )
                    . ' impressions'
                ),

            Stat::make(
                'Organic Sessions',
                number_format(
                    $data['sessions']
                )
            )
                ->description(
                    number_format(
                        $data[
                            'session_click_ratio'
                        ]
                        * 100,
                        1
                    )
                    . '% of SC clicks'
                ),

            Stat::make(
                'Key Events',
                number_format(
                    $data[
                        'key_events'
                    ],
                    0
                )
            )
                ->description(
                    number_format(
                        $data[
                            'key_event_rate'
                        ]
                        * 100,
                        1
                    )
                    . '% session rate'
                ),

            Stat::make(
                'Engagement Rate',
                number_format(
                    $data[
                        'engagement_rate'
                    ]
                    * 100,
                    1
                )
                . '%'
            )
                ->description(
                    number_format(
                        $data[
                            'engaged_sessions'
                        ]
                    )
                    . ' engaged sessions'
                ),
        ];
    }

    protected function ids(): array
    {
        $siteId =
            $this->pageFilters[
                'searchConsoleSiteId'
            ]
            ?? SearchConsoleSite::query()
                ->where(
                    'is_active',
                    true
                )
                ->orderByDesc(
                    'is_primary'
                )
                ->value('id');

        $propertyId =
            $this->pageFilters[
                'analyticsPropertyId'
            ]
            ?? AnalyticsProperty::query()
                ->where(
                    'is_active',
                    true
                )
                ->orderByDesc(
                    'is_primary'
                )
                ->value('id');

        return [
            $siteId
                ? (int) $siteId
                : null,

            $propertyId
                ? (int) $propertyId
                : null,
        ];
    }
}