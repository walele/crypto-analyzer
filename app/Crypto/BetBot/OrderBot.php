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
use App\Crypto\BinanceOCO;

use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\CrossValidation\HoldOut;
use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\CrossValidation\Metrics\Accuracy;
use Rubix\ML\Kernels\Distance\Manhattan;
use Illuminate\Support\Facades\Log;

class OrderBot
{
  const TRADE_SUCCESS_INC_PERC = 1.5;
  const TRADE_STOP_PRICE = 0.7;
  const TRADE_STOP_LIMIT_PRICE = 0.9;
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
    $this->binanceApi = new BinanceOCO($api_key,$api_secret);
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
      $price = $binance_order['price'] ?? 0.0;
      $price = floatval($price);
      $price = floatval($this->binanceApi->price($market));

      // parse binance key payload
      $parsed_binance = [];
      foreach($binance_order as $key=>$value){
        $parsedKey = strtolower($key);
        $parsed_binance[$parsedKey] = $binance_order[$key];
      }
      Log::info('$parsed_binance : ' . print_r($parsed_binance, true));


      // Get qty
      $origQty  = $parsed_binance['origqty'] ?? 0.0;
      $executedQty  = $parsed_binance['executedqty'] ?? 0.0;

      // Calc sell/stop price
      $sellPrice = $price + ($price * 0.019);       // Price for profit
      $stopPrice = $price - ($price * 0.007);       // Price that trigger stop
      $stopLimitPrice = $price - ($price * 0.009);  // Price of sell after stop loss

      // Format price
      $stopPrice = number_format($stopPrice, 8, '.', '');
      $stopLimitPrice = number_format($stopLimitPrice, 8, '.', '');

      // Prep binance order
      $quantity = $this->getCoinAvailable($market);
      Log::info('$quantity : ' . print_r($quantity, true));

      $quantity = $quantity['available'] ?? 0.0;


      $price = $sellPrice;        // Price for profit
      $stopPrice = $stopPrice;    // Price that trigger stop
      $flag = [
        "stopPrice" => $stopPrice,
        "stopLimitPrice" => $stopLimitPrice,
        "stopLimitTimeInForce" => "GTC"
      ];

      // Create app order
      $btc_amount = $price * $quantity;
      $payload = [
        'quantity' => $quantity,
        'price' => $price,
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
      $order = $this->binanceApi->sellOcoTest($market, $quantity, $price, $flag);
    } else if( $env === 'production'){
      $order = $this->binanceApi->sellOco($market, $quantity, $price, $flag);
      //$order = $this->binanceApi->sell($market, $quantity, $price);
    }

    $bet->binance_payload = serialize($order);
    $bet->save();

    return $order;

  }





  public function getCoinAvailable($market)
  {
    $name = str_replace('BTC', '', $market);
    $api = $this->getBinanceApi();
    $api->useServerTime();
    $ticker = $api->prices(); // Make sure you have an updated ticker object for this to work
    $balances = $api->balances($ticker);

    $data = $balances[$name] ?? [];


    return $data;
  }



}
