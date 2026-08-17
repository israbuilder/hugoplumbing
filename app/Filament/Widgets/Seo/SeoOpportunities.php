<?php

namespace App\Filament\Widgets\Seo;

use App\Models\SearchConsoleSite;
use App\Services\Analytics\SeoAnalyticsService;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class SeoOpportunities extends Widget
{
    use InteractsWithPageFilters;

    protected string $view =
        'filament.widgets.seo.opportunities';

    protected int|string|array $columnSpan =
        'full';

    protected static ?int $sort = 40;

    public function getRows(): Collection
    {
        $siteId = $this->siteId();

        if (!$siteId) {
            return collect();
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
                ?? null
        );

        return $service->opportunities(
            $siteId,
            $start,
            $end
        );
    }

    protected function siteId(): ?int
    {
        return (int) (
            $this->pageFilters['siteId']
            ?? SearchConsoleSite::query()
                ->where('is_active', true)
                ->orderByDesc('is_primary')
                ->value('id')
        ) ?: null;
    }

    public function getOpportunity(
        object $row
    ): array {
        $position =
            (float) $row->position;

        $ctr =
            (float) $row->ctr;

        if (
            $position >= 4
            && $position <= 10
        ) {
            return [
                'label' =>
                    'Page 1 opportunity',

                'description' =>
                    'Already ranking on page 1. Improve CTR and content to move into the top 3.',

                'color' =>
                    'success',
            ];
        }

        if (
            $position > 10
            && $position <= 15
        ) {
            return [
                'label' =>
                    'Near page 1',

                'description' =>
                    'Strengthen content, internal links and topical relevance.',

                'color' =>
                    'warning',
            ];
        }

        if (
            $position > 15
            && $position <= 20
        ) {
            return [
                'label' =>
                    'Page 2 potential',

                'description' =>
                    'Review search intent, content depth and internal linking.',

                'color' =>
                    'gray',
            ];
        }

        if ($ctr < 0.01) {
            return [
                'label' =>
                    'Low CTR',

                'description' =>
                    'Consider improving title and meta description.',

                'color' =>
                    'danger',
            ];
        }

        return [
            'label' =>
                'Opportunity',

            'description' =>
                'Review ranking page and search intent.',

            'color' =>
                'gray',
        ];
    }
}