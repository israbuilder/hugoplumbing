<?php

namespace App\Filament\Pages;

use App\Models\LocalRankKeyword;
use App\Models\LocalRankLocation;
use App\Models\LocalRankScan;
use App\Services\LocalRank\LocalRankScanService;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Pages\Page;
use UnitEnum;
use BackedEnum;

class LocalRankGrid extends Page
{   
        protected static string|BackedEnum|null $navigationIcon =
        Heroicon::Map;

    protected static ?string $navigationLabel = 'Local Rank Grid';

    protected static ?string $title = 'Local Rank Grid';

    protected static string|UnitEnum|null $navigationGroup =
        'Local SEO';

    protected static ?int $navigationSort = 10;

    protected string $view =
        'filament.pages.local-rank-grid';

    public ?int $locationId = null;

    public ?int $keywordId = null;

    public ?int $scanId = null;

    public int $gridSize = 5;

    public float $radiusMiles = 5;

    public int $zoom = 15;

    public array $mapData = [];

    public function mount(): void
    {
        $location = LocalRankLocation::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->first();

        if (!$location) {
            return;
        }

        $this->locationId = $location->id;

        $keyword = $location->keywords()
            ->where('is_active', true)
            ->orderBy('keyword')
            ->first();

        if ($keyword) {
            $this->keywordId = $keyword->id;

            $this->gridSize =
                $keyword->default_grid_size;

            $this->radiusMiles =
                $keyword->default_radius_miles;

            $this->zoom =
                $keyword->zoom;

            $latest = $keyword->scans()
                ->latest('id')
                ->first();

            if ($latest) {
                $this->scanId = $latest->id;
            }
        }

        $this->loadMap();
    }

    public function updatedLocationId(): void
    {
        $keyword = LocalRankKeyword::query()
            ->where(
                'local_rank_location_id',
                $this->locationId
            )
            ->where('is_active', true)
            ->orderBy('keyword')
            ->first();

        $this->keywordId = $keyword?->id;

        if ($keyword) {
            $this->gridSize =
                $keyword->default_grid_size;

            $this->radiusMiles =
                $keyword->default_radius_miles;

            $this->zoom =
                $keyword->zoom;
        }

        $this->loadLatestScan();
    }

    public function updatedKeywordId(): void
    {
        $keyword = LocalRankKeyword::find(
            $this->keywordId
        );

        if ($keyword) {
            $this->gridSize =
                $keyword->default_grid_size;

            $this->radiusMiles =
                $keyword->default_radius_miles;

            $this->zoom =
                $keyword->zoom;
        }

        $this->loadLatestScan();
    }

    public function updatedScanId(): void
    {
        $this->loadMap();
    }

    public function loadLatestScan(): void
    {
        if (!$this->keywordId) {
            $this->scanId = null;
            $this->mapData = [];

            $this->dispatchMap();

            return;
        }

        $latest = LocalRankScan::query()
            ->where(
                'local_rank_keyword_id',
                $this->keywordId
            )
            ->latest('id')
            ->first();

        $this->scanId = $latest?->id;

        $this->loadMap();
    }

    public function runScan(
        LocalRankScanService $service
    ): void {
        if (!$this->keywordId) {
            Notification::make()
                ->title('Select a keyword')
                ->danger()
                ->send();

            return;
        }

        if (
            $this->gridSize < 3 ||
            $this->gridSize > 13 ||
            $this->gridSize % 2 === 0
        ) {
            Notification::make()
                ->title('Invalid grid size')
                ->body(
                    'Use 3, 5, 7, 9, 11 or 13.'
                )
                ->danger()
                ->send();

            return;
        }

        $keyword = LocalRankKeyword::findOrFail(
            $this->keywordId
        );

        $scan = $service->create(
            keyword: $keyword,
            gridSize: $this->gridSize,
            radiusMiles: $this->radiusMiles,
            zoom: $this->zoom
        );

        $this->scanId = $scan->id;

        Notification::make()
            ->title('GeoGrid scan started')
            ->body(
                "{$scan->total_points} search points created."
            )
            ->success()
            ->send();

        $this->loadMap();
    }

    public function refreshScan(): void
    {
        $this->loadMap();
    }

    public function loadMap(): void
    {
        if (!$this->scanId) {
            $this->mapData = [];
            $this->dispatchMap();

            return;
        }

        $scan = LocalRankScan::with([
            'location',
            'keyword',
            'points.result',
        ])->find($this->scanId);

        if (!$scan) {
            return;
        }

        $this->mapData = [
            'scan' => [
                'id' => $scan->id,
                'status' => $scan->status,

                'keyword' =>
                    $scan->keyword->keyword,

                'grid_size' =>
                    $scan->grid_size,

                'radius_miles' =>
                    $scan->radius_miles,

                'center_latitude' =>
                    $scan->center_latitude,

                'center_longitude' =>
                    $scan->center_longitude,

                'average_rank' =>
                    $scan->average_rank,

                'top_3_percentage' =>
                    $scan->top_3_percentage,

                'top_10_percentage' =>
                    $scan->top_10_percentage,

                'visibility_score' =>
                    $scan->visibility_score,

                'completed_points' =>
                    $scan->completed_points,

                'total_points' =>
                    $scan->total_points,

                'provider_cost' =>
                    $scan->provider_cost,

                'created_at' =>
                    $scan->created_at?->format(
                        'M j, Y g:i A'
                    ),
            ],

            'location' => [
                'name' => $scan->location->name,

                'latitude' =>
                    $scan->location->latitude,

                'longitude' =>
                    $scan->location->longitude,
            ],

            'points' => $scan->points
                ->sortBy([
                    ['row', 'asc'],
                    ['column', 'asc'],
                ])
                ->map(function ($point) {
                    $result = $point->result;

                    return [
                        'id' => $point->id,

                        'row' => $point->row,
                        'column' => $point->column,

                        'latitude' =>
                            $point->latitude,

                        'longitude' =>
                            $point->longitude,

                        'distance_miles' =>
                            $point->distance_miles,

                        'is_center' =>
                            $point->is_center,

                        'status' =>
                            $point->status,

                        'found' =>
                            $result?->found ?? false,

                        'rank' =>
                            $result?->rank,

                        'business_name' =>
                            $result?->business_name,

                        'competitors' =>
                            collect(
                                $result?->items ?? []
                            )
                                ->take(10)
                                ->map(fn ($item) => [
                                    'rank' =>
                                        $item['rank_group'] ?? null,

                                    'name' =>
                                        $item['title'] ?? null,

                                    'rating' =>
                                        data_get(
                                            $item,
                                            'rating.value'
                                        ),

                                    'reviews' =>
                                        data_get(
                                            $item,
                                            'rating.votes_count'
                                        ),

                                    'category' =>
                                        $item['category'] ?? null,

                                    'place_id' =>
                                        $item['place_id'] ?? null,
                                ])
                                ->values()
                                ->all(),
                    ];
                })
                ->values()
                ->all(),
        ];

        $this->dispatchMap();
    }

    protected function dispatchMap(): void
    {
        $this->dispatch(
            'local-rank-map-updated',
            data: $this->mapData
        );
    }

    public function getLocationsProperty()
    {
        return LocalRankLocation::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getKeywordsProperty()
    {
        if (!$this->locationId) {
            return collect();
        }

        return LocalRankKeyword::query()
            ->where(
                'local_rank_location_id',
                $this->locationId
            )
            ->where('is_active', true)
            ->orderBy('keyword')
            ->get();
    }

    public function getScansProperty()
    {
        if (!$this->keywordId) {
            return collect();
        }

        return LocalRankScan::query()
            ->where(
                'local_rank_keyword_id',
                $this->keywordId
            )
            ->latest('id')
            ->limit(50)
            ->get();
    }
}