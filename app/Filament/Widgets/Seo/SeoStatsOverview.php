<?php

namespace App\Filament\Widgets\Seo;

use App\Models\SearchConsoleSite;
use App\Services\Analytics\SeoAnalyticsService;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SeoStatsOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $siteId = $this->siteId();

        if (!$siteId) {
            return [
                Stat::make(
                    'Organic Clicks',
                    'No property'
                ),

                Stat::make(
                    'Impressions',
                    'No property'
                ),

                Stat::make(
                    'Organic CTR',
                    'No property'
                ),

                Stat::make(
                    'Average Position',
                    'No property'
                ),
            ];
        }

        $service = app(
            SeoAnalyticsService::class
        );

        [
            $start,
            $end
        ] = $service->resolveDates(
            $this->pageFilters['startDate']
                ?? null,

            $this->pageFilters['endDate']
                ?? null,
        );

        $comparison =
            $service->comparison(
                $siteId,
                $start,
                $end
            );

        $current =
            $comparison['current'];

        $change =
            $comparison['change'];

        return [
            Stat::make(
                'Organic Clicks',
                number_format(
                    $current['clicks']
                )
            )
                ->description(
                    $this->changeLabel(
                        $change['clicks']
                    )
                )
                ->descriptionIcon(
                    $change['clicks'] >= 0
                        ? Heroicon::ArrowTrendingUp
                        : Heroicon::ArrowTrendingDown
                )
                ->color(
                    $change['clicks'] >= 0
                        ? 'success'
                        : 'danger'
                ),

            Stat::make(
                'Impressions',
                number_format(
                    $current['impressions']
                )
            )
                ->description(
                    $this->changeLabel(
                        $change['impressions']
                    )
                )
                ->descriptionIcon(
                    $change['impressions'] >= 0
                        ? Heroicon::ArrowTrendingUp
                        : Heroicon::ArrowTrendingDown
                )
                ->color(
                    $change['impressions'] >= 0
                        ? 'success'
                        : 'danger'
                ),

            Stat::make(
                'Organic CTR',
                number_format(
                    $current['ctr'] * 100,
                    2
                ) . '%'
            )
                ->description(
                    $this->changeLabel(
                        $change['ctr']
                    )
                )
                ->descriptionIcon(
                    $change['ctr'] >= 0
                        ? Heroicon::ArrowTrendingUp
                        : Heroicon::ArrowTrendingDown
                )
                ->color(
                    $change['ctr'] >= 0
                        ? 'success'
                        : 'danger'
                ),

            Stat::make(
                'Average Position',
                number_format(
                    $current['position'],
                    2
                )
            )
                ->description(
                    $this->positionChangeLabel(
                        $change['position']
                    )
                )
                ->descriptionIcon(
                    $change['position'] >= 0
                        ? Heroicon::ArrowTrendingUp
                        : Heroicon::ArrowTrendingDown
                )
                ->color(
                    $change['position'] >= 0
                        ? 'success'
                        : 'danger'
                ),
        ];
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
            ->where('is_active', true)
            ->orderByDesc('is_primary')
            ->value('id');
    }

    protected function changeLabel(
        float $change
    ): string {
        return sprintf(
            '%s%.1f%% vs previous period',
            $change >= 0 ? '+' : '',
            $change
        );
    }

    protected function positionChangeLabel(
        float $change
    ): string {
        if ($change > 0) {
            return sprintf(
                '+%.2f positions improved',
                $change
            );
        }

        if ($change < 0) {
            return sprintf(
                '%.2f positions declined',
                $change
            );
        }

        return 'No position change';
    }
}