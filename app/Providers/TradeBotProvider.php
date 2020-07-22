<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Crypto\BetBot\TradeBot;
use App\Crypto\BetBot\TradeBoto;

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
