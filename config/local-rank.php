<?php

return [

    'provider' => env('LOCAL_RANK_PROVIDER', 'dataforseo'),

    'default_grid' => (int) env('LOCAL_RANK_DEFAULT_GRID', 5),

    'default_radius' => (float) env('LOCAL_RANK_DEFAULT_RADIUS', 5),

    'default_zoom' => (int) env('LOCAL_RANK_DEFAULT_ZOOM', 15),

    'depth' => (int) env('LOCAL_RANK_DEPTH', 100),

    'language_code' => env('LOCAL_RANK_LANGUAGE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Ranking thresholds
    |--------------------------------------------------------------------------
    */

    'top_3' => 3,

    'top_10' => 10,

    'max_tracked_rank' => 100,

];