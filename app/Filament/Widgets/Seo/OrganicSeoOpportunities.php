<?php

namespace App\Filament\Widgets\Seo;

use App\Models\AnalyticsProperty;
use App\Models\SearchConsoleSite;
use App\Services\Analytics\OrganicPerformanceService;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class OrganicSeoOpportunities extends Widget
{
    use InteractsWithPageFilters;

    protected string $view =
        'filament.widgets.seo.organic-seo-opportunities';

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
            ->opportunities(
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
                    20
            );
    }

    public function opportunityMeta(
        string $opportunity
    ): array {
        return match ($opportunity) {

            'High traffic, low conversion' => [
                'color' =>
                    'danger',

                'description' =>
                    'Organic traffic is arriving but producing relatively few key events.',
            ],

            'High impressions, low CTR' => [
                'color' =>
                    'warning',

                'description' =>
                    'Google visibility is strong but the search result is not earning enough clicks.',
            ],

            'Near top 3' => [
                'color' =>
                    'success',

                'description' =>
                    'The page already ranks close to the top three and may respond well to SEO improvements.',
            ],

            'Traffic mismatch' => [
                'color' =>
                    'warning',

                'description' =>
                    'Search Console clicks are much higher than GA4 organic sessions. Review tracking and URL normalization.',
            ],

            default => [
                'color' =>
                    'gray',

                'description' =>
                    'Monitor this page.',
            ],
        };
    }
}