<?php

namespace Fireynis\LaravelCart;

use Fireynis\LaravelCart\Contracts\ItemInterface;
use Fireynis\LaravelCart\Exceptions\InvalidCartDataException;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

/**
 * Fireynis\LaravelCart\Cart
 *
 */
class Cart
{
    //Values don't matter, just the keys. This means I don't have to array_flip each
    //time I want to validate the incoming array.
    private $mandatoryKeys = [
        "id" => 1,
        "description" => 1,
        "price" => 1,
        "quantity" => 1
    ];

    const DEFAULT_CART_NAME = "fireynis_cart_default";

    protected $session;

    protected $cartName;

    protected $items;

    protected $autoDelete;

    public function __construct(SessionManager $session)
    {
        $this->session = $session;
        $this->items = collect($session->get($this->cartName . '.items', []));

        if ($this->workInIncognito()) {
            $this->cartName = Cookie::get('fireynis_cart', self::DEFAULT_CART_NAME);
        } else {
            $this->cartName = $session->get('fireynis_cart.name', self::DEFAULT_CART_NAME);
        }

        if ($this->alwaysStore() || $this->workInIncognito()) {
            $this->restoreCart($this->cartName);
        }

        $this->autoDelete = null;
    }

    public function setCartName(string $name)
    {
        $this->cartName = $name;
        $this->saveToSession();
    }

    public function setAutoDelete(bool $autoDelete)
    {
        $this->autoDelete = $autoDelete;
    }

    public function addItem($itemData, bool $overrideTaxable = false, bool $taxable = true, $overrideTaxRate = false, $taxRate = 13)
    {
        if (is_array($itemData) && $this->isMultidimensionalArray($itemData)) {
            foreach ($itemData as $itemDatum) {
                $this->addItem($itemDatum);
            }
            return $this;
        }
        if (array_key_exists('item', $itemData) && $itemData['item'] instanceof Item) {
            $item = $itemData['item'];
        } elseif (array_key_exists('item', $itemData) && $itemData['item'] instanceof ItemInterface) {
            $model = $itemData['item'];
            $modelType = get_class($model);
            $item = Item::fromValues($model->uniqueId(), $itemData['quantity'], $model->price(), $model->description(), $model->taxable(), config('cart.tax_rate'), $modelType);
        } elseif (is_array($itemData)) {
            $this->validate($itemData);
            $item = Item::fromValues($itemData['id'], $itemData['quantity'], $itemData['price'], $itemData['description'], $taxable, config('cart.tax_rate'), config('cart.associated_model'));
        } else {
            throw new InvalidCartDataException("The data provided is not a model or array.");
        }

        if ($overrideTaxable) {
            $item->setTaxable($taxable);
        }
        if ($overrideTaxRate) {
            $item->setTaxRate($taxRate);
        }

        $item->identifier = md5($item->item_id . microtime() . uniqid());

        $this->items->put($item->identifier, $item);

        $this->saveToSession();

        return $this;
    }

    public function updateItem(string $identifier, int $quantity)
    {
        $item = $this->items->pull($identifier);
        $item->quantity = $quantity;

        $this->items->put($item->identifier, $item);

        $this->saveToSession();

        return $this;
    }

    public function removeItem(string $identifier)
    {
        $this->items->pull($identifier);

        $this->saveToSession();
    }

    public function getItem(string $identifier)
    {
        return $this->items->get($identifier);

    }

    public function subTotal(bool $rounded = true): float
    {
        $subTotal = 0.00;
        foreach ($this->cartItems() as $item) {
            $subTotal += $item->price * $item->quantity;
        }

        return $this->formatNumber($subTotal, $rounded);
    }

    public function tax(bool $rounded = true): float
    {
        $taxTotal = 0.00;
        foreach ($this->cartItems() as $item) {
            if ($item->taxable) {
                $taxTotal += ($item->price * $item->quantity) * ($item->taxRate / 100);
            }
        }

        return $this->formatNumber($taxTotal, $rounded);
    }

    public function total(bool $rounded = true): float
    {
        return $this->formatNumber($this->subTotal(false) + $this->tax(false), $rounded);
    }

    public function count()
    {
        return $this->items->count();
    }

    public function cartItems(): Collection
    {
        return $this->items;
    }

    public function store(string $name = null)
    {
        if (!is_null($name)) {
            $this->setCartName($name);
        } else if ($this->cartName() == self::DEFAULT_CART_NAME) {
            $this->setCartName(md5(uniqid() . microtime()));
        }

        if (!is_null($this->autoDelete)) {
            $autoDelete = $this->autoDelete;
        } else {
            $autoDelete = config('cart.auto_delete');
        }

        $date = date('Y-m-d h:i:s', time());
        $cart_id = $this->getConnection()->where('name', $this->cartName)->value('id');

        if (is_null($cart_id)) {
            $cart_id = $this->getConnection()->insertGetId(
                [
                    'name' => $this->cartName,
                    'auto_delete' => $autoDelete,
                    'created_at' => $date,
                    'updated_at' => $date
                ]
            );
        } else {
            $this->getConnection()->where('name', $this->cartName)->update(['updated_at' => $date]);
        }

        Item::unguard();
        foreach ($this->items as $item) {
            $item->cart_id = $cart_id;
            Item::updateOrInsert(['identifier' => $item->identifier], $item->toArray());
        }
        Item::reguard();

        $ids = $this->items->pluck('identifier')->all();

        DB::connection($this->getConnectionName())
            ->table(config('cart.items_table_name'))
            ->where('cart_id', '=', $cart_id)
            ->whereNotIn('identifier', $ids)
            ->delete();

        return $this;
    }

    public function restoreCart(string $name)
    {
        $this->cartName = $name;
        $cart_id = $this->getConnection()->where('name', $this->cartName)->value('id');

        if (!is_null($cart_id)) {
            $this->items = Item::whereCartId($cart_id)->get();
        }
        $this->items = $this->items->keyBy('identifier');
        $this->saveToSession(true);
        return $this;
    }

    public function delete()
    {
        $cart_id = $this->getConnection()->where('name', $this->cartName)->value('id');
        if (!is_null($cart_id)) {
            Item::unguard();
            foreach ($this->items as $item) {
                $item->cart_id = $cart_id;
                Item::where('cart_id', $cart_id);
            }
            Item::reguard();
        }
        $this->getConnection()->where('name', $this->cartName)->delete();
    }

    public function cartName(): string
    {
        return !is_null($this->cartName) ? $this->cartName : self::DEFAULT_CART_NAME;
    }

    private function getConnection()
    {
        return DB::connection($this->getConnectionName())->table($this->getTableName());
    }

    private function getConnectionName()
    {
        return config('cart.db_connection');
    }

    private function getTableName()
    {
        return config('cart.cart_table_name');
    }

    private function isMultidimensionalArray($array): bool
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

    private function formatNumber(float $number, bool $rounded = true): float
    {
        if ($rounded) {
            return number_format(round($number, config('cart.number_format.decimal_places'), config('cart.number_format.rounding_preference')), config('cart.number_format.decimal_places'), '.', config('cart.number_format.thousand_separator'));
        } else {
            return $number;
        }
    }

    private function alwaysStore(): bool
    {
        return config('cart.always_store');
    }

    private function workInIncognito()
    {
        return config('cart.work_in_incognito');
    }

    private function saveToSession(bool $skipStore = false)
    {
        if (($this->alwaysStore() || $this->workInIncognito()) && !$skipStore) {
            $this->store();
        }
        if ($this->workInIncognito()) {
            Cookie::queue(Cookie::make('fireynis_cart', $this->cartName, config('cart.cookie_time_exist')));
        }
        $this->session->put('fireynis_cart.name', $this->cartName);
        $this->session->put($this->cartName() . '.items', $this->items);
        $this->session->save();
    }
}
