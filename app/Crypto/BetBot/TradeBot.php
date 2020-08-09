<?php

namespace App\Crypto\BetBot;

use App\Bet;
use App\Trade;
use App\Http\Resources\Bets;
use App\Crypto\MarketClient;
use App\Crypto\Helpers;
use Carbon\Carbon;
use Binance;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TradeBot
{
  const TRADE_BTC_AMOUNT = 0.002;

  private static $instance = null;

  private $bets = [];
  private $markets = [];
  private $binanceApi;
  private $learnerBot;
  private $orderBot;
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
    $this->orderBot= OrderBot::getInstance();
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
    // Init possible trades variable
    $trades = [];
    $logs = [];
    $betsToTrades = $this->getBetsToTrades();
    $possibleTradeNb = $this->getPossibleTradeNb();
    $tradeMade = 0;

    // Loop possible trades
    foreach($betsToTrades as $bet){

      // Quantity condition for trading
      $qty = $this->orderBot->getBuyingQty($bet->market);
      if($qty < 50){
        $log = sprintf("qty too low %s %s", $bet->market, $qty);
        Log::info($log);
        $logs[] = $log;

        continue;
      }

      // Create order if quota
      if( $tradeMade < $possibleTradeNb){
        $tradeMade++;

        // Create trade
        $trade = $this->createTrade($bet);

        // Default sell order
        $sell_order = [
          'null' => true
        ];

        // Create buy order
        $buy_order = $this->orderBot->placeBuyOrder($bet);
        $trade->buy_order_id = $buy_order->id;

        // Create sell order if buy is success
        //if( $status === 'FILLED'){
        if( $buy_order->success ){
          $sell_order = $this->orderBot->placeSellOrder($bet, $buyOrder);
          $trade->sell_order_id = $sell_order->id;
        }

        $trade->save();
        $trades[] = $trade;

      }else{
        $log = sprintf("not enough btc to trade");
        Log::info($log);
        $logs[] = $log;
      }

    }


    //$this->validateTrades();
    //$this->markTradedBets();

    $data = [
      'logs' => $logs,
      'bets' => $betsToTrades->toArray(),
      'trades' => $trades
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
  public function placeTrade($payload)
  {
    $bet = $payload['bet'];
    $buy_order = $payload['buy_order'] ?? [];
    $sell_order = $payload['sell_order'] ?? [];
      //$actives = $this->getActiveTrade($market);

    //  if( ! $actives->count() ){

        $trade = new Trade([
          'market' => $bet->market,
          'payload' => ($bet->payload),
          'buy_price' => $bet->buy_price,
          'sell_price' => $bet->sell_price,
          'stop_price' => $bet->stop_price,
          'active' => true,
          'buy_order_payload' => serialize($buy_order),
          'sell_order_payload' => serialize($sell_order),
        ]);
        $bet->save();
    //  }

      return $bet;
  }

  public function createTrade($bet)
  {

    $trade = new Trade([
      'market' => $bet->market,
      'payload' => ($bet->payload),
      'buy_price' => $bet->buy_price,
      'sell_price' => $bet->sell_price,
      'stop_price' => $bet->stop_price,
      'active' => true
    ]);
    $trade->save();

    return $trade;
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
      $successPrice = $buy_price + ($buy_price * 0.024);

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

  /**
  * Get bets with success ml status that havent been traded
  */
  public function getBetsToTrades()
  {
    $res = Bet::where('active', true)
              ->where('ml_status', "success")
              ->where('traded', false)
              ->orderBy('id', 'asc')
              ->get();

    return $res;
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
}
