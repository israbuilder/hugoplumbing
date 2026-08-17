<?php

namespace App\Filament\Widgets\Seo;

use App\Models\AnalyticsProperty;
use App\Models\SearchConsoleSite;
use App\Services\Analytics\OrganicPerformanceService;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class OrganicLandingPagesTable extends Widget
{
    use InteractsWithPageFilters;

    protected string $view =
        'filament.widgets.seo.organic-landing-pages';

    protected int|string|array $columnSpan =
        'full';

    public function getRows(): Collection
    {
        $service =
            app(
                OrganicPerformanceService::class
            );

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
            ->landingPages(
                siteId:
                    (int)
                    (
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
                            ->value('id')
                    ),

                propertyId:
                    (int)
                    (
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
                            ->value('id')
                    ),

                start:
                    $start,

                end:
                    $end,

                limit:
                    50
            );
    }
}