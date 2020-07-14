<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Crypto\BetBot\BetBot;
use App\Crypto\Strategies\ShortUpSinceDrop;

class BetBotProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
      $bot =  BetBot::getInstance();
      $bot->addStrategy(new ShortUpSinceDrop);

      $this->app->singleton(BetBot::class, function ($app) {

          $bot =  BetBot::getInstance();
          $bot->addStrategy(new ShortUpSinceDrop);

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
