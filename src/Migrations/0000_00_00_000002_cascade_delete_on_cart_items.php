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
            $table->dropForeign(['cart_id']);
            $table->foreign('cart_id')->references('id')->on(config('cart.cart_table_name'))->onDelete('cascade');
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
            $table->dropForeign(['cart_id']);
            $table->foreign('cart_id')->references('id')->on(config('cart.cart_table_name'));
        });
    }
}
