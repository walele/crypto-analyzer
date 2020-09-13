<?php

namespace App\Http\Controllers\API;

use App\Bet;
use App\Trade;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Crypto\BetBot\BetBot;
use App\Crypto\BetBot\StatsBot;


class StatsController extends Controller
{
    /**
     * Get bot general info
     */
    public function index( StatsBot $bot)
    {
      $data['stats'] = [
        'daily_wins' => $bot->getLastIntervalWins(),
      ];

      return $data;
    }


    /**
    * Return trades stats
    */
    public function getDailyWin()
    {

      $strategies = $bot->getStrategies();
      print_r($strategies);
      $data = Stats::getDailyWin();

      return $data;
    }

    /**
     * Get wallet info
     */
    public function wallet(TradeBot $bot)
    {
      $data = $bot->getWalletInfo();

      return $data;
    }

    /**
     * Get Coin info
     */
    public function coin(OrderBot $bot, $name)
    {
      $data = $bot->getCoinAvailable($name);


      return $data;
    }

    /**
     * Get Coin info
     */
    public function order(OrderBot $bot, $name, $id)
    {
      $data = $bot->getOrderInfo($name, $id);

      return $data;
    }

    /**
     * Get Coin step info
     */
    public function coinStep(OrderBot $bot, $name)
    {
      $data = $bot->getCoinAvailable($name);


      return $data;
    }

    /**
     * Get Coin step info
     */
    public function binanceOrders(OrderBot $bot)
    {
        $data = $bot->getBinanceOrders();


      return $data;
    }


    /**
    * Make bets via BetBot
    */
    public function makeBets(BetBot $bot)
    {
      // Run bot strategy
      $output = $bot->makeBets();

      return $output;
    }

    /**
    * Make trades via tradebot
    */
    public function makeTrades()
    {

      $bot = TradeBot::getInstance();
      $trades = $bot->makeTrades();

      return $trades;
    }

    public function validateTrades()
    {
      $bot = TradeBot::getInstance();
      $trades = $bot->validateTrades();

      return $trades;

    }

    public function validateBets()
    {
      $bot = BetBot::getInstance();
      $bets2 = $bot->validateBets2();

      return $bets2;

    }


    /**
    * Make Bets & trades
    */
    public function makeBetsAndTrades(BetBot $bot, TradeBot $tradeBot, OrderBot $orderBot)
    {
      $bets = $bot->makeBets();
      //$trades = $tradeBot->makeTrades();

      // Get bets for orders
      //$testsTrade = Trade::orderBy('id', 'desc')->limit(2)->get();
    //  $tradeForOrders = $trades['trades'];
      //$tradeForOrders = $testsTrade;

    //  $orders = $orderBot->makeOrders($tradeForOrders);
      $data = [
        'bets' => $bets,
      //  'trades' => $trades,
      //  'orders' => $orders
      ];

      return $data;

    }

    /**
    * Evaluate a ml estimator & model
    */
    public function evaluateEstimator($estimator){

      $learner = LearnerBot::getInstance();
      $result = $learner->evaluate($estimator);
      $data = [
        'estimator' => $estimator,
        'score' => $result
      ];

      return $data;
    }

    public function fixRealValues( OrderBot $orderBot)
    {
      $data = $orderBot->fixRealValues();

      return $data;
    }


    public function fixOldMarkets( BetBot $bot)
    {
      $data = $bot->fixOldMarkets();

      return $data;
    }
}
