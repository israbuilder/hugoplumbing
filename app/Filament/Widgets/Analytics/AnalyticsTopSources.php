<?php

namespace App\Filament\Widgets\Analytics;

use App\Services\Analytics\GoogleAnalyticsService;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class AnalyticsTopSources extends Widget
{
    use InteractsWithPageFilters;

    protected string $view =
        'filament.widgets.analytics.top-sources';

    protected int|string|array $columnSpan =
        1;

    protected static ?int $sort =
        40;

    public function getRows(): Collection
    {
        $service =
            app(
                GoogleAnalyticsService::class
            );

        $propertyId =
            isset(
                $this->pageFilters[
                    'propertyId'
                ]
            )
                ? (int)
                    $this->pageFilters[
                        'propertyId'
                    ]

                : $service
                    ->defaultPropertyId();

        if (!$propertyId) {
            return collect();
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

        return $service
            ->topSources(
                propertyId:
                    $propertyId,

                start:
                    $start,

                end:
                    $end,

                channel:
                    $this
                        ->pageFilters[
                            'channel'
                        ]
                        ?? null,

                limit:
                    15
            );
    }
}