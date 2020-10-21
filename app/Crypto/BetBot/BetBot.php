<?php

namespace App\Crypto\BetBot;

use Carbon\Carbon;
use App\Crypto\Strategies\Strategy;
use App\Crypto\MarketClient;
use App\Crypto\BinanceClient;
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
    if (self::$instance == null) {
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

    foreach ($this->strategies as $strat) {

      $data[] = [
        'name' => $strat->getName(),
        'key' => $strat->getKey(),
        'description' => $strat->getDescription(),
        'indicators' => $strat->getIndicators(),
        'conditions' => $strat->getConditions(),
        'features' => $strat->getFeatures(),
      ];
    }

    return $data;
  }

  /**
   * Run all strategies and make bets
   */
  private function run()
  {
    $results = [];

    foreach ($this->strategies as $s) {

      // Get inactive markets for strategy
      $active_markets = $this->getActiveMarketBets($s->getName());
      $inactive_markets = array_diff($this->markets, $active_markets);

      $s->run($inactive_markets);
      $key = $s->getKey();

      $results[$key] = [
        'name' => $s->getName(),
        'key' => $s->getKey(),
        'bets' => $s->getBets(),
        'logs' => $s->getLogs(),
      ];

      // Log
      $log = new Log([
        'type' => 'bet',
        'group' => $key,
        'payload' => serialize($s->getLogs()),
      ]);
      $log->save();
    }

    return $results;
  }


  /**
   * Make bets & validate bets
   */
  public function makeBets()
  {

    // Validate current bet
    $this->validateBets();

    // Run bot strategy
    $strategies = $this->run();

    foreach ($strategies as $s) {

      // Place new bets
      $bets = $s['bets'];
      $key = $s['key'];

      if ($bets) {
        $this->trainLearnBot($key);
      }
      foreach ($bets as $bet) {
        $this->placeBet($bet);
      }

    }


    $data = [
      'strategies' => $strategies,
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
    foreach ($this->strategies as $s) {
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

    foreach ($this->strategies as $s) {
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

    foreach ($this->strategies as $s) {
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

    foreach ($this->strategies as $s) {
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

    foreach ($this->strategies as $s) {
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

    foreach ($this->strategies as $s) {
      $data = ($s->getConditions());
    }

    return $data;
  }

  /**
   *  Get active bet for a market
   */
  public function getActiveBet($market, $strategy = '')
  {
    $activeBet = Bet::where('market', $market)
      ->where('strategy', $strategy)
      ->where('active', 1);

    return $activeBet;
  }

  /**
   *  Get active bets for a market
   */
  public function getActiveMarketBets($strategy)
  {
    $activeBets = Bet::where('active', 1)
      ->where('strategy', $strategy)
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
    $conditions = $bet['conditions'];
    $features = $bet['features'];
    $client = new MarketClient;
    $strategy = $bet['strategy'] ?? '';
    $strategy_key = $bet['strategy_key'] ?? '';
    $successPerc = $bet['success_perc'] ?? self::SUCCESS_PRICE;
    $stopPerc = $bet['stop_perc'] ?? self::STOP_PRICE;

    $activeBet = $this->getActiveBet($market, $strategy);

    if (!$activeBet->count()) {

      $curPrice = $client->getLastMarketPrice($market);
      $price = (float)$curPrice->price;
      $buy_price = number_format($price, 8);
      $sell_price = number_format(($price * $successPerc), 8);
      $stop_price = number_format(($price * $stopPerc), 8);


      $bet = new Bet([
        'market' => $market,
        'conditions' => serialize($conditions),
        'features' => serialize($features),
        'strategy' => $strategy,
        'strategy_key' => $strategy_key,
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

  /**
   * Train ML model based on strategy's bets
   * @param $strategy_key
   */
  public function trainLearnBot($strategy_key)
  {

    $this->learnerBot->trainFromBets($strategy_key);
  }

  /**
   * Predict a bet label
   *
   * @param $bet
   * @return mixed
   */
  private function getBetPrediction($bet)
  {
    return $this->learnerBot->getBetPrediction($bet);
  }

  /**
   *   Check if active bet are due to check
   */
  public function validateBets()
  {
    $data = [];
    $client = BinanceClient::getInstance();

    // Get active bets
    $bets = Bet::where('active', true)
      ->get();

    // Loop and get prices & check price stop & check timelimit
    foreach ($bets as $bet) {

      // Bet Variables
      $created_at = $bet->created_at;
      $created_at = $created_at->setTimezone('America/New_York');
      $successPrice = (float)$bet->sell_price;
      $stopPrice = (float)$bet->stop_price;
      $lowest = null;
      $highest = null;
      $lost = false;
      $win = false;
      //printf("%s - %s<br>", $created_at->timestamp, $created_at);
      //printf("Succes %s<br>Stop %s<br>", $successPrice, $stopPrice);

      // Get last binance candlestick data
      $candlesticks = $client->getCandleSticksData($bet->market, '15m', 500);

      // Loop from most recent prices
      $nb = count($candlesticks);
      for ($i = 0; $i < $nb; $i++) {

        $highPrice = $candlesticks[$i][2];
        $lowPrice = $candlesticks[$i][3];

        $timestamp = $candlesticks[$i][0];
        $timestamp = (int)($timestamp / 1000);
        $date = new Carbon($timestamp);
        $date->setTimezone('America/New_York');
        $str = $date->format('j F H:i');


        // Check if prices is after bet
        if ($date->timestamp > $created_at->timestamp) {
          //printf("Low %s<br>High %s<br>", $lowPrice, $highPrice);

          // save lowest for stats
          if (is_null($lowest) ||
            $lowPrice < $lowest) {
            $lowest = $lowPrice;
          }
          // save highest for stats
          if (is_null($highest) ||
            $highPrice > $highest) {
            $highest = $highPrice;
          }

          // Check if loss
          if ($lowPrice <= $stopPrice) {
            $lost = true;
            //printf("Lost! price: %s stop: %s<br>", $lowPrice, $stopPrice);
            break;
          }

          // Check if win
          if ($highPrice >= $successPrice) {
            $win = true;
            //printf("Win! price: %s stop: %s<br>", $highPrice, $successPrice);
            break;
          }

        } else {
          //echo $created_at->timestamp . ' ' . $date->timestamp . "<br>";
        }

      }

      if ($win || $lost) {
        $bet->final_min_price = $lowest;
        $bet->final_max_price = $highest;
        $bet->end_at = Carbon::now();
        $bet->success = $win;
        $bet->active = false;
        $bet->save();
      }


      $data[$bet->market] = [
        'buy_price' => $bet->buy_price,
        'stopPrice' => $stopPrice,
        'successPrice' => $successPrice,
        'maxPrice' => $highest,
        'minPrice' => $lowest,
        //  'prices' => $prices->toArray(),
        'fail' => $lost,
        'success' => $win
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
