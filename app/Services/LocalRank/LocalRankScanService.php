<?php

namespace App\Services\LocalRank;

use App\Jobs\LocalRank\SubmitLocalRankScan;
use App\Models\LocalRankKeyword;
use App\Models\LocalRankScan;
use Illuminate\Support\Facades\DB;

class LocalRankScanService
{
    public function __construct(
        protected GridGeneratorService $gridGenerator
    ) {
    }

    public function create(
        LocalRankKeyword $keyword,
        ?int $gridSize = null,
        ?float $radiusMiles = null,
        ?int $zoom = null
    ): LocalRankScan {
        $keyword->loadMissing('location');

        $location = $keyword->location;

        $gridSize ??= $keyword->default_grid_size
            ?: config('local-rank.default_grid', 5);

        $radiusMiles ??= $keyword->default_radius_miles
            ?: config('local-rank.default_radius', 5);

        $zoom ??= $keyword->zoom
            ?: config('local-rank.default_zoom', 15);

        $points = $this->gridGenerator->generate(
            centerLatitude: $location->latitude,
            centerLongitude: $location->longitude,
            gridSize: $gridSize,
            radiusMiles: $radiusMiles
        );

        return DB::transaction(function () use (
            $keyword,
            $location,
            $gridSize,
            $radiusMiles,
            $zoom,
            $points
        ) {
            $scan = LocalRankScan::create([
                'local_rank_location_id' => $location->id,
                'local_rank_keyword_id' => $keyword->id,

                'status' => 'pending',

                'grid_size' => $gridSize,
                'radius_miles' => $radiusMiles,
                'zoom' => $zoom,

                'center_latitude' => $location->latitude,
                'center_longitude' => $location->longitude,

                'total_points' => count($points),

                'started_at' => now(),
            ]);

            foreach ($points as $point) {
                $scan->points()->create([
                    ...$point,
                    'status' => 'pending',
                ]);
            }

            SubmitLocalRankScan::dispatch($scan->id);

            return $scan;
        });
    }
}