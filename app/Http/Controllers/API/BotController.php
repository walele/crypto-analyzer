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
use App\Crypto\BetBot\LearnerBot;
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
     * Get bot general info
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
    * Return trades stats
    */
    public function tradesStats(){
      $data = Stats::getTradesStats();

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
    public function makeTrades(TradeBot $bot)
    {
      $success = $bot->makeTrades();

      return $success;
    }

    /**
    * Make Bets & trades
    */
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

}
