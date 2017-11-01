<?php
/**
 * Created by PhpStorm.
 * User: firey
 * Date: 11/1/2017
 * Time: 1:02 PM
 */

namespace Fireynis\LaravelCart\Contracts;


interface ItemInterface
{
    /**
     * Obtain the description of the item.
     *
     * @return string
     */
    public function description(): string;

    /**
     * Returns the price of the item .
     *
     * @return float
     */
    public function price(): float;

    /**
     * Returns whether or not an item is taxable.
     *
     * @return bool
     */
    public function taxable(): bool;
}