<?php

namespace App\Http\Controllers\API;

use App\Bet;
use App\Trade;
use App\Http\Resources\Bets;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Binance;
use App\Crypto\BetBot\BetBot;
use App\Crypto\BetBot\TradeBot;
use App\Crypto\Bettor;
use App\Crypto\Stats;

use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\CrossValidation\HoldOut;
use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\CrossValidation\Metrics\Accuracy;

class BotController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(BetBot $bot)
    {
      $indicators = $bot->getIndicators();
      $conditions = $bot->getConditions();

      $data['strategy'] = [
        'indicators' => $indicators,
        'conditions' => $conditions,
        'description' => $bot->strategyToString()
      ];


      return $data;
    }


    /**
    * Return bets stats
    */
    public function betsStats(){
      $data = Stats::getBetsStats();

      return $data;
    }


    /**
    * Return bets stats
    */
    public function tradesStats(){
      $data = Stats::getTradesStats();

      return $data;
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function wallet(TradeBot $bot)
    {
      $data = [];
      //$api_key = config('binance.api_key');
      //$api_secret = config('binance.api_secret');
      //$api = new Binance\API($api_key,$api_secret);
      $api = $bot->getBinanceApi();
      $api->useServerTime();
      $ticker = $api->prices(); // Make sure you have an updated ticker object for this to work
      $balances = $api->balances($ticker);
      //print_r($balances);

      $data['btc'] = $balances['BTC']['available'];
      $data['all'] = $api->btc_value;

      $r = [
        'wallet' => $data
      ];

      return $r;
    }



    public function makeBets(BetBot $bot, TradeBot $tradeBot)
    {
      // Run bot strategy
      $output = $bot->makeBets();

      return $output;

    }


    public function makeTrades(TradeBot $bot)
    {
      // Get training data
      $success = $bot->makeTrades();

      return $success;
    }

    public function makeBetsAndTrades(BetBot $bot, TradeBot $tradeBot)
    {
      $bets = $bot->makeBets();
      $trades = $tradeBot->makeTrades();
      $data = [
        'bets' => $bets,
        'trades' => $trades
      ];

      return $data;

    }


}
