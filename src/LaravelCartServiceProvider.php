<?php

namespace Fireynis\LaravelCart;

use Illuminate\Support\ServiceProvider;

class LaravelCartServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/Config/cart.php' => config_path('cart.php'),
        ], 'config');

        $this->loadMigrationsFrom(__DIR__.'/Migrations');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('cart', 'Fireynis\LaravelCart\Cart');

        $this->mergeConfigFrom(__DIR__.'/Config/cart.php', 'cart');
    }
}