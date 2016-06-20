<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Per page
    |--------------------------------------------------------------------------
    |
    | Basic pagination count.
    |
    */

    'per_page' => 15,

    /*
    |--------------------------------------------------------------------------
    | Per row
    |--------------------------------------------------------------------------
    |
    | Default number of items per row
    |
    */

    'per_row'  => [

        'default' =>  4,       // Number of products in one row (fallback)
        'xs' => 2,              // Number of products in one row (xs)
        'sm' => 3,              // Number of products in one row (sm)
        'md' => 4,              // Number of products in one row (md)
        'lg' => 6,              // Number of products in one row (lg)

    ],

    /*
    |--------------------------------------------------------------------------
    | Cols
    |--------------------------------------------------------------------------
    |
    | Number of cols in layout
    |
    */

    'cols' => 12,

];
