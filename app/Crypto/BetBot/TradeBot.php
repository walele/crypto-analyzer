<?php

namespace App\Crypto\BetBot;

use App\Crypto\Strategies\Strategy;
use App\Crypto\Bettor;
use App\Crypto\MarketClient;
use App\Crypto\Helpers;
use App\Bet;
use App\Trade;
use App\Http\Resources\Bets;
use Carbon\Carbon;

use Binance;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\CrossValidation\HoldOut;
use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\CrossValidation\Metrics\Accuracy;
use Rubix\ML\Kernels\Distance\Manhattan;
use Illuminate\Support\Facades\DB;

class TradeBot
{
  private static $instance = null;

  private $bets = [];
  private $strategies = [];
  private $markets = [];
  private $binanceApi;
  private $learnerBot;
  private $tt = 0;

  public function __construct()
  {
    $this->init();
  }


  /**
  *   Singleton
  */
  public static function getInstance()
  {
    if (self::$instance == null)
    {
      self::$instance = new TradeBot();
    }

    return self::$instance;
  }

  /**
  *   Init
  */
  private function init()
  {
    $api_key = config('binance.api_key');
    $api_secret = config('binance.api_secret');
    $this->binanceApi = new Binance\API($api_key,$api_secret);
    $this->learnerBot = LearnerBot::getInstance();
  }

  /**
  * Get binance client
  */
  public function getBinanceApi()
  {
    return $this->binanceApi;
  }

  /**
  * Get current wallet info
  */
  public function getWalletInfo()
  {
    $api = $this->getBinanceApi();
    $api->useServerTime();
    $ticker = $api->prices(); // Make sure you have an updated ticker object for this to work
    $balances = $api->balances($ticker);

    $data['btc'] = $balances['BTC']['available'];
    $data['all'] = $api->btc_value;

    $r = [
      'wallet' => $data
    ];

    return $r;
  }

  private function markTradedBets()
  {
    $tradded = DB::table('bets')
              ->update(['traded' => true]);
  }

  /**
  * Make trade from currents bets
  *   Use LearnerBot to predict successful bets
  */
  public function makeTrades()
  {
    // Get training data
    $trainDataset = $this->learnerBot->getTrainDataset();

    // Train with KNN
    $estimator = new KNearestNeighbors(42, true, new Manhattan());
    $estimator->train($trainDataset);

    // Make predictions
    $predictDataset = $this->learnerBot->getPredictDataset();
    $predictMarket = $this->learnerBot->getPredictMarkets();
    $predictions = $estimator->predict($predictDataset);

    // Get success predictions
    $success = $this->learnerBot->getSuccessBets($predictMarket, $predictions);

    // Place trades from succes bets
    $this->placeTrades($success);
    $this->validateTrades();
    //$this->markTradedBets();

    $data = [
      'logs' => $predictions,
      'trades' => $success
    ];

    return $data;
  }

  /**
  *   Loop an array o trades and place
  */
  public function placeTrades($trades)
  {
    foreach($trades as $trade){
      $this->placeTrade($trade);
    }
  }


  /**
  * Add a bet to db if no active bet for that market
  */
  public function placeTrade($trade)
  {
      $market = $trade->market;
      $actives = $this->getActiveTrade($market);

      $bet = null;
      if( ! $actives->count() ){

        $price = $this->binanceApi->price($market);

        $bet = new Trade([
          'market' => $trade->market,
          'payload' => serialize($trade->payload),
          'buy_price' => $price,
          'active' => true,
          'traded' => false
        ]);
        $bet->save();
      }

      return $bet;
  }

  /**
  *  Validate current active bets to see if they are success
  */
  private function validateTrades()
  {
    $client = new MarketClient;
    $betTimeout = 6;
    $limit = (60/5) * $betTimeout;
    $trades = Trade::where('active', true)
                ->where('created_at', '<',
                  Carbon::now()->subHours($betTimeout)->toDateTimeString() )
                ->get();

    //print_r($bets->toArray());
    foreach($trades as $trade){

      $buy_price = (float) $trade->buy_price;
      $successPrice = $buy_price + ($buy_price * 0.015);

      $prices = $client->getLastMarketPrices($trade->market, $limit);
      $firstPrice = $prices->first();
      $lastPrice = $prices->last();
      $maxPrice = $prices->max('price');
      $diff = Helpers::calcPercentageDiff($buy_price, $maxPrice);


      $success = $maxPrice > $successPrice;

      $trade->success = $success;
      $trade->final_prices = $maxPrice;
      $trade->active = false;
      $trade->save();

    }
  }


  /**
  *  Get active bet for a market
  */
  public function getActiveTrade($market)
  {
    $actives = Trade::where('market', $market )
                      ->where('active', 1);

    return $actives;
  }


}
