<?php

namespace App\Crypto\BetBot;

use Carbon\Carbon;
use App\Crypto\Strategies\Strategy;
use App\Crypto\Bettor;
use App\Crypto\MarketClient;
use App\Crypto\Helpers;
use App\Bet;

class BetBot
{
  CONST SUCCESS_PRICE = 1.024;
  CONST STOP_PRICE = 0.99;
  private static $instance = null;

  private $bets = [];
  private $strategies = [];
  private $markets = [];
  private $learnerBot;

  private function __construct()
  {
    $this->init();
  }

  // The object is created from within the class itself
  // only if the class has no instance.
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
  * Fun all strategies and make bets
  */
  private function run()
  {
    $html = '';

    foreach($this->strategies as $s){
      $html .= $s->run($this->markets);
      $this->bets = $s->getBets();
    }

    return $html;
  }


  /**
  * Make bets & validate bets
  */
  public function makeBets()
  {

    // Run bot strategy
    $output = $this->run();
    $bets = $this->getBets();

    // Place new bets
    $this->trainLearnBot();
    foreach($bets as $bet){
      $this->placeBet($bet);
    }

    // Validate current bet
    $this->validateBets();

    $data = [
      'logs' => $output,
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
  * Add a bet to db if no active bet for that market
  */
  public function placeBet($bet)
  {
      $market = $bet['market'];
      $payload = $bet['payload'];
      $client = new MarketClient;

      $activeBet = $this->getActiveBet($market);

      if( ! $activeBet->count() ){

        $curPrice = $client->getLastMarketPrice($market);
        $price = (float) $curPrice->price;
        $buy_price = number_format($price, 8);
        $sell_price = number_format(( $price * SELF::SUCCESS_PRICE ), 8);
        $stop_price = number_format(( $price * SELF::STOP_PRICE ), 8);


        $bet = new Bet([
          'market' => $market,
          'payload' => serialize($payload),
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

  public function trainLearnBot()
  {
    $this->learnerBot->trainFromBets();
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
    $limit = (60/5) * $betTimeout;
    $bets = Bet::where('active', true)
                ->where('created_at', '<',
                  Carbon::now()->subHours($betTimeout)->toDateTimeString() )
                ->get();

    //print_r($bets->toArray());
    foreach($bets as $bet){

      $buy_price = (float) $bet->buy_price;
      $successPrice = ($buy_price * SELF::SUCCESS_PRICE);

      $prices = $client->getLastMarketPrices($bet->market, $limit);
      $firstPrice = $prices->first();
      $lastPrice = $prices->last();
      $maxPrice = $prices->max('price');
      $minPrice = $prices->min('price');
      $diff = Helpers::calcPercentageDiff($buy_price, $maxPrice);

      $finalPrices = sprintf('<small>%s <br> %s</small> %s (%s)',
                  $lastPrice->timestamp, $firstPrice->timestamp, $maxPrice, $diff);
      $success = $maxPrice > $successPrice;


      $bet->final_prices = $finalPrices;
      $bet->final_min_price = $minPrice;
      $bet->final_max_price = $maxPrice;
      $bet->success = $success;
      $bet->active = false;
      $bet->save();

    }
  }


}
