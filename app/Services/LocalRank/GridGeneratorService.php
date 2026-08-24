<?php

namespace App\Services\LocalRank;

use InvalidArgumentException;

class GridGeneratorService
{
    public function generate(
        float $centerLatitude,
        float $centerLongitude,
        int $gridSize = 5,
        float $radiusMiles = 5
    ): array {
        if ($gridSize < 3) {
            throw new InvalidArgumentException(
                'Grid size must be at least 3.'
            );
        }

        if ($gridSize % 2 === 0) {
            throw new InvalidArgumentException(
                'Grid size must be an odd number.'
            );
        }

        if ($radiusMiles <= 0) {
            throw new InvalidArgumentException(
                'Radius must be greater than zero.'
            );
        }

        /*
         * 1 degree latitude ≈ 69 miles.
         */
        $latitudeRadius = $radiusMiles / 69.0;

        /*
         * Longitude changes depending on latitude.
         */
        $longitudeMilesPerDegree =
            69.172 *
            cos(deg2rad($centerLatitude));

        $longitudeRadius =
            $radiusMiles /
            max($longitudeMilesPerDegree, 0.00001);

        $latStep = ($latitudeRadius * 2) / ($gridSize - 1);
        $lngStep = ($longitudeRadius * 2) / ($gridSize - 1);

        $centerIndex = intdiv($gridSize, 2);

        $points = [];

        for ($row = 0; $row < $gridSize; $row++) {
            /*
             * Top row should be north.
             */
            $latitude =
                $centerLatitude +
                $latitudeRadius -
                ($row * $latStep);

            for ($column = 0; $column < $gridSize; $column++) {
                $longitude =
                    $centerLongitude -
                    $longitudeRadius +
                    ($column * $lngStep);

                $distance = $this->distanceMiles(
                    $centerLatitude,
                    $centerLongitude,
                    $latitude,
                    $longitude
                );

                $points[] = [
                    'row' => $row,
                    'column' => $column,

                    'latitude' => round($latitude, 7),
                    'longitude' => round($longitude, 7),

                    'distance_miles' => round($distance, 4),

                    'is_center' =>
                        $row === $centerIndex &&
                        $column === $centerIndex,
                ];
            }
        }

        return $points;
    }

    public function distanceMiles(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2
    ): float {
        $earthRadius = 3958.7613;

        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a =
            sin($latDelta / 2) ** 2 +
            cos(deg2rad($lat1)) *
            cos(deg2rad($lat2)) *
            sin($lngDelta / 2) ** 2;

        $c = 2 * atan2(
            sqrt($a),
            sqrt(1 - $a)
        );

        return $earthRadius * $c;
    }
}