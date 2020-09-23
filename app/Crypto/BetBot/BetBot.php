<?php

namespace App\Crypto\BetBot;

use Carbon\Carbon;
use App\Crypto\Strategies\Strategy;
use App\Crypto\Bettor;
use App\Crypto\MarketClient;
use App\Crypto\Helpers;
use App\Bet;
use App\Log;

class BetBot
{
  CONST SUCCESS_PRICE = 1.024;
  CONST STOP_PRICE = 0.99;
  private static $instance = null;

  private $bets = [];
  private $logs = [];
  private $strategies = [];
  private $markets = [];
  private $learnerBot;

  private function __construct()
  {
    $this->init();
  }

  /**
  * Singleton pattern
  */
  public static function getInstance()
  {
    if (self::$instance == null)
    {
      self::$instance = new BetBot();
    }

    return self::$instance;
  }

  /**
  * Init
  */
  private function init()
  {
    $client = new MarketClient();
    $this->markets = $client->getTables();
    $this->learnerBot = LearnerBot::getInstance();
  }

  /**
  * Add strategy for betting
  */
  public function addStrategy(Strategy $s)
  {
      $this->strategies[] = $s;
  }

  /**
  * Get strategies
  */
  public function getStrategies()
  {
      return $this->strategies;
  }

  public function getStrategiesInfo(): array
  {
      $data = [];

      foreach($this->strategies as $strat){

        $data[] = [
          'name' => $strat->getName(),
          'key' => $strat->getKey(),
          'description' => $strat->getDescription(),
          'indicators' =>  $strat->getIndicators(),
          'conditions' => $strat->getConditions(),
          'features' => $strat->getFeatures(),
        ];
      }

      return $data;
  }

  /**
  * Fun all strategies and make bets
  */
  private function run()
  {
    $active_markets = $this->getActiveMarketBets();
    $inactive_markets = array_diff($this->markets, $active_markets);

    foreach($this->strategies as $s){
      $s->run($inactive_markets);
      $this->bets = $s->getBets();
      $this->logs = $s->getLogs();

      // Log
      $log = new Log([
        'type' => 'bet',
        'payload' => serialize($this->logs),
      ]);
      $log->save();
    }

    return true;
  }


  /**
  * Make bets & validate bets
  */
  public function makeBets()
  {

    // Validate current bet
    $this->validateBets2();
    $this->validateBets();


    // Run bot strategy
    $this->run();
    $bets = $this->bets;
    $logs = $this->logs;

    //Log::info('bets : ' . print_r($logs, true));


    // Place new bets
    $this->trainLearnBot($bets);
    foreach($bets as $bet){
      $this->placeBet($bet);
    }


    $data = [
      'logs' => $logs,
      'bets' => array_values($bets)
    ];

    return $data;

  }

  /**
  * Getter for bets
  */
  public function getBets()
  {

    return $this->bets;
  }

  /**
  * Print object
  */
  public function __toString()
  {
    $s = '';
    foreach($this->strategies as $s){
      //var_dump($s);
    }
    return '';
  }

  /**
  * Current strategy description
  */
  public function strategyToString(): string
  {
      $str = '';

      foreach($this->strategies as $s){
        $str .= ($s->getDescription());
      }

      return $str;
  }

  /**
  * Current strategy key
  */
  public function strategyKeyToString(): string
  {
      $str = '';

      foreach($this->strategies as $s){
        $str .= ($s->getKey());
      }

      return $str;
  }


  /**
  *  Get indicators used by strategies
  */
  public function getIndicators()
  {
    $data = [];

    foreach($this->strategies as $s){
      $data = ($s->getIndicators());
    }

    return $data;
  }

  /**
  *  Get features used by strategies
  */
  public function getFeatures()
  {
    $data = [];

    foreach($this->strategies as $s){
      $data = ($s->getFeatures());
    }

    return $data;
  }

  /**
  * Get conditions used by strategies
  */
  public function getConditions()
  {
    $data = [];

    foreach($this->strategies as $s){
      $data = ($s->getConditions());
    }

    return $data;
  }

  /**
  *  Get active bet for a market
  */
  public function getActiveBet($market)
  {
    $activeBet = Bet::where('market', $market )
                      ->where('active', 1);

    return $activeBet;
  }

  /**
  *  Get active bets for a market
  */
  public function getActiveMarketBets()
  {
    $activeBets = Bet::where('active', 1)
                      ->pluck('market')
                      ->toArray();

    return $activeBets;
  }

  /**
  * Add a bet to db if no active bet for that market
  */
  public function placeBet($bet)
  {
      $market = $bet['market'];
      $payload = $bet['payload'];
      $client = new MarketClient;
      $strategy = $bet['strategy'] ?? '';
      $successPerc = $bet['success_perc'] ?? self::SUCCESS_PRICE;
      $stopPerc = $bet['stop_perc'] ?? self::STOP_PRICE;

      $activeBet = $this->getActiveBet($market);

      if( ! $activeBet->count() ){

        $curPrice = $client->getLastMarketPrice($market);
        $price = (float) $curPrice->price;
        $buy_price = number_format($price, 8);
        $sell_price = number_format(( $price * $successPerc ), 8);
        $stop_price = number_format(( $price * $stopPerc ), 8);


        $bet = new Bet([
          'market' => $market,
          'payload' => serialize($payload),
          'strategy' => $strategy,
          'buy_price' => $buy_price,
          'sell_price' => $sell_price,
          'stop_price' => $stop_price,
          'active' => true,
          'traded' => false
        ]);
        $bet->save();

        // Predict success via machine learning
        $ml_status = $this->getBetPrediction($bet);
        $bet->ml_status = $ml_status;
        $bet->save();
      }

      return $bet;
  }

  public function trainLearnBot($bets)
  {
    $strategy_key = '';

    foreach($bets as $bet){
      $strategy_key = $bet['strategy'] ?? '';
    }

    $this->learnerBot->trainFromBets($strategy_key);
  }

  private function getBetPrediction($bet)
  {
    return $this->learnerBot->getBetPrediction($bet);
  }

  /**
  *   Check if active bet are due to check
  */
  public function validateBets()
  {
    $client = new MarketClient;
    $betTimeout = 6;
    foreach($this->strategies as $s){
      $betTimeout = $s->getActiveTime();
    }
    $limit = (60/5) * $betTimeout;
    $bets = Bet::where('active', true)
                ->where('created_at', '<',
                  Carbon::now()->subHours($betTimeout)->toDateTimeString() )
                ->get();

    //print_r($bets->toArray());
    foreach($bets as $bet){

      $buy_price = (float) $bet->buy_price;
      $successPrice = (float) $bet->sell_price;

      // Get last prices from db
      $prices = $client->getLastMarketPrices($bet->market, $limit);
      $maxPrice = (float) $prices->max('price');
      $minPrice = (float) $prices->min('price');

      $success = $maxPrice > $successPrice;

      $bet->final_min_price = $minPrice;
      $bet->final_max_price = $maxPrice;
      $bet->success = $success;
      $bet->active = false;
      $bet->end_at = Carbon::now();
      $bet->save();

    }
  }

  /**
  *   Check if active bet are due to check
  */
  public function validateBets2()
  {
    $data = [];
    $client = new MarketClient;

    $bets = Bet::where('active', true)
                  ->get();

    foreach($bets as $bet){

      $created_at =  $bet->created_at;
      $created_at = $created_at->setTimezone('America/New_York');
      $buy_price = (float) $bet->buy_price;
      $successPrice = (float) $bet->sell_price;
      $stopPrice = (float) $bet->stop_price;

      // Get last prices from db
      $prices = $client->getMarketPricesAfter($bet->market, $created_at, 500);
      $maxPrice = (float) $prices->max('price');
      $minPrice = (float) $prices->min('price');

      $success = $maxPrice > $successPrice;
      $fail = ($minPrice) && ( $minPrice < $stopPrice );

      if($success || $fail){
        $bet->final_min_price = $minPrice;
        $bet->final_max_price = $maxPrice;
        $bet->end_at = Carbon::now();
        $bet->success = $success;
        $bet->active = false;
        $bet->save();
      }

      $data[$bet->market] = [
          'buy_price' => $buy_price,
          'stopPrice' => $stopPrice,
          'successPrice' => $successPrice,
          'maxPrice' => $maxPrice,
          'minPrice' => $minPrice,
        //  'prices' => $prices->toArray(),
          'fail' => $fail,
          'success' => $success
        ];
    }

    return $data;
  }


  public function fixOldMarkets()
  {
    $client = new MarketClient();
    $data = $client->fixOldMarkets();

    return $data;
  }

}
