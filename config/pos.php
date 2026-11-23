<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Held bills
    |--------------------------------------------------------------------------
    | A held bill is a cart that was paused and can be finished later.
    | max      : most held bills one user may keep at the same time
    | ttl_days : a held bill is deleted automatically after this many days
    */
    'held_bills' => [
        'max'      => (int) env('POS_HELD_MAX', 20),
        'ttl_days' => (int) env('POS_HELD_TTL_DAYS', 3),
    ],

    'returns' => [
        'window_days' => (int) env('POS_RETURN_WINDOW_DAYS', 0),
    ],

];