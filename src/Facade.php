<?php
/**
 * Created by PhpStorm.
 * User: firey
 * Date: 11/1/2017
 * Time: 5:05 PM
 */

namespace Fireynis\LaravelCart;


class Facade extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor()
    {
        return Cart::class;
    }
}