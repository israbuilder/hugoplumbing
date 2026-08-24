<?php

namespace App\Filament\Widgets\GoogleAds;

use App\Services\Analytics\LsaAnalyticsService;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class LsaCallsTable extends Widget
{
    use InteractsWithPageFilters;

    protected string $view =
        'filament.widgets.google-ads.lsa-calls-table';

    protected int|string|array $columnSpan =
        'full';

    public function getRows(): Collection
    {
        $service =
            app(
                LsaAnalyticsService::class
            );

        $customerId =
            isset(
                $this->pageFilters[
                    'customerId'
                ]
            )
                ? (int)
                    $this->pageFilters[
                        'customerId'
                    ]
                : $service
                    ->defaultCustomerId();

        if (!$customerId) {
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
            ->calls(
                $customerId,
                $start,
                $end,
                50
            );
    }

    public function duration(
        ?int $milliseconds
    ): string {
        return app(
            LsaAnalyticsService::class
        )->formatDuration(
            $milliseconds
        );
    }

    public function quality(
        ?int $milliseconds
    ): array {
        $seconds =
            (
                $milliseconds
                ?? 0
            )
            / 1000;

        if ($seconds >= 120) {
            return [
                'label' =>
                    'Strong',

                'color' =>
                    'success',
            ];
        }

        if ($seconds >= 30) {
            return [
                'label' =>
                    'Connected',

                'color' =>
                    'info',
            ];
        }

        return [
            'label' =>
                'Short',

            'color' =>
                'warning',
        ];
    }
}