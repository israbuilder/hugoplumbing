<?php

namespace App\Filament\Widgets\Analytics;

use App\Services\Analytics\GoogleAnalyticsService;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class AnalyticsTopLandingPages extends Widget
{
    use InteractsWithPageFilters;

    protected string $view =
        'filament.widgets.analytics.top-landing-pages';

    protected int|string|array $columnSpan =
        1;

    protected static ?int $sort =
        30;

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
            ->topLandingPages(
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