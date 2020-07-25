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


  /**
  * Make trade from currents bets
  *   Use LearnerBot to predict successful bets
  */
  public function makeTrades()
  {
    // Get training data
    $trainDataset = $this->learnerBot->getTrainDataset();

    // Train with KNN
    $estimator = new KNearestNeighbors(3);
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

    $data = [
      'logs' => $predictions,
      'trades' => $success
    ];

    return $data;
  }

/*
  public function createDataset($data, $unlabeled = false)
  {
    $samples = $data['rows'];
    $labels = [];

    foreach($samples as $key => $sample){

      // Set labels
      $labels[] = ($sample['success'] == 1) ? 'success' : 'fail';

      // Remove unused features
      unset($samples[$key]['success']);
      unset($samples[$key]['active']);
      unset($samples[$key]['id']);
      unset($samples[$key]['market']);

      // Parse float for remaining features
      $samples[$key] = array_map('floatval', $samples[$key]);

    }

    if($unlabeled){
      $dataset = new Unlabeled($samples);
    }else{
      $dataset = new Labeled($samples, $labels);
    }


    return $dataset;
  }

  public function getTrainDataset()
  {
    // Get finish bets
    $res = new Bets(Bet::where('active', false)
                      ->orderBy('id', 'asc')->get());

    $data = $res->toCsv();
    $dataset = $this->createDataset($data);


    return $dataset;

  }

  public function getPredictDataset()
  {
    // Get finish bets
    $res = new Bets(Bet::where('active', true)
                      ->orderBy('id', 'asc')->get());

    $data = $res->toCsv();
    $dataset = $this->createDataset($data, true);


    return $dataset;
  }

  public function getPredictMarkets()
  {
    // Get finish bets
    $res = (Bet::where('active', true)
                          ->orderBy('id', 'asc')->get());

    return $res;
  }

  public function getSuccessBets($bets, $predictions)
  {
    $success = [];

    foreach( $bets as $key => $bet){
      if( $predictions[$key] === 'success'){
        $success[] = $bet;
      }
    }

    return $success;
  }
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
          'active' => true
        ]);
        $bet->save();
      }

      return $bet;
  }

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
