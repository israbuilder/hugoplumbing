<?php

namespace App\Services\LocalRank;

use App\Models\LocalRankScan;

class LocalRankMetricsService
{
    public function refreshScan(
        int $scanId
    ): LocalRankScan {
        $scan = LocalRankScan::with([
            'points.result',
        ])->findOrFail($scanId);

        $completed = $scan->points
            ->where('status', 'completed');

        $failed = $scan->points
            ->where('status', 'failed');

        $results = $completed
            ->pluck('result')
            ->filter();

        $ranks = $results
            ->filter(
                fn ($result) =>
                    $result->found &&
                    $result->rank !== null
            )
            ->pluck('rank');

        $totalPoints = max(
            $scan->total_points,
            1
        );

        /*
         * When not found, treat as max rank + 1
         * for average-rank purposes.
         */
        $maxTracked =
            config('local-rank.max_tracked_rank', 100);

            $foundResults = $results->filter(fn ($result) =>
                $result->found &&
                $result->rank !== null
        );

                $averageRank = $foundResults->isNotEmpty()
                    ? round(
                        $foundResults->avg('rank'),
                        2
                    )
                    : null;

                    $coveragePercentage = round(
            (
                $foundResults->count()
                / $totalPoints
            ) * 100,
            2
        );

        $top3Count = $ranks->filter(
            fn ($rank) => $rank <= 3
        )->count();

        $top10Count = $ranks->filter(
            fn ($rank) => $rank <= 10
        )->count();

        /*
         * Percentage is based on all grid points,
         * including "not found".
         */
        $top3Percentage = round(
            ($top3Count / $totalPoints) * 100,
            2
        );

        $top10Percentage = round(
            ($top10Count / $totalPoints) * 100,
            2
        );

        $visibilityTotal = 0;

        foreach ($results as $result) {
            $visibilityTotal += $this->rankScore(
                $result->rank
            );
        }

        /*
         * Max 100 points per grid point.
         */
        $visibilityScore = round(
            $visibilityTotal / $totalPoints,
            2
        );

        $finishedCount =
            $completed->count() +
            $failed->count();

        $isComplete =
            $finishedCount >= $scan->total_points;

        $scan->update([
            'completed_points' => $completed->count(),
            'failed_points' => $failed->count(),

            'average_rank' => $averageRank,
            'coverage_percentage' => $coveragePercentage,
            'top_3_percentage' => $top3Percentage,
            'top_10_percentage' => $top10Percentage,
            'visibility_score' => $visibilityScore,

            'status' =>
                $isComplete
                    ? 'completed'
                    : 'processing',

            'completed_at' =>
                $isComplete
                    ? now()
                    : null,
        ]);

        return $scan->fresh();
    }

    public function rankScore(
        ?int $rank
    ): float {
        if (!$rank) {
            return 0;
        }

        return match (true) {
            $rank === 1 => 100,
            $rank === 2 => 90,
            $rank === 3 => 80,
            $rank === 4 => 70,
            $rank === 5 => 60,
            $rank === 6 => 50,
            $rank === 7 => 40,
            $rank === 8 => 35,
            $rank === 9 => 30,
            $rank === 10 => 25,

            $rank <= 12 => 20,
            $rank <= 15 => 15,
            $rank <= 20 => 10,

            $rank <= 30 => 5,

            default => 0,
        };
    }
}