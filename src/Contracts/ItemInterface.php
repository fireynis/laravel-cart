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

    /**
     * Returns the rate it is taxed at.
     *
     * @return int
     */
    public function taxRate(): int;

    /**
     * Returns the primary key that could be used to
     * obtain an object from the database.
     *
     * @return int
     */
    public function uniqueId(): int;

    /**
     * Returns the cost per item for shipping.
     *
     * @return float
     */
    public function shipping(): float;
}