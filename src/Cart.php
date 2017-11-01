<?php

namespace Fireynis\LaravelCart;

use Fireynis\LaravelCart\Contracts\ItemInterface;
use Illuminate\Support\Collection;

class Cart
{

    private $items;

    private $name;

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

        $items = \Session::get($this->name . '.items');
        if (is_null($items)) {
            $this->items = new Collection();
        } else {
            $this->items = collect($items);
        }
    }

    public function add(ItemInterface $item, int $quantity, bool $overrideTaxable = false, bool $taxable = true, $overrideTaxRate = false, $taxRate = 13)
    {
        if (!$overrideTaxable) {
            $taxable = $item->taxable();
        }
        if (!$overrideTaxRate) {
//            $taxRate = config('cart.tax_rate');
            $taxRate = 13;
        }
        $this->items[uniqid()] = [
            'description' => $item->description(),
            'price' => $item->price(),
            'taxable' => $taxable,
            'taxRate' => $taxRate,
            'quantity' => $quantity
        ];
        $this->itemsToSession();
    }

    public function update(string $identifier, int $quantity)
    {
        if ($this->items->has($identifier)) {
            $this->items->get($identifier)['quantity'] = $quantity;
        }
        $this->itemsToSession();
    }

    public function remove(string $identifier)
    {
        if ($this->items->has($identifier)) {
            $this->items->forget($identifier);
        }
        $this->itemsToSession();
//        dd(\Session::get($this->name . '.items'));
    }

    public function subTotal(bool $rounded = true): float
    {
        $subTotal = 0.00;
        foreach ($this->items as $item) {
            $subTotal += $item['price']*$item['quantity'];
        }

        return $this->formatNumber($subTotal, $rounded);
    }

    public function tax(bool $rounded = true): float
    {
        $taxTotal = 0.00;
        foreach ($this->items as $item) {
            if ($item['taxable']) {
                $taxTotal += ($item['price']*$item['quantity'])*($item['taxRate']/100);
            }
        }

        return $this->formatNumber($taxTotal, $rounded);
    }

    public function total(bool $rounded = true): float
    {
        return $this->formatNumber($this->subTotal(false)+$this->tax(false), $rounded);
    }

    public function getCartItems()
    {
        return $this->items;
    }

    private function formatNumber(float $number, bool $rounded = true): float
    {
        if ($rounded) {
            return number_format(round($number, 2, PHP_ROUND_HALF_UP), 2, '.', ' ');
        } else {
            return number_format($number, 2, '.', ' ');
        }
    }

    private function itemsToSession()
    {
        \Session::forget($this->name . '.items');
        \Session::put($this->name . '.items', $this->items);
        \Session::save();
    }

}
