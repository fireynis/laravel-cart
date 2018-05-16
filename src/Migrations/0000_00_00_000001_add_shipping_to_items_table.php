<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShippingToItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('cart.db_connection'))->table(config('cart.items_table_name'), function (Blueprint $table) {
            $table->boolean('override_taxable')->after("quantity")->default(false);
            $table->boolean('override_tax_rate')->after("taxable")->default(false);
            $table->boolean('override_shipping')->after("tax_rate")->default(false);
            $table->float('shipping', 10, 2)->after("override_shipping")->default(0.00);
        });

        Schema::connection(config('cart.db_connection'))->table(config('cart.cart_table_name'), function (Blueprint $table) {
            $table->boolean('override_shipping')->default(0.00)->after('auto_delete');
            $table->float('shipping', 10, 2)->default(0.00)->after('shipping');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('cart.db_connection'))->table(config('cart.items_table_name'), function (Blueprint $table) {
            $table->dropColumn('shipping');
            $table->dropColumn('override_taxable');
            $table->dropColumn('override_tax_rate');
            $table->dropColumn('override_shipping');
        });
    }
}
