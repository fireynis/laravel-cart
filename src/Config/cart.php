<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tax Rate
    |--------------------------------------------------------------------------
    |
    | This value is the percent tax that you want to charge per item. This is
    | the default rate applied, it can be overridden on each addition of an
    | item to the cart. It is a whole positive number. %13 --> 13.
    |
    */

    'tax_rate' => 13,

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value will set the name of the model class that your products are
    | stored as. When you get an item, it will be of this type on return.
    |
    */

    'associated_model' => null,

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | The following dictate the format the number will take on return. The
    | rounding can be ignored per each call that has a $rounding parameter.
    |
    */

    'number_format' => [
        'decimal_places' => 2,
        'thousand_separator' => " ",
        'rounding_preference' => PHP_ROUND_HALF_UP
    ]
];