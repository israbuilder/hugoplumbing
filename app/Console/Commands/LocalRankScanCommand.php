<?php

namespace App\Console\Commands;

use App\Models\LocalRankKeyword;
use App\Models\LocalRankLocation;
use App\Services\LocalRank\LocalRankScanService;
use Illuminate\Console\Command;

class LocalRankScanCommand extends Command
{
    protected $signature = 'local-rank:scan
        {location : Local rank location ID}
        {keyword? : Local rank keyword ID}
        {--grid= : Grid size, example 5}
        {--radius= : Radius in miles}
        {--zoom= : Google Maps zoom}';

    protected $description =
        'Start a Google Maps Local Rank GeoGrid scan';

    public function handle(
        LocalRankScanService $scanService
    ): int {
        $location = LocalRankLocation::findOrFail(
            $this->argument('location')
        );

        $keywordId = $this->argument('keyword');

        $keywords = $keywordId
            ? LocalRankKeyword::query()
                ->where('local_rank_location_id', $location->id)
                ->whereKey($keywordId)
                ->get()
            : $location->keywords()
                ->where('is_active', true)
                ->get();

        if ($keywords->isEmpty()) {
            $this->error(
                'No active keywords found.'
            );

            return self::FAILURE;
        }

        foreach ($keywords as $keyword) {
            $scan = $scanService->create(
                keyword: $keyword,

                gridSize: $this->option('grid')
                    ? (int) $this->option('grid')
                    : null,

                radiusMiles: $this->option('radius')
                    ? (float) $this->option('radius')
                    : null,

                zoom: $this->option('zoom')
                    ? (int) $this->option('zoom')
                    : null
            );

            $this->info(
                "Created scan #{$scan->id}: {$keyword->keyword}"
            );
        }

        return self::SUCCESS;
    }
}