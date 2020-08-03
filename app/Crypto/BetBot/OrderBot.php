<?php

namespace App\Crypto\BetBot;

use App\Crypto\Strategies\Strategy;
use App\Crypto\Bettor;
use App\Crypto\MarketClient;
use App\Crypto\Helpers;
use App\Bet;
use App\Trade;
use App\Order;
use App\Http\Resources\Bets;
use Carbon\Carbon;

use Binance;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\CrossValidation\HoldOut;
use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\CrossValidation\Metrics\Accuracy;
use Rubix\ML\Kernels\Distance\Manhattan;

class OrderBot
{
  const TRADE_SUCCESS_INC_PERC = 1.5;
  const TRADE_BTC_AMOUNT = 0.002;

  private static $instance = null;

  private $bets = [];
  private $strategies = [];
  private $markets = [];
  private $binanceApi;
  private $exchangeInfo;
  private $learnerBot;
  private $tradeBtcAmount = 0.002;


  public function __construct()
  {
    $this->init();
    $this->initExchangeInfo();
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

  private function initExchangeInfo()
  {
    $data = $this->binanceApi->exchangeInfo();
    $symbols = $data['symbols'] ?? [];
    $this->exchangeInfo = $symbols;
  }

  public function getMarketOrderPrecision($market)
  {
    $marketInfo = $this->exchangeInfo[$market] ?? [];
    $filters = $marketInfo['filters'] ?? [];

    $stepSize = 1;
    foreach($filters as $filter){
      $filterType = $filter['filterType'] ?? '';
      if( $filterType == 'LOT_SIZE'){
        $stepSize = $filter['stepSize'] ?? 1;
      }
    }

    $stepSize = (float) $stepSize;
    $r = 1.0 / $stepSize;
    $i = (int) $r;
    $s = (string) $i;

    $zerolen = strlen($s) -1;

    return $zerolen;
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
  public function getWalletBtc()
  {
    $api = $this->getBinanceApi();
    $api->useServerTime();
    $ticker = $api->prices(); // Make sure you have an updated ticker object for this to work
    $balances = $api->balances($ticker);

    $btc = $balances['BTC']['available'] ?? 0;

    return $btc;
  }

  /**
  * get nb of trade we can make with btc wallter / min trade amount
  */
  public function getPossibleTradeNb()
  {
      $possibleTradeNb = 0;
      $btc = $this->getWalletBtc();
      $possibleTradeNb = floor($btc / self::TRADE_BTC_AMOUNT);

      return $possibleTradeNb;
  }

  /**
  * Make Order from currents bets
  */
  public function makeOrders($bets)
  {
    $buyOrder = [];
    $sellOrder = [];
    $possibleTradeNb = $this->getPossibleTradeNb();
    $nbBets = count($bets);

    for( $i=0; $i < $possibleTradeNb && $i < $nbBets ; $i++){

      $buyOrder = $this->placeBuyOrder($bets[$i]);
      $status = $buyOrder['status'] ?? '';
      if( $status === 'FILLED'){
        $sellOrder = $this->placeSellOrder($bets[$i], $buyOrder);
      }

    }

    $data = [
      'buyOrder' => $buyOrder,
      'sellOrder' => $sellOrder,
    ];

    return $data;
  }

  /**
  *   Make a buy order
  */
  private function placeBuyOrder($bet)
  {
      $env = config('app.env');
      $market = $bet->market;

      $price = $this->binanceApi->price($market);

      // Get precision float for market
      $precision = $this->getMarketOrderPrecision($market);

      $buy_price = $price;
      $quantity = self::TRADE_BTC_AMOUNT / $buy_price ; #;
      $quantity = number_format($quantity, $precision);
      $btc_amount = $quantity * $buy_price;
      $type = 'MARKET';

      $test = $env !== 'production';
      $payload = [
        'market' => $market,
        'price' => $buy_price,
        'quantity' => $quantity,
        'type' => 'MARKET',
        'flags' => [],
        'test' => $test
      ];

     // Create app order
     $bet = new Order([
       'market' => $market,
       'type' => 'buy',
       'payload' => serialize($payload),
       'price' => $buy_price,
       'quantity' => $quantity,
       'btc_amount' => $btc_amount,
       'active' => true,
       'trade_id' => $bet->id,
       'wallet_btc' => $this->getWalletBtc()
     ]);
    $bet->save();

    // Create binance Order
    $order = [
      'null' => null
    ];
    if( $env === 'local'){
      $order = $this->binanceApi->marketBuyTest($market, $quantity);
    } else if( $env === 'production'){
      $order = $this->binanceApi->marketBuy($market, $quantity);
    }

    $bet->binance_payload = serialize($order);
    $bet->save();

    return $order;

  }

  /**
  *   Make a Sell order with stop limit
  */
  private function placeSellOrder($bet, $binance_order)
  {
      $env = config('app.env');
      $test = $env !== 'production';

      $market = $bet->market;

      // Get base price
      $price  = $binance_order['price'] ?? 0.0;
      $price = floatval($price);
      $price = floatval($this->binanceApi->price($market));

      $origQty  = $binance_order['origqty'] ?? 0.0;
      $executedQty  = $binance_order['executedqty'] ?? 0.0;

      // Calc sell/stop price
      $sellPrice = $price + ($price * 0.015);
      $stopPrice = $price - ($price * 0.005);

      // Prep binance order
      $type = "STOP_LOSS_LIMIT"; // Set the type STOP_LOSS (market) or STOP_LOSS_LIMIT, and TAKE_PROFIT (market) or TAKE_PROFIT_LIMIT
      $quantity = $executedQty;
      $price = $sellPrice; // Try to sell it for 0.5 btc
      $stopPrice = $stopPrice; // Sell immediately if price goes below 0.4 btc
      $flag = ["stopPrice"=>$stopPrice];

      // Create app order
      $btc_amount = $price * $quantity;
      $payload = [
        'quantity' => $quantity,
        'price' => $price,
        'type' => $type,
        'flags' => $flag,
        'test' => $test
      ];

     // Create app order
     $bet = new Order([
       'market' => $market,
       'type' => 'sell',
       'payload' => serialize($payload),
       'price' => $price,
       'quantity' => $quantity,
       'btc_amount' => $btc_amount,
       'active' => true,
       'trade_id' => $bet->id,
       'wallet_btc' => $this->getWalletBtc()

     ]);
    $bet->save();

    // Create binance Order
    $order = [
      'null' => null
    ];
    if( $env === 'local'){
      $order = $this->binanceApi->sellTest($market, $quantity, $price, $type, $flag);
    } else if( $env === 'production'){
      $order = $this->binanceApi->sell($market, $quantity, $price, $type, $flag);
    }

    $bet->binance_payload = serialize($order);
    $bet->save();

    return $order;

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
          'active' => true
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
