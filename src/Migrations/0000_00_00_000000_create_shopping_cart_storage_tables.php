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
        Schema::connection(config('cart.db_connection'))->create(config('cart.cart_table_name'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->boolean('auto_delete')->default(true);
            $table->timestamps();
        });

        Schema::connection(config('cart.db_connection'))->create(config('cart.items_table_name'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cart_id');
            $table->unsignedInteger('item_id');
            $table->string('identifier')->unique();
            $table->longText('description');
            $table->float('price', 10, 2);
            $table->unsignedInteger('quantity');
            $table->boolean('taxable');
            $table->unsignedInteger('tax_rate');
            $table->string('model_type')->nullable();
            $table->timestamps();

            $table->foreign('cart_id')->references('id')->on(config('cart.cart_table_name'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('cart.db_connection'))->dropIfExists(config('cart.items_table_name'));
        Schema::connection(config('cart.db_connection'))->dropIfExists(config('cart.cart_table_name'));
    }
}
