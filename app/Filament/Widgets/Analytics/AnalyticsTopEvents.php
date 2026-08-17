<?php

namespace App\Filament\Widgets\Analytics;

use App\Services\Analytics\GoogleAnalyticsService;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class AnalyticsTopEvents extends Widget
{
    use InteractsWithPageFilters;

    protected string $view =
        'filament.widgets.analytics.top-events';

    protected int|string|array $columnSpan =
        'full';

    protected static ?int $sort =
        50;

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
            ->topEvents(
                propertyId:
                    $propertyId,

                start:
                    $start,

                end:
                    $end,

                limit:
                    20
            );
    }

    public function eventType(
        string $event
    ): array {
        return match ($event) {

            'phone_click',
            'form_submit',
            'generate_lead',
            'schedule_click' => [

                'label' =>
                    'Conversion',

                'color' =>
                    'success',
            ],

            'page_view',
            'session_start',
            'first_visit' => [

                'label' =>
                    'Traffic',

                'color' =>
                    'info',
            ],

            'scroll',
            'user_engagement',
            'click' => [

                'label' =>
                    'Engagement',

                'color' =>
                    'warning',
            ],

            default => [

                'label' =>
                    'Event',

                'color' =>
                    'gray',
            ],
        };
    }
}