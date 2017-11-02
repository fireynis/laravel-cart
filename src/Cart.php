<?php

namespace Fireynis\LaravelCart;

use Fireynis\LaravelCart\Contracts\ItemInterface;
use Fireynis\LaravelCart\Exceptions\InvalidCartDataException;
use Illuminate\Support\Collection;

class Cart
{

    private $items;

    private $name;

    private $defaultTaxRate;

    //Values don't matter, just the keys. This means I don't have to array_flip each
    //time I want to validate the incoming array.
    private $mandatoryKeys = [
        "id" => 1,
        "description" => 1,
        "price" => 1,
        "quantity" => 1
    ];

    /**
     *
     */
    public function __construct()
    {
        if (auth()->guest()) {
            $this->name = 'cart';
        } else {
            $this->name = auth()->user()->getAuthIdentifier();
        }

        $this->defaultTaxRate = config('cart.tax_rate');

        $items = \Session::get($this->name . '.items', null);
        if (is_null($items)) {
            $this->items = new Collection();
        } else {
//            $cartItems = [];
//            foreach ($items as $item) {
//                $cartItems[] = Item::fromArray($item);
//            }
            $this->items = collect($items);
        }
    }

    public function add($itemData, bool $overrideTaxable = false, bool $taxable = true, $overrideTaxRate = false, $taxRate = 13)
    {

        if (is_array($itemData) && $this->isMulti($itemData)) {
            foreach ($itemData as $itemDatum) {
                $this->add($itemDatum);
            }
            return;
        } else {

            if (array_key_exists('item', $itemData) && $itemData['item'] instanceof Item) {
                $item = $itemData['item'];
            } elseif (array_key_exists('item', $itemData) && $itemData['item'] instanceof ItemInterface) {
                $model = $itemData['item'];
                $modelType = get_class($model);
                $item = new Item($model->id(), $itemData['quantity'], $model->price(), $model->description(), $model->taxable(), $this->defaultTaxRate, $modelType);
            } elseif (is_array($itemData)) {
                $this->validate($itemData);
                $item = new Item($itemData['id'], $itemData['quantity'], $itemData['price'], $itemData['description'], $taxable, $this->defaultTaxRate, config('cart.associated_model'));
            } else {
                throw new InvalidCartDataException("The data provided is not a model or array.");
            }

            if ($overrideTaxable) {
                $item->setTaxable($taxable);
            }
            if ($overrideTaxRate) {
                $item->setTaxRate($taxRate);
            }

            $this->items[uniqid()] = $item;
            $this->itemsToSession();
        }
    }

    public function update(string $identifier, int $quantity)
    {
        if ($this->items->has($identifier)) {
            $this->items->get($identifier)['quantity'] = $quantity;
        }
        $this->itemsToSession();
    }

    public function get(string $identifier)
    {
        return $this->items->get($identifier);
    }

    public function remove(string $identifier)
    {
        if ($this->items->has($identifier)) {
            $this->items->forget($identifier);
        }
        $this->itemsToSession();
    }

    public function subTotal(bool $rounded = true): float
    {
        $subTotal = 0.00;
        foreach ($this->items as $item) {
            $subTotal += $item['price'] * $item['quantity'];
        }

        return $this->formatNumber($subTotal, $rounded);
    }

    public function tax(bool $rounded = true): float
    {
        $taxTotal = 0.00;
        foreach ($this->items as $item) {
            if ($item['taxable']) {
                $taxTotal += ($item['price'] * $item['quantity']) * ($item['taxRate'] / 100);
            }
        }

        return $this->formatNumber($taxTotal, $rounded);
    }

    public function total(bool $rounded = true): float
    {
        return $this->formatNumber($this->subTotal(false) + $this->tax(false), $rounded);
    }

    public function getCartItems(): Collection
    {
        return $this->items;
    }

    private function formatNumber(float $number, bool $rounded = true): float
    {
        if ($rounded) {
            return number_format(round($number, config('cart.number_format.decimal_places'), config('cart.number_format.rounding_preference')), config('cart.number_format.decimal_places'), '.', config('cart.number_format.thousand_separator'));
        } else {
            return number_format($number, config('cart.number_format.decimal_places'), '.', config('cart.number_format.thousand_separator'));
        }
    }

    private function itemsToSession()
    {
        \Session::forget($this->name . '.items');
        \Session::put($this->name . '.items', $this->items);
        \Session::save();
    }

    private function isMulti($array): bool
    {
        foreach ($array as $item) {
            if (is_array($item)) {
                return true;
            }
        }
        return false;
    }

    private function validate($item)
    {
        $diff = array_diff_key($this->mandatoryKeys, $item);
        if (count($diff) > 0) {
            throw new InvalidCartDataException("The following mandatory keys are missing from the item submitted: " . implode(", ", $diff));
        }
    }

}
