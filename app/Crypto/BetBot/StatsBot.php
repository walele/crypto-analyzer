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


  public function getWinStats()
  {
      $data = [];

      // Get startegy key
      $strategies = $this->betBot->getStrategies();
      foreach($strategies as $strat){
        $strat_key = $strat->getKey();
      }

      // Get win bets for strategy
      $bets = Bet::where('strategy', $strat_key )
                  ->where('success', true)
                    ->get();

      $average_success_time = '';
      $bets_times = [];
      $durations = [];
      $min_percs = [];
      $markets_win = [];

      // win bets for strategy
      foreach($bets as $bet){

        // calc duration
        $end_at = Carbon::parse($bet->end_at);
        $duration = $bet->created_at->diffInHours($end_at);
        $durations[] = $duration;

        // calc min perc
        $min_perc = Helpers::calcPercentageDiff($bet->buy_price, $bet->final_min_price);
        $min_percs[] = $min_perc;

        // markets win
        if( isset($markets_win[$bet->market])){
          $markets_win[$bet->market] += 1;
        } else{
          $markets_win[$bet->market] =1;
        }
      }

      $durations = collect($durations);
      $min_percs = collect($min_percs);

      $data = [
        'average_success_time' => number_format($durations->avg(), 2),
        'average_min_perc' => number_format($min_percs->avg(), 2),
        'count' => $bets->count(),
        'strat_key' => $strat_key,
        'markets_win' => $markets_win
      ];

      return $data;
  }

  /**
  *
  */
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
