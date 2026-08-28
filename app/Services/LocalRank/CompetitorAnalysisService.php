<?php

namespace App\Services\LocalRank;

use App\Models\LocalRankScan;

class CompetitorAnalysisService
{
    public function analyze(
        LocalRankScan $scan
    ): array {
        $scan->loadMissing([
            'location',
            'results',
        ]);

        $businessPlaceId =
            trim(
                (string)
                $scan->location->google_place_id
            );

        $businessCid =
            trim(
                (string)
                $scan->location->google_cid
            );

        $competitors = [];

        foreach (
            $scan->results as $result
        ) {

            foreach (
                $result->items ?? []
                as $item
            ) {

                if (
                    ($item['type'] ?? null)
                    !== 'maps_search'
                ) {
                    continue;
                }

                $placeId =
                    trim(
                        (string)
                        ($item['place_id'] ?? '')
                    );

                $cid =
                    trim(
                        (string)
                        ($item['cid'] ?? '')
                    );

                /*
                 * Skip ourselves.
                 */
                if (
                    $businessPlaceId !== '' &&
                    $placeId === $businessPlaceId
                ) {
                    continue;
                }

                if (
                    $businessCid !== '' &&
                    $cid === $businessCid
                ) {
                    continue;
                }

                $key =
                    $placeId
                    ?: $cid
                    ?: md5(
                        mb_strtolower(
                            $item['title']
                            ?? 'unknown'
                        )
                    );

                if (
                    !isset(
                        $competitors[$key]
                    )
                ) {
                    $competitors[$key] = [
                        'name' =>
                            $item['title']
                            ?? 'Unknown',

                        'place_id' =>
                            $placeId ?: null,

                        'cid' =>
                            $cid ?: null,

                        'category' =>
                            $item['category']
                            ?? null,

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

                        'appearances' => 0,

                        'rank_sum' => 0,

                        'best_rank' => null,

                        'above_us' => 0,
                    ];
                }

                $rank =
                    (int)
                    ($item['rank_group'] ?? 0);

                if (!$rank) {
                    continue;
                }

                $competitors[$key][
                    'appearances'
                ]++;

                $competitors[$key][
                    'rank_sum'
                ] += $rank;

                if (
                    $competitors[$key][
                        'best_rank'
                    ] === null ||
                    $rank <
                    $competitors[$key][
                        'best_rank'
                    ]
                ) {
                    $competitors[$key][
                        'best_rank'
                    ] = $rank;
                }

                /*
                 * Determine whether this competitor
                 * beat us at this point.
                 */
                $ourRank =
                    $result->rank;

                if (
                    $ourRank === null ||
                    $rank < $ourRank
                ) {
                    $competitors[$key][
                        'above_us'
                    ]++;
                }
            }
        }

        foreach (
            $competitors as &$competitor
        ) {

            $competitor['average_rank'] =
                $competitor['appearances'] > 0
                    ? round(
                        $competitor['rank_sum']
                        /
                        $competitor['appearances'],
                        2
                    )
                    : null;

            unset(
                $competitor['rank_sum']
            );
        }

        unset($competitor);

        return collect(
            $competitors
        )
            ->sortByDesc(
                'appearances'
            )
            ->values()
            ->all();
    }
}