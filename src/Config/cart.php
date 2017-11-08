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
    | Associated Model
    |--------------------------------------------------------------------------
    |
    | This value will set the name of the model class that your products are
    | stored as. When you get an item, it will be of this type on return.
    |
    */

    'associated_model' => null,

    /*
    |--------------------------------------------------------------------------
    | Always store
    |--------------------------------------------------------------------------
    |
    | If you always want the cart to persist in the DB connection you assign,
    | set this to true. Otherwise it will exist in Session until you call
    | the store function.
    |
    */

    'always_store' => false,

    /*
    |--------------------------------------------------------------------------
    | Work in incognito / private browsing
    |--------------------------------------------------------------------------
    |
    | Normally the cart is stored in session, but this will not work for
    | private browsing. Set this to true to use a cookie and persist the cart
    | to the database after every modification.
    |
    */

    'work_in_incognito' => false,

    /*
    |--------------------------------------------------------------------------
    | Number Format
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
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    |
    | This determines which defined database connection will be used to
    | create the tables used for storage of the cart.
    |
    */

    'db_connection' => 'mysql',

    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    |
    | Here you can replace the names of the table to prevent a local clash
    | of your own tables.
    |
    */

    'cart_table_name' => 'carts',
    'items_table_name' => 'cart_items',
];