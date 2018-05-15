<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShoppingCartStorageTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('cart.db_connection'))->table(config('cart.items_table_name'), function (Blueprint $table) {
            $$table->float('shipping', 10, 2)->after("tax_rate");
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
            $$table->dropColumn('shipping');
        });
    }
}
