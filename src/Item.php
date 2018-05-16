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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Fireynis\LaravelCart\Item
 *
 */
class Item extends Model implements ItemInterface
{
    protected $guarded = ['id', 'identifier'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('cart.items_table_name');
        $this->connection = config('cart.db_connection');
    }

    public static function fromValues($id, $quantity, $price, $description, $overrideTaxable, $taxable, $overrideTaxRate, $taxRate, $overrideShipping, $shipping, $modelType = null)
    {
        $model = new self();
        $model->item_id = $id;
        $model->quantity = $quantity;
        $model->price = $price;
        $model->description = $description;
        $model->override_taxable = $overrideTaxable;
        $model->taxable = $taxable;
        $model->override_tax_rate = $overrideTaxRate
        $model->tax_rate = $taxRate;
        $model->override_shipping = $overrideShipping;
        $model->shipping = $shipping;
        if (!is_null($modelType)) {
            $model->setModel($modelType);
        }
        return $model;
    }

    public function setTaxable(bool $taxable)
    {
        $this->taxable = $taxable;
        $this->override_taxable = true;
    }

    public function setTaxRate(int $rate)
    {
        $this->tax_rate = $rate;
        $this->override_tax_rate = true;
    }

    public function setShipping(int $rate)
    {
        $this->shipping = $rate;
        $this->override_shipping = true;
    }

    /**
     * Obtain the description of the item.
     *
     * @return string
     */
    public function description(): string
    {
        if ($this->modelIsItem()) {
            return $this->model()->description();
        }
        return $this->description;
    }

    /**
     * Returns the price of the item .
     *
     * @return float
     */
    public function price(): float
    {
        if ($this->modelIsItem()) {
            return $this->model()->price();
        }
        return $this->price;
    }

    /**
     * Returns whether or not an item is taxable.
     *
     * @return bool
     */
    public function taxable(): bool
    {
        if ($this->modelIsItem() && !$this->override_taxable) {
            return $this->model()->taxable();
        }
        return $this->taxable;
    }

    /**
     * Returns the rate it is taxed at.
     *
     * @return int
     */
    public function taxRate(): int
    {
        if ($this->modelIsItem() && !$this->override_tax_rate) {
            return $this->model()->taxRate();
        }
        return $this->tax_rate;
    }

    /**
     * Returns the primary key that could be used to
     * obtain an object from the database.
     *
     * @return int
     */
    public function uniqueId(): int
    {
        if ($this->modelIsItem()) {
            return $this->model()->uniqueId();
        }
        return $this->item_id;
    }

    public function model()
    {
        if (!is_null($this->model_type)) {
            return with(new $this->model_type)->find($this->item_id);
        }
        return null;
    }

    public function setModel($modelType)
    {
        if (is_string($modelType) && class_exists($modelType)) {
            $this->model_type = $modelType;
        } else {
            throw new InvalidClassTypeException("The class ".$modelType." does not exist");
        }
    }

    private function modelIsItem(): bool
    {
        if (!is_null($this->model()) && $this->model() instanceof ItemInterface) {
            return true;
        }
        return false;
    }

    /**
     * Returns the cost per item for shipping.
     *
     * @return float
     */
    public function shipping(): float
    {
        if ($this->modelIsItem()) {
            return $this->model()->shipping();
        }
        return $this->shipping;
    }
}