<?php

namespace Fireynis\LaravelCart\Commands;

use Carbon\Carbon;
use Fireynis\LaravelCart\Cart;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveOldCarts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cart:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes carts that have auto delete. Deletion is determined by the age set in the config';

    protected $cart;

    /**
     * Create a new command instance.
     *
     */
    public function __construct(Cart $cart)
    {
        parent::__construct();

        $this->cart = $cart;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $interval = \DateInterval::createFromDateString(config('cart.auto_delete_time'));
        $today = Carbon::today();
        $carts = DB::connection(config('cart.db_connection'))->table(config('cart.cart_table_name'))->get();
        foreach ($carts as $cart) {
            $dateUpdated = Carbon::createFromFormat("Y-m-d h:i:s", $cart->updated_at);
            if ($dateUpdated->add($interval)->lessThanOrEqualTo($today)) {
                $this->cart->restoreCart($cart->name)->delete();
            }
        }
        return true;
    }
}