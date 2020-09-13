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
use App\Http\Resources\BetCollection;

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
      $bet_time = $strat->getActiveTime();
    }

    $start_time = Carbon::now()->subHours($bet_time)->toDateTimeString();
    $daily_win = Bet::where('success', true)
                ->where('created_at', '>',
                  $start_time )
                ->get();

    $bets = BetCollection::make($daily_win);


    $data = [
      'bet_time' => $bet_time,
      'start_time' => $start_time,
      'count' => $daily_win->count(),
      'bets' => $bets
    ];

    return $data;
  }
}
