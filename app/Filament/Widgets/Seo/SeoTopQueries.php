<?php

namespace App\Filament\Widgets\Seo;

use App\Models\SearchConsoleSite;
use App\Services\Analytics\SeoAnalyticsService;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class SeoTopQueries extends Widget
{
    use InteractsWithPageFilters;

    protected string $view =
        'filament.widgets.seo.top-queries';

    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 20;

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

        return $service->topQueries(
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
}