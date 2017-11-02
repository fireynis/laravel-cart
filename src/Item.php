<?php
/**
 * Created by PhpStorm.
 * User: firey
 * Date: 11/2/2017
 * Time: 2:32 PM
 */

namespace Fireynis\LaravelCart;


use Fireynis\LaravelCart\Contracts\ItemInterface;
use Fireynis\LaravelCart\Exceptions\InvalidClassTypeException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class Item implements Arrayable, Jsonable, ItemInterface
{
    //TODO: store identifier for item.
    private $id, $quantity, $price, $description, $taxable, $taxRate;

    private $modelType;

    public function __construct($id, $quantity, $price, $description, $taxable, $taxrate, $modelType = null)
    {
        $this->id = $id;
        $this->quantity = $quantity;
        $this->price = $price;
        $this->description = $description;
        $this->taxable = $taxable;
        $this->taxRate = $taxrate;
        if (!is_null($modelType)) {
            $this->setModel($modelType);
        }
    }

    public function setTaxable(bool $taxable)
    {
        $this->taxable = $taxable;
    }

    public function setTaxRate(int $rate)
    {
        $this->taxRate = $rate;
    }

    public static function fromArray(array $data)
    {
        return new self($data['id'], $data['quantity'], $data['price'], $data['description'], $data['taxable'], $data['taxRate'], $data['modelType']);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'description' => $this->description,
            'taxable' => $this->taxable,
            'taxRate' => $this->taxRate,
            'modelType' => $this->modelType
        ];
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Obtain the description of the item.
     *
     * @return string
     */
    public function description(): string
    {
        return $this->description;
    }

    /**
     * Returns the price of the item .
     *
     * @return float
     */
    public function price(): float
    {
        return $this->price;
    }

    /**
     * Returns whether or not an item is taxable.
     *
     * @return bool
     */
    public function taxable(): bool
    {
        return $this->taxable;
    }

    /**
     * Returns the primary key that could be used to
     * obtain an object from the database.
     *
     * @return int
     */
    public function id(): int
    {
        return $this->id;
    }

    /**
     * Returns the rate it is taxed at.
     *
     * @return int
     */
    public function taxRate(): int
    {
        return $this->taxRate;
    }

    public function model()
    {
        if (!is_null($this->modelType)) {
            return with(new $this->modelType)->find($this->id);
        }
        return null;
    }

    public function setModel($modelType)
    {
        if (is_string($modelType) && class_exists($modelType)) {
            $this->modelType = $modelType;
        } else {
            throw new InvalidClassTypeException("The class ".$modelType." does not exist");
        }
    }
}