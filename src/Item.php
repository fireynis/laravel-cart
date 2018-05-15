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
 * @property int $id
 * @property int $cart_id
 * @property int $item_id
 * @property string $identifier
 * @property string $description
 * @property float $price
 * @property int $quantity
 * @property int $taxable
 * @property int $tax_rate
 * @property int $shipping
 * @property string|null $model_type
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static Builder|Item whereCartId($value)
 * @method static Builder|Item whereCreatedAt($value)
 * @method static Builder|Item whereDescription($value)
 * @method static Builder|Item whereId($value)
 * @method static Builder|Item whereIdentifier($value)
 * @method static Builder|Item whereItemId($value)
 * @method static Builder|Item whereModelType($value)
 * @method static Builder|Item wherePrice($value)
 * @method static Builder|Item whereQuantity($value)
 * @method static Builder|Item whereTaxRate($value)
 * @method static Builder|Item whereTaxable($value)
 * @method static Builder|Item whereUpdatedAt($value)
 * @mixin \Eloquent
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

    public static function fromValues($id, $quantity, $price, $description, $taxable, $taxRate, $modelType = null)
    {
        $model = new self();
        $model->item_id = $id;
        $model->quantity = $quantity;
        $model->price = $price;
        $model->description = $description;
        $model->taxable = $taxable;
        $model->tax_rate = $taxRate;
        if (!is_null($modelType)) {
            $model->setModel($modelType);
        }
        return $model;
    }

    public function setTaxable(bool $taxable)
    {
        $this->taxable = $taxable;
    }

    public function setTaxRate(int $rate)
    {
        $this->tax_rate = $rate;
    }

    public static function fromArray(array $data)
    {
        return Item::fromValues($data['id'], $data['quantity'], $data['price'], $data['description'], $data['taxable'], $data['taxRate'], $data['modelType']);
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
        if ($this->modelIsItem()) {
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
        if ($this->modelIsItem()) {
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