<?php

namespace App\Crypto\BetBot;

use App\Bet;
use App\Trade;
use App\Http\Resources\Bets;
use App\Crypto\BetBot\BetBot;
use App\Crypto\MarketClient;
use App\Crypto\Helpers;
use Carbon\Carbon;
use Binance;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class StatsBot
{
  private static $instance = null;

  private $betBot;

  public function __construct(BetBot $betBot)
  {
    $this->betBot = $betBot;
  }


  public function getLastIntervalWins()
  {
    $strategies = $this->betBot->getStrategies();
    foreach($strategies as $strat){
      $betTime = $strat->getActiveTime();
    }

    $daily_win = Bet::where('success', true)
                ->where('created_at', '>',
                  Carbon::now()->subHours($betTime)->toDateTimeString() )
                ->get();


    $data = $daily_win->toArray();


    return $data;
  }
}
