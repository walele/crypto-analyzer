<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Crypto\BetBot\TradeBot;
use App\Crypto\BetBot\OrderBot;

class TradeBotProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
       $this->app->singleton(TradeBot::class, function ($app) {

            $bot =  new TradeBot;

            return $bot;
        });

        $this->app->singleton(OrderBot::class, function ($app) {

             $bot =  new OrderBot;

             return $bot;
         });

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
